<?php

ini_set("memory_limit","300M");

class Pdb_model extends CI_Model {
    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $this->qa_status = array(NULL,'valid','missing nts','modified nts','abnormal chain','incomplete nts','complementary','symmetry','all high RSRZ','pair high RSRZ');
        // Call the Model constructor
        parent::__construct();
    }
    function get_all_pdbs()
    {
        $this->db->select('distinct(pdb_id)')
                 ->from('pdb_info');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $pdbs[] = $row->pdb_id; //anchor(base_url(array('pdb',$row->structureId)), $row->structureId );
        }
        return $pdbs;
    }
    function get_recent_rna_containing_structures($num)
    {
        $this->db->select('distinct(pdb_id)')
                 ->from('pdb_info')
                 ->order_by('release_date', 'desc')
                 ->limit($num);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdbs[] = $row->pdb_id;
        }
        return $pdbs;
    }
    function get_latest_motif_assignments($pdb_id, $loop_type)
    {
        // This does not actually get all the most recent assignments
        // It only searches the most recent motif atlas release
        // If the structure has loops in that release, they are shown
        // If it does not, then no motif assignments are shown
        // That may be safer for structures that used to be in the Motif Atlas

        // $loop_type = IL or HL

        $latest_release = $this->get_latest_motif_release($loop_type);

        $this->db->select()
                 ->from('ml_loops')
                 ->where('ml_release_id', $latest_release)
                 ->like('loop_id', strtoupper($loop_type) . '_' . $pdb_id, 'right');
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data[$row->loop_id] = $row->motif_id;
        }
        return $data;
    }
    function get_all_latest_motif_assignments($loop_type) // new
    {
        // This does not actually get all the most recent assignments
        // It only searches the most recent motif atlas release
        // If the structure has loops in that release, they are shown
        // If it does not, then no motif assignments are shown
        // That may be safer for structures that used to be in the Motif Atlas

        // $loop_type = IL or HL

        $latest_release = $this->get_latest_motif_release($loop_type);

        $this->db->select()
                 ->from('ml_loops')
                 ->where('ml_release_id', $latest_release)
                 ->like('loop_id', strtoupper($loop_type) . '_', 'right');
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data[$row->loop_id] = $row->motif_id;
        }
        return $data;
    }
    function get_latest_loop_release()
    {
        $this->db->select('loop_release_id')
                 ->from('loop_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['loop_release_id'];
    }
    function get_latest_loop_release_for_this_pdb($pdb_id)
    {
        $this->db->select('lq.loop_release_id')
                 ->from('loop_releases AS lr')
                 ->join('loop_qa AS lq', 'lq.loop_release_id = lr.loop_release_id')
                 ->join('loop_info AS li', 'li.loop_id = lq.loop_id')
                 ->where('pdb_id', $pdb_id)
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['loop_release_id'];
    }
    function get_loop_mappings($pdb_id)
    {
    	$this->db->select('lm.loop_id')
    			->select('lm.query_loop_id AS similar_loop')
    			->select('la.annotation_1 AS similar_annotation')
    			->select('lm.match_type')
    			->from('loop_mapping AS lm')
    			->join('loop_annotations AS la', 'lm.query_loop_id = la.loop_id', 'left')
    			->where('lm.pdb_id', $pdb_id)
    			->order_by('loop_mapping_id'); // use (...,'desc') if opposite order
    	$query = $this->db->get();
    	$loop_mapping_table = array();
		foreach ($query->result() as $row) {
            $loop_mapping_table[$row->loop_id] = $row;
        }
        // need to add loop annotations
    	return $loop_mapping_table;
    }
    function get_loops($pdb_id)
    {
        // sub query for only most recent loop_mapping per loop id
      $loop_mapping_table = $this->get_loop_mappings($pdb_id);

        // big query for output table
        $this->db->select('lq.loop_id')
                 ->select('lq.status')
                 ->select('lq.modifications')
                 ->select('lq.nt_signature')
                 ->select('lq.complementary')
                 ->select('li.unit_ids')
                 ->select('li.loop_name')
                 ->select('la.annotation_1')
                 ->from('loop_qa AS lq')
                 ->join('loop_info AS li', 'li.loop_id = lq.loop_id')
                 ->join('loop_annotations AS la', 'lq.loop_id = la.loop_id', 'left')
                 ->where('li.pdb_id', $pdb_id);
        $query = $this->db->get();

        $loop_types = array('IL','HL','J3');
        foreach ($loop_types as $loop_type) {
            $valid_tables[$loop_type] = array();
            $invalid_tables[$loop_type] = array();
        }

        // $motifs = $this->get_latest_motif_assignments($pdb_id, 'IL');
        // $motifs = array_merge($motifs, $this->get_latest_motif_assignments($pdb_id, 'HL'));
        $motifs = $this->get_all_latest_motif_assignments('IL');
        $motifs = array_merge($motifs, $this->get_all_latest_motif_assignments('HL'));

        foreach ($query->result() as $row) {
            $loop_type = substr($row->loop_id, 0, 2);

            // building direct annotation + motif group (column 4)
            if(!is_null($row->annotation_1)){
                  $annotation = $row->annotation_1;
                } else {
                  if( array_key_exists($row->loop_id, $loop_mapping_table) ){
                    if(!is_null($loop_mapping_table[$row->loop_id]->similar_annotation)) {
                      $annotation = $loop_mapping_table[$row->loop_id]->similar_annotation;
                    } else {
                      $annotation = "No text annotation";
                    }
                  } else {
                    $annotation = "No text annotation";
                  }
                }

            if ($row->status == 1 or $row->status == 3) { // this goes all the way down to valid tables???

                if ( array_key_exists($row->loop_id, $motifs) ) {
                    $motif_id = anchor_popup("motif/view/{$motifs[$row->loop_id]}",
                      $motifs[$row->loop_id]);
                } else {
                  $motif_id = 'Not in a motif group';
                }



              $annotation_and_motif_group = "{$annotation}<br>{$motif_id}";


              // building loop mapping info (column 5)
              if( array_key_exists($row->loop_id, $loop_mapping_table) ){
                // if($row->loop_id != $loop_mapping_table[$row->loop_id]->similar_loop){
                if(is_null($row->annotation_1) && !array_key_exists($row->loop_id, $motifs)){
                  $match_type = $loop_mapping_table[$row->loop_id]->match_type;

                  $similar_loop = anchor_popup("loops/view/{$loop_mapping_table[$row->loop_id]->similar_loop}",
                      $loop_mapping_table[$row->loop_id]->similar_loop);

                  if ( array_key_exists($loop_mapping_table[$row->loop_id]->similar_loop, $motifs) ) {
                    $similar_motif = anchor_popup("motif/view/{$motifs[$loop_mapping_table[$row->loop_id]->similar_loop]}", $motifs[$loop_mapping_table[$row->loop_id]->similar_loop]);
                  } else {
                      $similar_motif = 'NA';
                  }
                  $loop_mapping_info = "{$match_type}<br>{$similar_loop}<br>{$similar_motif}";
                } else {
                  $loop_mapping_info = "";
                }
              } else {
                $loop_mapping_info = "";
              }


                $valid_tables[$loop_type][] = array(array( 'class' => 'loop',
                                                            'data' => $this->get_checkbox($row->loop_id, $row->unit_ids)
                                                        ),
                                                    anchor_popup("loops/view/{$row->loop_id}", $row->loop_id), // turning loop id into link
                                                    str_replace(",", ",<br>", $row->loop_name), //location
                                                    // $motif_id, //motif
                                                    $annotation_and_motif_group, // column 4
                                                    $loop_mapping_info // column 5
                                                    );
            } else {
                // if (!is_null($row->complementary)) {
                //     $annotation = $row->complementary;
                // } elseif (!is_null($row->modifications)) {
                //     $annotation = $row->modifications;
                // } else {
                //     $annotation = $row->nt_signature;
                // }
                $invalid_tables[$loop_type][] = array(array( 'class' => 'loop',
                                                             'data' => $this->get_checkbox($row->loop_id, $row->unit_ids)
                                                         ),
                                                      anchor_popup("loops/view/{$row->loop_id}",
                                                        $row->loop_id),
                                                      $this->make_reason_label($row->status),
                                                      $annotation);
            }
        }
        return array('valid' => $valid_tables, 'invalid' => $invalid_tables);
    }
    function make_reason_label($status)
    {
        return '<label class="label important">' . $this->qa_status[$status] . '</label>';
    }
    function get_checkbox($id, $nt_ids)
    {
        return "<input type='radio' id='{$id}' class='jmolInline' data-coord='{$id}' data-quality='{$id}'>";
    }
    function pdb_exists($pdb_id)
    {
        // does BGSU RNA Site know about this structure?
        $this->db->select('pdb_id')
                 ->from('pdb_info')
                 ->where('pdb_id', $pdb_id)
                 ->limit(1);
        if ( $this->db->get()->num_rows() > 0 ) {
            return true;
        }
        // if not, is it in PDB itself?
        $pdb_rest_url = 'http://www.pdb.org/pdb/rest/describePDB?structureId=';
        $pdb_description = file_get_contents($pdb_rest_url . $pdb_id);
        // when a pdb doesn't exist, $pdb_description == '</PDBdescription>'
        if ( strpos($pdb_description, '<PDBdescription>') === false ) {
            return false;
        } else {
            return true;
        }
    }
    function pdb_is_annotated($pdb_id, $interaction_type)
    {
        //$this->db->select('pdb_analysis_status_id')
        //         ->from('pdb_analysis_status')
        //         ->where('pdb_id', $pdb_id)
        //         ->where("$interaction_type IS NOT NULL");
        //$query = "COUNT(IF(`li`.`type` = `$interaction_type`, 1, NULL)) AS numloops";
        $this->db->select('pi.pdb_id')
                 ->from('pdb_info AS pi')
                 ->where('pi.pdb_id', $pdb_id);
/*
        $this->db->select('pi.pdb_id, li.type')
                 //->select($query, FALSE)
                 ->from('pdb_info AS pi')
                 ->join('loop_info AS li', 'pi.pdb_id = li.pdb_id')
                 ->where('pi.pdb_id', $pdb_id);
*/
        if ( $this->db->get()->num_rows() > 0 ) {
            return True;
        } else {
            return False;
        }
    }
    function _get_unit_ids($pdb_id)
    {
    #    // get correspondences between old and new ids
    #    $this->db->select('old_id, unit_id')
    #             ->from('__pdb_unit_id_correspondence')
    #             ->where('pdb_id', $pdb_id);
    #    $query = $this->db->get();
    #    $unit_ids = array();
    #    foreach ( $query->result() as $row ) {
    #        $unit_ids[$row->old_id] = $row->unit_id;
    #    }
    #
    #    // if no new unit ids are found, fall back on old ids
    #    if ( count($unit_ids) == 0 ) {
    #        $this->db->select('pdb_coordinates_id')
    #                 ->from('__pdb_coordinates')
    #                 ->where('pdb_id', $pdb_id);
    #        $query = $this->db->get();
    #        foreach ( $query->result() as $row ) {
    #            $unit_ids[$row->pdb_coordinates_id] = $row->pdb_coordinates_id;
    #        }
    #    }
        // retrieve new-style IDs from unit_info
        $this->db->select('unit_id')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb_id);
        $query = $this->db->get();
        $unit_ids = array();
        foreach ( $query->result() as $row ) {
            $unit_ids[$row->unit_id] = $row->unit_id;
        }
        return $unit_ids;
    }
    function get_interactions($pdb_id, $interaction_type)
    {
        /* Commented out since we don't have aa-nt annotations yet
        if ( $interaction_type == 'baseaa' ) {
            $unit_ids = $this->_get_unit_ids($pdb_id);
            $this->db->select('uai.na_unit_id, uai.aa_unit_id, uai.annotation, uai.value')
                 ->from('unit_aa_interactions AS uai')
                 ->join('unit_info AS u1', 'uai.na_unit_id = u1.unit_id')
                 ->join('unit_info AS u2', 'uai.aa_unit_id = u2.unit_id')
                 ->where('uai.pdb_id', $pdb_id)
                 #->order_by('number');
                 ->order_by('u1.chain, u1.chain_index, u2.chain, u2.chain_index');
            $query = $this->db->get();
            foreach($query->result() as $row) {
                $na_unit_id[] = $row->na_unit_id;
                $aa_unit_id[] = $row->aa_unit_id;
                $annotation[] = $row->annotation;
                $value[] = $row->value;
            }
            $array_size = count($na_unit_id);
            $html = '';
            for ($i = 0; $i <= ($array_size-1); $i++) {
                // Don't display value for cation-pi interactions
                if ($value[$i] == NULL) {

                    $html .= str_pad('<span>' . $na_unit_id[$i] . '</span>', 38, ' ') .
                             "<a class='jmolInline' id='s{$i}'>" .
                             str_pad( '<span>' . $annotation[$i] . '</span>' , 10, '',STR_PAD_BOTH) .
                             "</a>" .
                             str_pad('<span>' . $aa_unit_id[$i] . '</span>', 38, ' ', STR_PAD_LEFT) .
                             "\n";
                } else {
                    $html .= str_pad('<span>' . $na_unit_id[$i] . '</span>', 38, ' ') .
                             "<a class='jmolInline' id='s{$i}'>" .
                             str_pad( '<span>' . $annotation[$i] . '</span>', 10, '', STR_PAD_BOTH) .
                             "</a>" .
                             str_pad('<span>' . $aa_unit_id[$i] . '</span>', 38, ' ', STR_PAD_LEFT) .
                             "\n";
                }
            }
            return array( 'data'   => $html,
                          'header' => array('#', 'Nucleotide id', 'Amino acid id', "Base-amino acid")
                     );
        }
        */

        $url_parameters = array('basepairs', 'stacking', 'basephosphate', 'baseribose');
        $db_fields      = array('f_lwbp', 'f_stacks', 'f_bphs', 'f_brbs');
        $header_values  = array('Base-pair', 'Base-stacking', 'Base-phosphate', 'Base-ribose');
        $header         = array('#', 'Nucleotide id 1', 'Nucleotide id 2');
        if ( in_array($interaction_type, $url_parameters) ) {
            $targets = array_keys($url_parameters, $interaction_type);
            //print_r(array_values($targets));
            $db_field = $db_fields[$targets[0]];
            $interaction_description = $header_values[$targets[0]];
            $where = "$db_field IS NOT NULL";
        } elseif ( $interaction_type == 'all' ) {
            $targets = array_keys($url_parameters);
            $db_field = implode(',', $db_fields);
            $interaction_description = implode(',', $header_values);
            $where = '(' . implode(' IS NOT NULL OR ', $db_fields) . ')';
        } else {
            return array( 'data'   => array(),
                          'header' => array(),
                          'csv'    => ''
                         );
        }
        $unit_ids = $this->_get_unit_ids($pdb_id);
        $this->db->select('upi.unit_id_1, upi.unit_id_2,' . $db_field)
                 ->from('unit_pairs_interactions AS upi')
                 ->join('unit_info AS u1', 'upi.unit_id_1 = u1.unit_id')
                 ->join('unit_info AS u2', 'upi.unit_id_1 = u2.unit_id')
                 ->where('upi.pdb_id', $pdb_id)
                 ->where('upi.program', 'matlab')
                 ->where($where)
                 #->order_by('number');
                 ->order_by('u1.model, u1.chain, u1.sym_op, u1.chain_index, u2.model, u2.chain, u2.sym_op, u2.chain_index');
        $query = $this->db->get();

        # if length of query is zero, query again for python data
        if ($query->num_rows() == 0) {
            $this->db->select('upi.unit_id_1, upi.unit_id_2,' . $db_field)
            ->from('unit_pairs_interactions AS upi')
            ->join('unit_info AS u1', 'upi.unit_id_1 = u1.unit_id')
            ->join('unit_info AS u2', 'upi.unit_id_1 = u2.unit_id')
            ->where('upi.pdb_id', $pdb_id)
            ->where('upi.program', 'python')
            ->where($where)
            #->order_by('number');
            ->order_by('u1.model, u1.chain, u1.sym_op, u1.chain_index, u2.model, u2.chain, u2.sym_op, u2.chain_index');
            $query = $this->db->get();

        }

        $i = 1;
        $html = '';
        $csv  = '';
        foreach ( $query->result() as $row ) {
            $output_fields = array();
            $csv_fields    = array();
            $csv_fields[0] = $unit_ids[$row->unit_id_1];
            foreach ($targets as $target) {
                if ( isset($row->{$db_fields[$target]}) and ($row->{$db_fields[$target]} != '') ) {
                    $output_fields[] = $row->{$db_fields[$target]};
                    $csv_fields[]    = $row->{$db_fields[$target]};
                } else {
                    $csv_fields[] = '';
                }
            }
            $csv_fields[] = $unit_ids[$row->unit_id_2];
            $ids = $unit_ids[$row->unit_id_1] .','. $unit_ids[$row->unit_id_2];
            $html .= str_pad('<span>' . $unit_ids[$row->unit_id_1] . '</span>', 32, ' ') .
                    "<a class='jmolInline' id='s{$i}'>" .
                      str_pad(implode(', ', $output_fields), 8, ' ', STR_PAD_BOTH) .
                    "</a>" .
             //       '<span style="text-align: right;">' . str_pad($unit_ids[$row->unit_id_2], 22, ' ', STR_PAD_LEFT) . ' <a href="http://rna.bgsu.edu/correspondence/SVS?id=' . $ids . '&format=unique&input_form=True" target="_blank" rel="noopener noreferrer">R3DSVS</a>' . '</span>' .
                    str_pad('<span>' . $unit_ids[$row->unit_id_2]. '</span>', 32, ' ', STR_PAD_LEFT) . ' <a href="http://rna.bgsu.edu/correspondence/SVS?id=' . $ids . '&format=unique&input_form=True" target="_blank" rel="noopener noreferrer">R3DSVS</a>' .
                    "\n";
            $csv .= '"' . implode('","', $csv_fields) . '"' . "\n";
            $i++;
          // print $csv;
        }
        $header2 = array_merge( $header, explode(',', $interaction_description) );
        return array( 'data'   => $html,
                      'header' => array_merge( $header, explode(',', $interaction_description) ),
                      'csv'    => $csv
                     );
    }
    function get_general_info($pdb_id)
    {
        //  QUERY NEEDS TO BE REWRITTEN
        //  NEEDS DATA FROM BOTH pdb_info and chain_info

        // get a list of all chains in the pdb_id
        $this->db->select()
                 ->from('pdb_info AS pi')
                 ->join('chain_info AS ci', 'pi.pdb_id = ci.pdb_id', 'left')
                 ->where('pi.pdb_id', $pdb_id);
        $query = $this->db->get();

        $data['rna_chains'] = 0;
        foreach ($query->result() as $row) {
            $ndb_id = ( $row->ndb_id ) ? $row->ndb_id : $pdb_id;
            if ( $data['rna_chains'] == 0 ) {
                // get this info only once because it applies to all chains
                $data['title'] = $row->title;
                $data['experimental_technique'] = $row->experimental_technique;
                $data['resolution'] = $row->resolution;
                $data['release_date'] = $row->release_date;
                $data['authors'] = $row->authors;
                $data['pdb_url'] = "https://www.rcsb.org/structure/{$pdb_id}";
                $data['ndb_url'] = "http://ndbserver.rutgers.edu/service/ndb/atlas/summary?searchTarget={$ndb_id}";
                $data['NAKB_url'] = "https://www.nakb.org/atlas={$pdb_id}";
                $data['circular_url'] = "http://rna.bgsu.edu/correspondence/circular?id={$pdb_id}&input_form=True";

            }
            // only for RNA chains
            // Todo: add a section for DNA chains as well
            if ( $row->entity_macromolecule_type == 'Polyribonucleotide (RNA)' or
                 $row->entity_macromolecule_type == 'polyribonucleotide' or
                 $row->entity_macromolecule_type == 'DNA/RNA Hybrid' or
                 $row->entity_macromolecule_type == 'polydeoxyribonucleotide/polyribonucleotide hybrid'
            ) {
                $data['rna_chains']++;
                $organisms[] = $row->source;
                $data['rna_compounds'][] = array("chain"      => $row->chain_id,
                                                 "chain_name" => $row->chain_name,
                                                 "compound"   => $row->compound,
                                                 "length"     => $row->chain_length,
                                                 "organism"   => $row->source);
            }
            // for all chains
            $compounds[] = $row->compound;
        }
        if ( empty($organisms) ) {
          $organisms[] = 'synthetic';
        }
        $data['non_rna_chains'] = $query->num_rows() - $data['rna_chains'];
        $data['organisms'] = implode(', ', array_unique($organisms));
        $data['compounds'] = implode(', ', $compounds);
        return $data;
    }
    function get_latest_nr_release($pdb_id)
    {
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->order_by('date', 'desc')
                 ->limit(1);
        $result = $this->db->get()->row();
        return $result->nr_release_id;
    }
    function get_nrlist_info($pdb_id)
    {
        // get the latest nr release
        $data['latest_nr_release'] = $this->get_latest_nr_release($pdb_id);
        // get nr equivalence classes
        $this->db->select('nl.name')
                 ->select_min('nc.rank')
                 ->select('COUNT(nl.name) AS count')
                 ->from('nr_class_rank AS nc')
                 ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->where('ii.pdb_id', $pdb_id)
                 ->where('nl.nr_release_id', $data['latest_nr_release'])
                 ->group_by('nl.nr_class_id')
                 ->group_by('nl.name');
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data['nr_classes'][] = $row->name;
            $data['nr_urls'][$row->name] = anchor('nrlist/view/' . $row->name, $row->name);
            # $data['representatives'][$row->name] = $row->rep;
            if ($row->rank == 0){
                $data['representatives'][$row->name] = 1;
            }
            else {$data['representatives'][$row->name] = 0;}
            $data['count'][$row->name] = $row->count;
        }
        return $data;
    }
    function get_loops_info($pdb_id)
    {
        $this->db->select('count(loop_id) as counts, type')
                 ->from('loop_info')
                 ->where('pdb_id', $pdb_id)
                 ->group_by('type');
        $query = $this->db->get();
        $data['loops'] = array();
        foreach ($query->result() as $row) {
            $data['loops'][$row->type] = $row->counts;
        }

        // add zeros if some loop types are not present
        foreach ( array('IL', 'HL', 'J3') as $loop_type ) {
            if ( !array_key_exists($loop_type, $data['loops']) ) {
                $data['loops'][$loop_type] = 0;
            }
        }

        $data['loops']['url'] = anchor('pdb/' . $pdb_id . '/motifs', 'More');

        return $data;
    }
    function get_latest_motif_release($motif_type)
    {
        // this is unreliable as a way to find most recent motif assignment

        $this->db->select('ml_release_id')
                 ->from('ml_releases')
                 ->order_by('date', 'desc')
                 ->where('type', $motif_type)
                 ->limit(1);

        $result = $this->db->get()->row();

        return $result->ml_release_id;
    }
    function get_motifs_info($pdb_id, $motif_type)
    {
        $latest_release = $this->get_latest_motif_release($motif_type);
        // count motifs
        $this->db->select('count(distinct motif_id) as counts')
                 ->from('ml_loops')
                 ->where('ml_release_id', $latest_release)
                 ->like('loop_id', $motif_type . '_' . $pdb_id, 'right');
        $result = $this->db->get()->row();
        return $result->counts;
    }
    function get_pairwise_info($pdb_id, $interaction)
    {
        $this->db->select("count($interaction)/2 as counts")
                 ->from('unit_pairs_interactions')
                 ->where('program', 'matlab')
                 ->where('pdb_id', $pdb_id);
        if ( $interaction == 'f_bphs' ) {
            $this->db->where("char_length($interaction) = 4");
        } else {
            $this->db->where("char_length($interaction) = 3");
        }
        $result = $this->db->get()->row();
        return number_format($result->counts, 0);
    }
    function get_baseaa_info($pdb_id)
    {
        $this->db->select("count(na_unit_id) as counts")
                 ->from('unit_aa_interactions')
                 ->where('pdb_id', $pdb_id);
        $result = $this->db->get()->row();
        return number_format($result->counts, 0);
    }
    function get_related_structures($pdb_id)
    {
        $pdb_id = strtoupper($pdb_id);
        $latest_nr_release = $this->get_latest_nr_release($pdb_id);
        // choose the equivalence class
        $this->db->select('cl.nr_class_id')
                 ->select('cl.name')
                 ->from('nr_class_rank AS ch')
                 ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
                 ->join('ife_info AS ii', 'ch.ife_id = ii.ife_id')
                 ->where('ii.pdb_id', $pdb_id)
                 ->where('cl.nr_release_id', $latest_nr_release)
                 ->where('cl.resolution', 'all');
        $result = $this->db->get();
        if ( $result->num_rows() == 0 ) {
            $equivalence_class = 'Not a member of any equivalence class, possibly due to the absence of complete nucleotides.';
            $equivalence_class_name = "";
            $equivalence_class_found = False;
        } else {
            $equivalence_class = $result->row()->nr_class_id;
            $equivalence_class_name = $result->row()->name;
            $equivalence_class_found = True;
        }
        $pdbs = array();
        $representative = Null;
        if ( $equivalence_class_found ) {
            // choose all structures from the selected equivalence class
            $this->db->select('ii.pdb_id')
                     ->from('nr_class_rank AS np')
                     ->join('ife_info AS ii', 'np.ife_id = ii.ife_id')
                     # no need for this limitation
                     # ->where('nr_release_id', $latest_nr_release)
                     ->where('nr_class_id', $equivalence_class)
                     ->order_by('rank', 'asc');
            $query = $this->db->get();
            $isFirst = True;
            foreach($query->result() as $row) {
                if ( $isFirst ) {
                    $representative = $row->pdb_id;
                    $isFirst = False;
                }
                if ( $row->pdb_id != $pdb_id ) {
                    $pdbs[] = $row->pdb_id;
                }
            }
        }
        return array('related_pdbs' => $pdbs,
                     'eq_class' => $equivalence_class_name,
                     'representative' => $representative);
    }

    function get_ordered_nts($pdb_id)
    {
        $aa = 'aa';
        $this->db->select('ui.unit_id as id, ui.model, ui.chain, ui.sym_op, ui.unit as sequence, ui.unit_type_id')
                 ->select_min('ui.sym_op')
                 ->from('unit_info AS ui')
                 ->where('ui.pdb_id', $pdb_id)
                 ->where('chain_index is NOT NULL', NULL, FALSE)
                 ->where('model','1')
                 ->order_by('ui.model', 'asc')
                 ->order_by('ui.sym_op', 'asc')
                 ->order_by('ui.chain', 'asc')
                 ->order_by('ui.chain_index', 'asc')
                 ->order_by('ui.alt_id', 'desc')
                 ->group_by('ui.pdb_id, ui.model, ui.sym_op, ui.chain, ui.number, ui.unit, ui.ins_code');
        $query = $this->db->get();
        $chain_data = array();
        foreach($query->result() as $row) {

            // modified nts can have null unit_type_id, but avoid 'aa'
            if (is_null($row->unit_type_id) or $row->unit_type_id != 'aa') {
                if ($row->sym_op == '1_555') {
                    $chain = $pdb_id . '|' . $row->model . '|' . $row->chain;
                } else {
                    $chain = $pdb_id . '|' . $row->model . '|' . $row->chain . ' ' . $row->sym_op;
                }

                if ( !array_key_exists($chain, $chain_data) ){
                  $chain_data[$chain] = array('id' => $chain,
                                              'chain' => $chain,
                                              'nts' => array());
                }

                $chain_data[$chain]['nts'][] = array('id' => $row->id,
                                                 'sequence' => $row->sequence);
            }


        }

        return $chain_data;
    }

    function get_ordered_nts_very_old($pdb_id)
    {
        $this->db->select('ui.unit_id as id, ui.chain, ui.unit as sequence')
                 ->select_min('ui.sym_op')
                 ->from('unit_info AS ui')
                 ->join('ife_info AS ii', 'ui.pdb_id = ii.pdb_id AND ui.model = ii.model')
                 ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id AND ii.model = ic.model')
                 ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id AND ui.pdb_id = ci.pdb_id AND ui.chain = ci.chain_name')
                 ->where('ui.pdb_id', $pdb_id)
                 ->where('chain_index is NOT NULL', NULL, FALSE)
                 ->group_by('ui.pdb_id, ui.model, ui.chain, ui.number, ui.unit, ui.alt_id')
                 ->group_by('ui.ins_code, ui.chain_index')
                 ->order_by('ui.chain', 'asc')
                 ->order_by('ui.number', 'asc');
        $query = $this->db->get();
        $chain_data = array();
        foreach($query->result() as $row) {
            $chain = $row->chain;
            if ( !array_key_exists($chain, $chain_data) ){
              $chain_data[$chain] = array('id' => 'chain-' + $chain,
                                          'nts' => array());
            }

            $chain_data[$chain]['nts'][] = array('id' => $row->id,
                                                 'sequence' => $row->sequence);
        }
        return array_values($chain_data);
    }
    function get_airport($pdb_id)
    {
        $new_result = '';
        $table = 'pdb_airport';
        if (! $this->db->table_exists($table)) {
            return false;
        }
        //
        //  Adding a wrapper around the ss_unit_positions code (2017-06-13).
        //
        //  This block isn't performing well, and the problem is exacerbated
        //    when it is called for a structure which has no presence in the
        //    ss_* hierarchy.
        //
        $this->db->select('pdb_id')
                 ->from('ss_pdb_mapping')
                 ->where('pdb_id', $pdb_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            // process ss_unit_positions
            //  Revision:  performance of view ss_unit_positions is horrible, but
            //    the underlying query appears to perform better.
            /*
            $this->db->select()
                     ->from('ss_unit_positions')
                     ->where('pdb_id', $pdb_id);
            */
            $this->db->select('UI.unit_id, SPM.pdb_id, UI.model, SPM.chain_name AS chain, UI.number')
                     ->select('UI.unit, UI.alt_id, UI.ins_code, UI.sym_op, UI.chain_index')
                     ->select('UI.unit_type_id, SP.index, SP.ss_id, SP.x_coordinate')
                     ->select('SP.y_coordinate')
                     ->select("IF(UI.unit_id IS NOT NULL, 1, 0) AS 'is_resolved'",false)
                     ->from('ss_pdb_mapping AS SPM')
                     ->join('ss_exp_seq_position_mapping AS ESPM', 'ESPM.ss_exp_seq_mapping_id = SPM.ss_exp_seq_mapping_id')
                     ->join('ss_positions AS SP', 'SP.ss_position_id = ESPM.ss_position_id','left')
                     ->join('exp_seq_unit_mapping AS ESUM','ESUM.exp_seq_position_id = ESPM.exp_seq_position_id','left')
                     ->join('unit_info AS UI', 'UI.unit_id = ESUM.unit_id', 'left')
                     ->where('ISNULL(UI.pdb_id)')
                     ->or_where('UI.pdb_id = SPM.pdb_id')
                     ->where('SPM.pdb_id', $pdb_id)
                     ->group_by('SP.ss_position_id');
;
            $query = $this->db->get();
            $nts_data = array();
            $new_json = array();
            $model = '';
            $create = 0;
            foreach ($query->result() as $row) {
                $create = 1;
                if ($row->unit_id){
                    $rowArr = array(
                        'y' => $row->y_coordinate,
                        'x' => $row->x_coordinate,
                        'id' => $row->unit_id,
                        'sequence' => $row->unit
                    );
                    $nts_data[] = $rowArr;
                }
                $model = !($model) ? $row->model : $model;
            }
            if ($create == 1) {
                $new_json = array(
                    'nts'  => $nts_data,
                    'id'   => $row->pdb_id . '|' . $model . '|' . $row->chain,
                    'name' => 'Chain ' . $row->chain
                );
                $json = '[' . json_encode($new_json, JSON_NUMERIC_CHECK) . ']';
            }
        } else {
            $this->db->select('json_structure')
                     ->from($table)
                     ->where('pdb_id', $pdb_id);
            $result = $this->db->get()->row();
            $json = ($result) ? $result->json_structure : "";
        }
        return ($json) ? $json : false;
    }
    function get_longrange_bp($pdb)
    {
        $this->db->select('U1.unit_id as nt1')
                 ->select('U2.unit_id as nt2')
                 ->select('upi.f_lwbp as family')
                 ->select('upi.f_crossing as crossing')
                 ->from('unit_pairs_interactions AS upi')
                 ->join('unit_info as U1', 'U1.unit_id = upi.unit_id_1')
                 ->join('unit_info as U2', 'U2.unit_id = upi.unit_id_2')
                 ->where('upi.pdb_id', $pdb)
                 ->where('upi.program', 'matlab')
                 ->where('f_crossing > 3')
                 ->where('f_lwbp is not null');
        $query = $this->db->get();
        if (!$query->num_rows()) {
            return array();
        }
        foreach($query->result() as $row) {
            $longrange[] = $row;
        }
        return $longrange;
    }
}
/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */
