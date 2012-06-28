<?php

ini_set("memory_limit","200M");

class Motif_model extends CI_Model {

    function __construct()
    {
        $this->release_id = '';
        $this->loops      = array(); // loops in the search order
        $this->nts        = array(); // human-readable nts
        $this->nt_ids     = array(); // full nt ids
        $this->full_nts   = array();
        $this->header     = array();
        $this->disc       = array();
        $this->f_lwbp     = array();
        $this->similarity = array(); // loops in similarity order
        // Call the Model constructor
        parent::__construct();
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

    function get_linkage_data( $motif_id )
    {
        // get loop ids from this motif
//         SELECT id FROM ml_loops WHERE motif_id='IL_85647.1' AND release_id = '0.6';
        $this->db->select('id')
                 ->from('ml_loops')
                 ->where('motif_id', $motif_id)
                 ->where('release_id', $this->release_id);
        $query = $this->db->get();
        $loops = array();
        foreach ($query->result() as $row) {
            $loops[] = $row->id;
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
    function get_3d_sequence_variation( $motif_id )
    {
        $latest_release = $this->get_latest_release_for_motif($motif_id);

        // complete motif
        $this->db->select('seq, count(seq) as num')
                 ->from('ml_loops as t1')
                 ->join('loops_all as t2', 't1.id = t2.id')
                 ->where('motif_id', $motif_id)
                 ->where('release_id',$latest_release)
                 ->group_by('seq')
                 ->order_by('count(seq)', 'DESC');
        $query = $this->db->get();
        $complete_motif = array();
        foreach ($query->result() as $row) {
            $complete_motif[] = array($row->seq, $row->num);
        }

        // non-WC part
        $this->db->select('nwc_seq, count(nwc_seq) as num')
                 ->from('ml_loops as t1')
                 ->join('loops_all as t2', 't1.id = t2.id')
                 ->where('motif_id', $motif_id)
                 ->where('release_id',$latest_release)
                 ->group_by('nwc_seq')
                 ->order_by('count(nwc_seq)', 'DESC');
        $query = $this->db->get();
        $nwc_motif = array();
        foreach ($query->result() as $row) {
            $nwc_motif[] = array($row->nwc_seq, $row->num);
        }

        return array('complete' => $complete_motif,
                     'nwc' => $nwc_motif);
    }

    function get_latest_release_for_motif($motif_id)
    {
        $this->db->select('ml_releases.id')
                 ->from('ml_releases')
                 ->join('ml_motifs', 'ml_releases.id = ml_motifs.release_id')
                 ->where('ml_motifs.id',$motif_id)
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['id'];
    }

    // history tab
    function get_motif_release_history($motif_id)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->join('ml_motifs', 'ml_releases.id = ml_motifs.release_id')
                 ->where('ml_motifs.id',$motif_id)
                 ->where('ml_releases.type', substr($motif_id, 0, 2))
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
                 ->from('ml_releases')
                 ->join('ml_motifs', 'ml_releases.id = ml_motifs.release_id')
                 ->where('ml_motifs.id', $motif_id)
                 ->order_by('date');
        $result = $this->db->get();
        foreach ($result->result() as $row) {
            $releases_present[] = $row->release_id;
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
                                anchor_popup("motif/view/{$result[$i]['motif_id2']}", $result[$i]['motif_id2']),
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
        $this->get_header();
        for ($i = 0; $i < $this->num_loops; $i++) {
            $rows[$i] = $this->generate_row($i+1);
        }
        $rows = $this->remove_empty_columns($rows);
        return $rows;
    }

    function get_header()
    {
        $header = array('#D', '#S', 'Loop id', 'PDB', 'Disc');
        // 1, 2, ..., N
        for ($i = 1; $i < $this->num_nt; $i++) {
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

    function generate_row($id)
    {
        for ($i = 0; $i < count($this->header); $i++) {
            $key = $this->header[$i];
            if ( $key == '#D' ) {
                $row[$i] = $id;
            } elseif ( $key == '#S') {
                $row[$i] = array_search($this->loops[$id], $this->similarity);
            } elseif ( $key == 'Loop id' ) {
                $row[$i] = array('class'=>'loop','data'=>$this->get_checkbox($id)); //$this->loops[$id];
            } elseif ( $key == 'PDB' ) {
                $parts = explode("_", $this->loops[$id]);
                $row[$i] = '<a class="pdb">' . $parts[1] . '</a>';
            } elseif ( is_int($key) ) {
                $row[$i] = $this->nts[$this->loops[$id]][$key];
            } elseif ( $key == 'Disc' ) {
                $row[$i] = $this->disc[$this->loops[1]][$this->loops[$id]];
            }
            else {
                $parts = explode('-', $key);
                $nt1 = $this->nts[$this->loops[$id]][$parts[0]];
                $nt2 = $this->nts[$this->loops[$id]][$parts[1]];
                if ( isset($this->f_lwbp[$nt1][$nt2]) ) {
                    $row[$i] = $this->f_lwbp[$nt1][$nt2];
                } else {
                    $row[$i] = '';
                }
            }
        }
        return $row;
    }

    function get_interactions()
    {
        $this->db->select()
                 ->from('pdb_pairwise_interactions')
                 ->where_in('iPdbSig', array_keys($this->nt_ids))
                 ->where_in('jPdbSig', array_keys($this->nt_ids));
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $nt_full1 = $result[$i]['iPdbSig'];
            $nt_full2 = $result[$i]['jPdbSig'];
            $nt1 = $this->nt_ids[$nt_full1];
            $nt2 = $this->nt_ids[$nt_full2];

            if ($result[$i]['f_lwbp'] == $result[$i]['m_lwbp'] and
                $result[$i]['m_lwbp'] == $result[$i]['r_lwbp']) {
                $this->f_lwbp[$nt1][$nt2] = "<span class='label success'>{$result[$i]['f_lwbp']}</span>";
            }

            $this->f_lwbp[$nt1][$nt2] = $result[$i]['f_lwbp'];
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
        $this->db->select('loop_id,original_order,similarity_order')
                 ->from('ml_loop_order')
                 ->where('release_id', $this->release_id)
                 ->where('motif_id', $this->motif_id)
                 ->order_by('original_order');
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $loops[$result[$i]['original_order']] = $result[$i]['loop_id'];
            $similarity[$result[$i]['similarity_order']] = $result[$i]['loop_id'];
        }
        $this->loops = $loops;
        $this->num_loops = count($loops);
        $this->similarity = $similarity;
        // $loops[1] = 'IL_1S72_001'
        // $similarity[1] = 'IL_1J5E_029'
    }

    function get_nucleotides()
    {
        $this->db->select('loop_id,nt_id,position')
                 ->from('ml_loop_positions')
                 ->where('release_id', $this->release_id)
                 ->where('motif_id', $this->motif_id);
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $parts = explode("_", $result[$i]['nt_id']);
            $nt_id = $parts[4] . $parts[6] . ' ' . $parts[5];
            $nts[$result[$i]['loop_id']][$result[$i]['position']] = $nt_id;
            $this->full_nts[$result[$i]['loop_id']][$result[$i]['position']] = $result[$i]['nt_id'];
            $this->nt_ids[$result[$i]['nt_id']] = $nt_id;
        }
        $this->nts = $nts;
        $this->num_nt = count($nts, COUNT_RECURSIVE) / count($nts);
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
        $this->db->select('release_id')
                 ->from('ml_motifs')
                 ->where('id',$this->motif_id)
                 ->order_by('release_id','desc')
                 ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $this->release_id = $row->release_id;
        }
        return $this->release_id;
//         $this->release_id = $release_id;
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
        $query = $this->db->get();

        $row = $query->row();
        if ($row->id == $this->release_id) {
            return ' <label class="label success">current</label>';
        } else {
            return '';
        }
    }

}

/* End of file motif_model.php */
/* Location: ./application/model/motif_model.php */