<?php

ini_set("memory_limit","200M");

class Motif_model extends CI_Model {

    function __construct()
    {
        $this->release_id = '';
        $this->loops      = array(); // loops in the search order
        $this->nts        = array(); // human-readable nts
        $this->nt_ids     = array(); // full nt ids
        $this->unit_ids   = array();
        $this->full_nts   = array();
        $this->full_units = array();
        $this->header     = array();
        $this->disc       = array();
        $this->f_lwbp     = array();
        $this->similarity = array(); // loops in similarity order
        $this->full_length = array();
        $this->chainbreak = -1;
        // Call the Model constructor
        parent::__construct();
    }

    function is_valid_motif_id($motif_id)
    {
        $this->db->select('ml_motifs_id')
                 ->from('ml_motifs')
                 ->where('ml_motifs_id', $motif_id)
                 ->limit(1);

        if ( $this->db->get()->num_rows() > 0 ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function _get_aligned_unit_ids($motif_id)
    {
        $this->db->select()
                 ->from('ml_loop_positions AS ML')
                 #->join('unit_info AS UI',
                 #       'ML.unit_id = UI.unit_id')
                 ->where('motif_id', $motif_id)
                 ->where('ml_release_id', $this->release_id)
                 ->order_by('loop_id, position');
        $query = $this->db->get();

        // store the data temporarily in random order
        foreach($query->result() as $row) {
            // $temp['IL_XXXX_YYY'][0] = unit_id
            $temp[$row->loop_id][] = $row->unit_id;
        }

        // get the right order
        $this->db->select('loop_id')
                 ->from('ml_loop_order')
                 ->where('motif_id', $motif_id)
                 ->where('release_id', $this->release_id)
                 ->order_by('original_order');
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $data[$row->loop_id] = $temp[$row->loop_id];
        }

        return $data;
    }

    function get_csv($motif_id)
    {
        $data = $this->_get_aligned_unit_ids($motif_id);
        $csv = '';

        foreach($data as $loop_id) {
            $csv .= '"' . implode('","', $loop_id) . '"' . "\n";
        }

        return $csv;
    }

    function get_json($motif_id)
    {
        $alignment  = $this->_get_aligned_unit_ids($motif_id);
        $array_keys = array_keys($alignment);
        $data['num_instances']   = count($array_keys);
        $data['num_nucleotides'] = count($alignment[array_pop($array_keys)]);
        $data['alignment'] = $alignment;
        $data['motif_id']  = $motif_id;
        $this->get_chainbreak();
        $data['chainbreak'] = $this->chainbreak;
        $data = array_merge($data, $this->get_annotations($motif_id));

        return json_encode($data);
    }

    function get_annotations($motif_id)
    {
        $this->db->select()
                  ->from('ml_motif_annotations')
                  ->where('motif_id', $motif_id)
                  ->limit(1);
        $query = $this->db->get();

        if ( $query->num_rows > 0 ) {
            $result = $query->row();
            return array(
                'common_name'  => trim($result->common_name),
                'annotation'   => trim($result->annotation),
                'bp_signature' => trim($result->bp_signature)
            );
        } else {
            return array(
                'common_name' => '',
                'annotation' => '',
                'bp_signature' => ''
            );
        }
    }

    function save_annotation( $data )
    {
        $this->db->select()
                  ->from('ml_motif_annotations')
                  ->where('motif_id', $data['motif_id'])
                  ->limit(1);
        $query = $this->db->get();

        if ( $query->num_rows > 0 ) {
            $this->db->set($data['column'], trim($data['value']) );
            $this->db->set('author', trim($data['author']) );

            return $this->db
                        ->where('motif_id', $data['motif_id'])
                        ->update('ml_motif_annotations');
        } else {
            return 0;
        }
    }

    // compare two motifs page
    function compare_motifs($motif1, $motif2)
    {
        // get ordering and loop ids
        $this->db->select()
                 ->from('ml_loop_order')
                 ->where('motif_id', $motif1)
                 ->or_where('motif_id', $motif2)
                 ->order_by('motif_id, similarity_order')
                 // limit to one release only to avoid duplication
                 ->group_by('motif_id, loop_id');
        $query = $this->db->get();
        $i = 0;
        foreach($query->result() as $row) {
            $order[$row->motif_id][] = $row->loop_id;
            $loop_ids[] = $row->loop_id;
            $motifs[$row->motif_id][$row->loop_id] = 1;
        }

        // get loop sequences
        $this->db->select('loop_id, seq')
                 ->from('loop_info')
                 ->where_in('loop_id', $loop_ids);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $seqs[$row->loop_id] = $row->seq;
        }

        // get mutual discrepancies
        $this->db->select('LS.loop_id_1 as loop1,
                           LS.loop_id_2 as loop2,
                           LS.disc as disc,
                           LQ.status as status,
                           LQ.message as qa_message,
                           LQ.status as qa_status')
                 ->from('loop_searches', 'LS')
                 ->join('loop_search_qa', 'LQ', 'LS.loop_id_1 = LQ.loop_id_1 AND LS.loop_id_2 = LQ.loop_id_2', 'left')
                 ->where_in('LS.loop_id_1', $loop_ids)
                 ->where_in('LS.loop_id_2', $loop_ids);
        $query = $this->db->get();

        $matrix = array();
        
        foreach ($query->result() as $row) {
            $qa_status[$row->loop1][$row->loop2] = $row->qa_status;
            $qa_message[$row->loop1][$row->loop2] = $row->qa_message;
            $matrix[$row->loop1][$row->loop2] = $row->disc;
        }

        // make the table
        $table = array();
        $table[] = array(); // left top corner empty cell

        // horizontal header
        $i = 0;
        for ($j = 0; $j < count($order[$motif2]); $j++) {

            $loop1 = $order[$motif1][$i];
            $loop2 = $order[$motif2][$j];

            $class = 'annotation';

            if (array_key_exists($loop2, $motifs[$motif1]) and
                array_key_exists($loop2, $motifs[$motif2])) {
                $data = '1&2';
                $title = "Loop $loop2 is present in both $motif1 and $motif2";
            } else {
                $data = '2';
                $class .= ' highlight';
                $title = "Loop $loop2 is only in $motif2";
            }

            $table[] = array(
                             'data' => $data,
                             'class' => $class,
                             'rel'   => 'twipsy',
                             'title' => $title
                             );
        }

        for ($i = 0; $i < count($order[$motif1]); $i++) {
            for ($j = 0; $j < count($order[$motif2]); $j++) {

                $loop1 = $order[$motif1][$i];
                $loop2 = $order[$motif2][$j];

                $class = 'annotation';

                // first cell of each row with row info
                if ($j == 0) {
                    if (array_key_exists($loop1, $motifs[$motif1]) and
                        array_key_exists($loop1, $motifs[$motif2])) {
                        $data = '1&2';
                        $title = "Loop $loop1 is present in both $motif1 and $motif2";
                    } else {
                        $class .= ' highlight';
                        $data = '1';
                        $title = "Loop $loop1 is only in $motif1";
                    }

                    $table[] = array(
                                     'data' => $data,
                                     'class' => $class,
                                     'rel'   => 'twipsy',
                                     'title' => $title
                                     );
                }

                $data = '';
                $class = '';
                // for asymmetric searches choose the real discrepancy over -1's
                // if both searches not loaded in the db yet, assign -2
                if ( $matrix[$loop1][$loop2] == '' and $matrix[$loop2][$loop1] == '' ) {
                    $disc = -2;
                } else {
                    $disc = max($matrix[$loop1][$loop2], $matrix[$loop2][$loop1]);
                }

                if ( $disc == -1 ) {
                    $annotation = 'no match at discrepancy < 1.0';
                } elseif ( $disc == -2 ) {
                    $annotation = 'discrepancy data not loaded yet';
                } else {
                    $annotation = number_format($disc, 4);
                }

                $title = implode(', ', array("{$loop1}:{$loop2}", $annotation));

                if ($qa_status[$loop1][$loop2] or $qa_status[$loop2][$loop1]) {
                    $error_code = max($qa_status[$loop1][$loop2], $qa_status[$loop2][$loop1]);
                    $class = '';
                    $data = 'x';

                    if ( $error_code == 4 ) {
                        $title .= '<br>Extra basepair:<br>';
                    } elseif ( $error_code == 5 ) {
                        $title .= '<br>Extra near pair:<br>';
                    } elseif ( $error_code == 6 ) {
                        $title .= '<br>Intercalation:<br>';
                    } elseif ( $error_code == 7 ) {
                        $title .= '<br>Basepair conflict:<br>';
                    } elseif ( $error_code == 8 ) {
                        $title .= '<br>Basepair-basestacking mismatch:<br>';
                    }

                    if ( $qa_message[$loop1][$loop2] ) {
                        $title .= $qa_message[$loop1][$loop2];
                    } elseif ( $qa_message[$loop2][$loop1] ) {
                        $title .= $qa_message[$loop2][$loop1];
                    }
                }

                if ( $seqs[$loop1] == $seqs[$loop2] ) {
                    $title .= '<br>Attention: identical sequences';
                    $class .= 'identical';
                }

                $coord = "{$loop1}:{$loop2}";

                $table[] = array(
                                 'data'  => $data,
                                 'class' => implode(' ' , array($this->get_css_class($disc), 'jmolTools-loop-pairs', $class)),
                                 'rel'   => 'twipsy',
                                 'title' => $title,
                                 'data-coord' => $coord
                                );
            }
        }

        $num_columns = $j + 1; // one additional column for the first cell in each row

        return array('table' => $table, 'columns' => $num_columns);

    }

    // similar motifs tab
    function get_similar_motifs($motif_id)
    {
        $data = array();

        // check one search orientation
        $this->db->select_min('t2.disc', 'similarity_level')
                 ->select('t3.motif_id as similar_motif')
                 ->from('ml_loops as t1')
                 ->join('loop_searches as t2', 't1.ml_loops_id = t2.loop_id_1')
                 ->join('ml_loops as t3', 't2.loop_id_2 = t3.ml_loops_id')
                 ->where('t1.motif_id', $motif_id)
                 ->where('t1.release_id', $this->release_id)
                 ->where('t3.release_id', $this->release_id)
                 ->where('t3.motif_id !=', $motif_id)
                 ->where('t2.disc >=', 0)
                 ->group_by('t3.motif_id')
                 ->order_by('t2.disc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $data[$row->similarity_level] = $row->similar_motif;
        }

        // check the second search orientation
        $this->db->select_min('t2.disc', 'similarity_level')
                 ->select('t3.motif_id as similar_motif')
                 ->from('ml_loops as t1')
                 ->join('loop_searches as t2', 't1.ml_loops_id = t2.loop_id_2')
                 ->join('ml_loops as t3', 't2.loop_id_1 = t3.ml_loops_id')
                 ->where('t1.motif_id', $motif_id)
                 ->where('t1.release_id', $this->release_id)
                 ->where('t3.release_id', $this->release_id)
                 ->where('t3.motif_id !=', $motif_id)
                 ->where('t2.disc >=', 0)
                 ->group_by('t3.motif_id')
                 ->order_by('t2.disc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $data[$row->similarity_level] = $row->similar_motif;
        }

        // sort by discrepancy
        ksort($data);
        $done = array();
        $table = array();

        $i = 1;
        foreach ($data as $similarity_level => $similar_motif) {
            if (!array_key_exists($similar_motif, $done)) {
                $this->db->select('common_name')
                         ->from('ml_motif_annotations')
                         ->where('motif_id', $similar_motif);
                $name = '';
                $query = $this->db->get();
                foreach($query->result() as $row){
                    $name = $row->common_name;
                }

                $compare_link = anchor_popup("motif/compare/$motif_id/$similar_motif", 'Compare');
                $table[] = array($i,
                                 number_format($similarity_level, 4),
                                 anchor_popup("motif/view/$similar_motif", $similar_motif),
                                 $compare_link,
                                 $name);
                $done[$similar_motif] = 0;
                $i++;
            }
        }

        return $table;
    }

    // linkage
    function get_linkage_data( $motif_id )
    {
        // get loop ids from this motif
        $this->db->select('ml_loops_id')
                 ->from('ml_loops')
                 ->where('motif_id', $motif_id)
                 ->where('release_id', $this->release_id);
        $query = $this->db->get();
        $loops = array();

        foreach ($query->result() as $row) {
            $loops[] = $row->ml_loops_id;
        }

        // intraclusteral linkage
        $this->db->select_max('discrepancy', 'intra_max_disc')
                 ->select_avg('discrepancy', 'intra_avg_disc')
                 ->from('ml_mutual_discrepancy')
                 ->where_in('loop_id1', $loops)
                 ->where('release_id', $this->release_id);
        $query = $this->db->get();

        $results = array();
        foreach ($query->result() as $row) {
            $results['linkage']['intra_max_disc'] = $row->intra_max_disc;
            $results['linkage']['intra_avg_disc'] = $row->intra_avg_disc;
        }

        $this->db->select_min('discrepancy', 'intra_min_disc')
                 ->from('ml_mutual_discrepancy')
                 ->where_in('loop_id1', $loops)
                 ->where('release_id', $this->release_id)
                 ->where('discrepancy >', 0);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $results['linkage']['intra_min_disc'] = $row->intra_min_disc;
        }

        // interclusteral linkage

        return $results;
    }

    // sequence variation
    function get_sequence_variants($motif_id)
    {
        $latest_release = $this->get_latest_release_for_motif($motif_id);
        $seq   = array();

        foreach($this->loops as $loop_id) {
            $index = array();
            $position = array();
            #$unit_id_arr = array();

            // get indexes of bordering nucleotides for loop id
            $this->db->select('ML.position, LP.unit_id, UI.number')
                     ->from('loop_positions AS LP')
                     ->join('unit_info AS UI', 'LP.unit_id = UI.unit_id')
                     ->join('ml_loop_positions AS ML', 'LP.loop_id = ML.loop_id AND ' .
                                                       'LP.unit_id = ML.unit_id')
                     ->where('ML.ml_release_id', $latest_release)
                     ->where('LP.loop_id', $loop_id)
                     ->where('LP.border', 1)
                     ->order_by('ML.position');
            $query = $this->db->get();

            foreach($query->result() as $row) {
                $index[] = $row->position;
                #$unit_id_arr[] = $row->unit_id;
            }

            list($loop_type, $pdb, $order) = explode('_', $loop_id);

            if ( $loop_type == 'HL' ) {
                $seq = array($this->get_strand_fragment($pdb, $index[0], $index[1]));
                $seq_nwc[] = substr($seq[0], 1, -1);
            } elseif ( $loop_type == 'IL' ) {
                $this->db->select('LI.seq, LI.nwc_seq, LI.r_seq, LI.r_nwc_seq')
                         ->from('loop_info AS LI')
                         ->where('LI.loop_id', $loop_id);
                $query = $this->db->get();

                foreach ( $query->result() as $row ){
                    $seq_com[] = ( $index[0] < $index[2] ) ? $row->seq : $row->r_seq; ## doesn't quite work
                    $seq_com[] = $row->seq;
                    $seq_nwc[] = $row->nwc_seq;
                }
                #$seq = array($this->get_strand_fragment($pdb, $index[0], $index[1]),
                #             $this->get_strand_fragment($pdb, $index[2], $index[3]));
                #$seq_nwc[] = substr($seq[0], 1, -1) . '*' . substr($seq[1], 1, -1);

                #echo "<p>pdb/i0/i1/i2/i3:  $pdb // $index[0] // $index[1] // $index[2] // $index[3]</p>";
                #echo "<p>seq: " . var_dump($seq) . "</p>";
            }

            $seq_all[] = implode('*', $seq);
        }

        #$counts = array_count_values($seq_all);
        $counts = array_count_values($seq_com);
        arsort($counts);
        foreach($counts as $seq => $count) {
            $complete[] = array($seq, $count);
        }

        $counts = array_count_values($seq_nwc);
        arsort($counts);
        foreach($counts as $seq => $count) {
            $nwc[] = array($seq, $count);
        }

        return array('complete' => $complete,
                     'nwc'      => $nwc);
    }

    private function get_strand_fragment($pdb, $start, $stop)
    {
        $rna = array('A', 'C', 'G', 'U');

        $this->db->select('unit')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb)
                 ->where_in('unit', $rna)
                 #->where('chain_index >=', $start);
                 #->where('chain_index <=', $stop);
                 ->where('number >=', $start)
                 ->where('number <=', $stop)
                 ->order_by('number');

        $query = $this->db->get();

        foreach($query->result() as $row) {
            $nts[] = $row->unit;
        }
        
        return implode('', $nts);
    }

    private function get_strand_fragment_unit($unit_1, $unit_2)
    {
        //  testing a unit_id-based approach to building sequence variant strings
        $rna = array('A', 'C', 'G', 'U');

        list($pdb_1, $model_1, $chain_1, $nuc_1, $pos_1, $trash_1_1, $trash_2_1, 
             $trash_3_1, $ic_1) = explode('_', $unit_1);
        list($pdb_2, $model_2, $chain_2, $nuc_2, $pos_2, $trash_1_2, $trash_2_2, 
             $trash_3_2, $ic_2) = explode('_', $unit_2);

        $this->db->select('unit')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb_1)
                 ->where('model', $model_1)
                 ->where('chain', $chain_1)
                 ->where('number >=', $pos_1)
                 ->where('number <=', $pos_2)
                 ->where_in('unit', $rna);
    }

    function get_latest_release_for_motif($motif_id)
    {
        $this->db->select('MR.ml_releases_id')
                 ->from('ml_releases AS MR')
                 ->join('ml_motifs AS MM', 'MR.ml_releases_id = MM.release_id')
                 ->where('MM.ml_motifs_id',$motif_id)
                 ->where('MR.type', substr($motif_id, 0, 2))
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['ml_releases_id'];
    }

    // history tab
    function get_motif_release_history($motif_id)
    {
        $this->db->select()
                 ->from('ml_releases AS MR')
                 ->join('ml_motifs AS MM', 'MR.ml_releases_id = MM.release_id')
                 ->where('MM.ml_motifs_id',$motif_id)
                 ->where('MR.type', substr($motif_id, 0, 2))
                 ->order_by('date');
        $query = $this->db->get();

        $table[0][0] = 'Release';
        $table[1][0] = '<strong>Date</strong>';
        $table[2][0] = '<strong>Status</strong>';
        foreach ($query->result() as $row) {
            $table[0][] = anchor(base_url("motifs/release/".substr($motif_id,0,2) .'/'.$row->release_id), $row->release_id);
            $table[1][] = date('Y-m-d', strtotime($row->date));

            if ($row->comment == 'Exact match') {
                $label = 'success';
            } elseif ($row->comment == 'New id, no parents') {
                $label = 'notice';
            } else {
                $label = 'important';
            }

            $table[2][] = "<span class='label $label'>{$row->comment}</span>";
        }

        return $table;
    }

    function get_history($motif_id)
    {
        $this->db->select()
                 ->from('ml_releases as MR')
                 ->join('ml_motifs AS MM', 'MR.ml_releases_id = MM.release_id')
                 ->where('MM.ml_motifs_id', $motif_id)
                 ->order_by('date');
        $result = $this->db->get();

        foreach ($result->result() as $row) {
            $releases_present[] = $row->ml_releases_id;
        }

        $this->db->select()
                 ->from('ml_set_diff')
                 ->where_in('release_id', $releases_present)
                 ->where('motif_id1', $this->motif_id)
                 ->order_by('overlap', 'desc');
        $result = $this->db->get()->result_array();
        $tables['parents'] = $this->make_history_table($result, $motif_id);

        $this->db->select()
                 ->from('ml_set_diff')
                 ->where_not_in('release_id', $releases_present)
                 ->where('motif_id1', $this->motif_id)
                 ->order_by('overlap', 'desc');
        $result = $this->db->get()->result_array();
        $tables['children'] = $this->make_history_table($result, $motif_id);

        return $tables;
    }

    function make_history_table($result, $motif_id)
    {
        $table = array();

        for ($i = 0; $i < count($result); $i++) {
            $table[] = array(
                                anchor_popup("motif/view/{$result[$i]['motif_id2']}", $result[$i]['motif_id2']) .
                                '<br>' . anchor_popup("motif/compare/{$result[$i]['motif_id2']}/{$result[$i]['motif_id1']}", 'Compare'),
                                $this->make_loop_links($result[$i]['intersection']),
                                $this->make_loop_links($result[$i]['one_minus_two']),
                                $this->make_loop_links($result[$i]['two_minus_one'])
                             );
        }

        return $table;
    }

    function make_loop_links($loop_list)
    {
        if ( $loop_list == '' ) {
            return $loop_list;
        }

        $loops = explode(',', $loop_list);

        for ($i = 0; $i < count($loops); $i++) {
            $loops[$i] = anchor_popup("loops/view/$loops[$i]", $loops[$i]);
        }

        return implode(', ', $loops);
    }

    // mutual discrepancy matrix widget
    function get_mutual_discrepancy_matrix()
    {
        ini_set('memory_limit', '512M');

        $this->db->select()
                 ->from('ml_mutual_discrepancy')
                 ->where('release_id', $this->release_id)
                 ->where_in('loop_id1', $this->loops)
                 ->where_in('loop_id2', $this->loops);
        $result = $this->db->get()->result_array();

        $disc = array(); // $disc['IL_1S72_001']['IL_1J5E_023'] = 0.2897
        for ($i = 0; $i < count($result); $i++) {
            $disc[$result[$i]['loop_id1']][$result[$i]['loop_id2']] = $result[$i]['discrepancy'];
        }

        ### DEBUG
        #echo "<p>result count:  $i</p>";
        #echo "<p>release_id:  $this->release_id</p>";
        #echo "<p>loops:  " . var_dump($this->loops) . "</p>";

        $matrix = array();
        for ($i = 1; $i <= $this->num_loops; $i++) {
            $loop_id1 = $this->similarity[$i];
            for ($j = 1; $j <= $this->num_loops; $j++) {
                $loop_id2 = $this->similarity[$j];
                $cell = array('data-disc' => $disc[$loop_id1][$loop_id2],
                              'data-pair' => "$loop_id1:$loop_id2",
                              'class'     => $this->get_css_class($disc[$loop_id1][$loop_id2]),
                              'rel'       => 'twipsy',
                              'title'     => "$loop_id1:$loop_id2, {$disc[$loop_id1][$loop_id2]}");
                $matrix[] = $cell;
            }
        }
        return $matrix;
    }

    function get_css_class($disc)
    {
        $class = '';

        if ( $disc == 0 ) {
            $class = 'md00';
        } elseif ( $disc == -1 ) {
            $class = 'md_no_match';
        } elseif ( $disc == -2 ) {
            $class = 'md_not_loaded';
        } elseif ( $disc < 0.1 ) {
            $class = 'md01';
        } elseif ( $disc < 0.2 ) {
            $class = 'md02';
        } elseif ( $disc < 0.3 ) {
            $class = 'md03';
        } elseif ( $disc < 0.4 ) {
            $class = 'md04';
        } elseif ( $disc < 0.5 ) {
            $class = 'md05';
        } elseif ( $disc < 0.6 ) {
            $class = 'md06';
        } elseif ( $disc < 0.7 ) {
            $class = 'md07';
        } elseif ( $disc < 0.8 ) {
            $class = 'md08';
        } elseif ( $disc < 0.9 ) {
            $class = 'md09';
        } else {
            $class = 'md10';
        }

        return $class;
    }

    // checkbox widget
    function get_checkboxes($loops)
    {
        // $full_nts['IL_1S72_001'][1] = '1S72_AU_...'
        $checkbox_div = '<ul class="inputs-list">';
        for ($i = 1; $i <= count($loops); $i++) {
            $checkbox_div .= "<li><label><input type='checkbox' id='{$loops[$i]}' class='jmolInline' ";
            ksort($this->full_nts[$loops[$i]]);
            $checkbox_div .= "data-coord='" . implode(",", $this->full_nts[$loops[$i]]) . "'>";
            $checkbox_div .= "&nbsp;{$loops[$i]}";
            $checkbox_div .= '</label></li>';
            //<input type='checkbox' id='s1' class='jmolInline' data-coord='1S72_1_0_1095,1S72_1_0_1261'><label for='s1'>IL_1S72_038</label><br>
        }
        $checkbox_div .= '</ul>';
        return $checkbox_div;
    }

    function get_checkbox($i)
    {
        ksort($this->full_nts[$this->loops[$i]]);
        return "<label><input type='checkbox' id='{$this->loops[$i]}' class='jmolInline' " .
               "data-coord='". implode(",", $this->full_nts[$this->loops[$i]]) ."'>{$this->loops[$i]}</label>"
               . "<span class='loop_link'>" . anchor_popup("loops/view/{$this->loops[$i]}", '&#10140;') . "</span>";

    }

    // pairwise interactions widget
    function get_interaction_table()
    {
        $this->get_nucleotides();
        $this->get_loops();
        $this->get_discrepancies();
        $this->get_interactions();
        $this->get_loop_lengths();
        $this->get_chainbreak();
        $this->get_header();

        for ($i = 0; $i < $this->num_loops; $i++) {
            $rows[$i] = $this->generate_row($i+1);
        }
        
        $rows = $this->remove_empty_columns($rows);
        
        return $rows;
    }

    function get_loop_lengths()
    {
        // get lengths of complete loops to calculate the number of bulges.
        $this->db->select('loop_id, length')
                 ->from('loop_info')
                 ->where_in('loop_id', $this->loops);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $this->full_length[$row->loop_id] = $row->length;
        }
    }

    function get_header()
    {
        $header = array('#D', '#S', 'Loop id', 'PDB', 'Disc', 'Bulges');

        // 1, 2, ..., N
        for ($i = 1; $i < $this->num_nt; $i++) {
			if ($i == $this->chainbreak + 1) { // insert a column after chainbreak
				$header[] = 'break';
			}

			$header[] = ' ';
			$header[] = $i;
		}

        // 1-2, ..., 1-N, ..., N-1 - N
        for ($i = 1; $i < $this->num_nt; $i++) {
            for ($j = $i; $j < $this->num_nt; $j++) {
                $header[] = "$i-$j";
            }
        }

        $this->header = $header;
    }

	function get_chainbreak()
	{
		// if a hairpin, do nothing
		if ( substr($this->motif_id, 0, 2) == 'HL' ) {
			return;
		}

		// get an internal loop from this motif
		$this->db->select('ml_loops_id')
		         ->from('ml_loops')
		         ->where('motif_id', $this->motif_id)
		         ->limit(1);
        $query = $this->db->get();
        $result = $query->row();
    	$loop_id = $result->ml_loops_id;

    	// get chain breaks
    	$this->db->select('ML.position')
    	         ->from('loop_positions AS LP')
    	         ->join('ml_loop_positions AS ML', 'LP.loop_id = ML.loop_id AND ' .
    	                                           'LP.unit_id = ML.unit_id')
    	         ->where('LP.loop_id', $loop_id)
    	         ->where('LP.border', 1)
    	         ->where('ML.motif_id', $this->motif_id)
    	         ->where('ML.ml_release_id', $this->release_id)
    	         ->order_by('ML.position', 'ASC');
        $result = $this->db->get()->result_array();

        // take second row
		if ( count($result) > 0 ) {
	        $this->chainbreak = $result[1]['position'];
        }
	}

    function generate_row($id)
    {
        for ($i = 0; $i < count($this->header); $i++) {
            $key = $this->header[$i];

            if ( $key == '#D' ) {
                $row[] = $id;
            } elseif ( $key == '#S') {
                $row[] = array_search($this->loops[$id], $this->similarity);
            } elseif ( $key == 'Loop id' ) {
                $row[] = array('class'=>'loop','data'=>$this->get_checkbox($id)); //$this->loops[$id];
            } elseif ( $key == 'PDB' ) {
                $parts = explode("_", $this->loops[$id]);
                $row[] = '<a class="pdb">' . $parts[1] . '</a>';
            } elseif ( $key == 'Bulges' ) {
                $row[] = $this->full_length[$this->loops[$id]] - count($this->full_nts[$this->loops[$id]]);
            } elseif ( $key == 'break' ) {
            	$row[] = '*';
            } elseif ( is_int($key) ) {
                $parts = explode(' ', $this->nts[$this->loops[$id]][$key]);
                $row[] = $parts[1];
                $row[] = $parts[0];
            } elseif ( $key == ' ' ) {
                // do nothing
            } elseif ( $key == 'Disc' ) {
                $row[] = $this->disc[$this->loops[1]][$this->loops[$id]];
            }
            else {
                $parts = explode('-', $key);
                $nt1 = $this->nts[$this->loops[$id]][$parts[0]];
                $nt2 = $this->nts[$this->loops[$id]][$parts[1]];
                $unit_1 = $this->units[$this->loops[$id]][$parts[0]];
                $unit_2 = $this->units[$this->loops[$id]][$parts[1]];

                #echo "<p>nt1 = $nt1 // nt2 = $nt2</p>";
                #echo "<p>unit_1 = $unit_1 // unit_2 = $unit_2</p>";

                #if ( isset($this->f_lwbp[$nt1][$nt2]) ) {
                #    $row[] = $this->f_lwbp[$nt1][$nt2];
                if ( isset($this->f_lwbp[$unit_1][$unit_2]) ) {
                    $row[] = $this->f_lwbp[$unit_1][$unit_2];
                } else {
                    $row[] = '';
                }
            }
        }
        return $row;
    }

    function get_interactions()
    {
        $this->db->select()
                 ->from('unit_pairs_interactions')
                 #->where_in('iPdbSig', array_keys($this->nt_ids))
                 #->where_in('jPdbSig', array_keys($this->nt_ids));
                 ->where_in('unit_id_1', array_keys($this->unit_ids))
                 ->where_in('unit_id_2', array_keys($this->unit_ids));
        $query = $this->db->get();

        foreach($query->result() as $row) {
            #$nt_full1 = $row->iPdbSig;
            #$nt_full2 = $row->jPdbSig;
            $unit_full_1 = $row->unit_id_1;
            $unit_full_2 = $row->unit_id_2;

            #if ( array_key_exists($nt_full1,$this->nt_ids) and
            #     array_key_exists($nt_full2,$this->nt_ids) ) {
            if ( array_key_exists($unit_full_1,$this->unit_ids) and
                 array_key_exists($unit_full_2,$this->unit_ids) ) {
                $unit_1 = $this->unit_ids[$unit_full_1];
                $unit_2 = $this->unit_ids[$unit_full_2];

                $this->f_lwbp[$unit_1][$unit_2] = $row->f_lwbp;
            }
        }
    }

    function get_discrepancies()
    {
        $this->db->select()
                 ->from('ml_mutual_discrepancy')
                 ->where('release_id', $this->release_id)
                 ->where('loop_id1', $this->loops[1])
                 ->where_in('loop_id2', $this->loops);
        $result = $this->db->get()->result_array();
        
        for ($i = 0; $i < count($result); $i++) {
            $disc[$result[$i]['loop_id1']][$result[$i]['loop_id2']] = number_format($result[$i]['discrepancy'],4);
        }

        if ( $i == 0 ) {
            $this->disc = 0;
        } else {
            $this->disc = $disc;
        }
    }

    function get_loops()
    {
        $this->db->select('loop_id, original_order, similarity_order')
                 ->from('ml_loop_order')
                 ->where('release_id', $this->release_id)
                 ->where('motif_id', $this->motif_id)
                 ->order_by('original_order');
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $loops[$row->original_order] = $row->loop_id;
            $similarity[$row->similarity_order] = $row->loop_id;
        }
        
        $this->loops = $loops;
        $this->num_loops = count($loops);
        $this->similarity = $similarity;
        // $loops[1] = 'IL_1S72_001'
        // $similarity[1] = 'IL_1J5E_029'
    }

    function get_nucleotides()
    {
        $this->db->select('MLP.loop_id, MLP.nt_id, MLP.position, UI.unit_id')
                 ->from('ml_loop_positions AS MLP')
                 ->join('unit_info AS UI', 'MLP.unit_id = UI.unit_id')
                 ->where('ml_release_id', $this->release_id)
                 ->where('motif_id', $this->motif_id);
        $result = $this->db->get()->result_array();

        for ($i = 0; $i < count($result); $i++) {
            $parts = explode("_", $result[$i]['nt_id']);
            $nt_id = $parts[4] . $parts[6] . ' ' . $parts[5];
            $unit_id = $result[$i]['unit_id'];
            #$ic = ( $parts[6] == '' ) ? '' : '||||' . $parts[6];
            #$unit_id = $parts[0] . '|' . $parts[2] . '|' . $parts[3] . '|' . $parts[5] 
            #            . '|' . $parts[4] . $ic;

            $nts[$result[$i]['loop_id']][$result[$i]['position']] = $nt_id;
            $this->full_nts[$result[$i]['loop_id']][$result[$i]['position']] = $result[$i]['nt_id'];
            $this->nt_ids[$result[$i]['nt_id']] = $nt_id;

            $units[$result[$i]['loop_id']][$result[$i]['position']] = $unit_id;
            $this->full_units[$result[$i]['loop_id']][$result[$i]['position']] = $unit_id;
            $this->unit_ids[$unit_id] = $unit_id;
        }

        $this->nts = $nts;
        $this->num_nt = count($nts, COUNT_RECURSIVE) / count($nts);
        $this->units = $units;
        $this->num_unit = count($units, COUNT_RECURSIVE) / count($units);
        // $nts['IL_1S72_001'][1] = 'A 102'
        // $nt_ids['1S72_AU_...'] = 'A 102'
        // $full_nts['IL_1S72_001'][1] = '1S72_AU_...'
    }

    function remove_empty_columns($rows)
    {
        // find empty columns
        $to_delete = array();
        for ( $i = 0; $i < count($this->header); $i++ ) {
            $empty = 0;

            for ( $j = 0; $j < $this->num_loops; $j++ ) {
                if ( $rows[$j][$i] == '' ) {
                    $empty++;
                } else {
                    break;
                }
            }

            if ( $empty == $this->num_loops ) {
                $to_delete[] = $i;
            }
        }

        // remove empty columns
        for ( $i = 0; $i < count($to_delete); $i++ ) {
            unset($this->header[$to_delete[$i]]);

            for ( $j = 0; $j < $this->num_loops; $j++ ) {
                unset($rows[$j][$to_delete[$i]]);
            }
        }

        return $rows;
	}

    // auxiliary functions
    function set_release_id()
    {
        $this->release_id = $this->get_latest_release_for_motif($this->motif_id);
        return $this->release_id;
    }

    function set_motif_id($motif_id)
    {
        $this->motif_id = $motif_id;
    }

    function is_current_motif($motif_id)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type', substr($motif_id, 0, 2))
                 ->order_by('date','desc');
        $row = $this->db->get()->row();

        if ($row->ml_releases_id == $this->release_id) {
            return ' <label class="label success">current</label>';
        } else {
            return '';
        }
    }

}

/* End of file motif_model.php */
/* Location: ./application/model/motif_model.php */
