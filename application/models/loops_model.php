<?php
class Loops_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

        // Call the Model constructor
        parent::__construct();
    }

    function is_valid_loop_id($id)
    {
        $this->db->select('loop_id')
                 ->from('loop_info')
                 ->where('loop_id', $id);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function get_loop_list($pdb_id)
    {
/*
        $this->db->select('group_concat(unit_id) AS unit_ids, loop_info.loop_id', FALSE)
                 ->from('loop_info')
                 ->join('loop_positions', 'loop_info.loop_id=loop_positions.loop_id')
                 ->join('pdb_unit_id_correspondence', 'loop_positions.nt_id=pdb_unit_id_correspondence.old_id')
                 ->where('loop_info.pdb_id', $pdb_id)
                 ->group_by('loop_info.loop_id');
*/
        ### TESTING THIS QUERY -- REMOVE LINES 30-37 WHEN APPROVED
        // $this->db->select('group_concat(unit_id) AS unit_ids, LI.loop_id', FALSE)
        //          ->from('loop_info AS LI')
        //          ->join('loop_positions AS LP', 'LI.loop_id = LP.loop_id')
        //          ->where('LI.pdb_id', $pdb_id)
        //          ->group_by('LI.loop_id');
        // $query = $this->db->get();

        // if ( $query->num_rows() > 0 ) {
        //     $data = array();
        //     foreach($query->result() as $row) {
        //         $data[] = '"' . implode('","', array($row->loop_id, $row->unit_ids)) . '"';
        //     }
        //     $table = implode("\n", $data);
        // } else {
        //     $table = 'No loops found';
        // }

        // return $table;


        $this->db->select('loop_info.loop_id, group_concat(unit_id order by position_2023) as unit_ids')
                 ->from('loop_info')
                 ->join('loop_positions', 'loop_info.loop_id = loop_positions.loop_id')
                 ->where('loop_info.pdb_id', $pdb_id)
                 ->group_by('loop_info.loop_id');
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $data = array();
            foreach($query->result() as $row) {
                $data[] = '"' . implode('","', array($row->loop_id, $row->unit_ids)) . '"';
            }
            $table = implode("\n", $data);
        } else {
            $table = 'No loops found';
        }

        return $table;

    }


    function get_loop_list_with_breaks($pdb_id)
    {
        // Query for all loops in this pdb file
        // Concatenate unit_ids and borders into a single string
        // Note that if that string is too long, it will be truncated
        // The default length is 1024, but we set it to a higher number below
        // A loop with 64 nucleotides has length around 1030; 5000 should cover every conceivable loop, right?
        $this->db->select('group_concat(unit_id order by position_2023) AS unit_ids, group_concat(border order by position_2023) AS borders, LI.loop_id', FALSE)
                 ->from('loop_info AS LI')
                 ->join('loop_positions AS LP', 'LI.loop_id = LP.loop_id')
                 ->where('LI.pdb_id', $pdb_id)
                 ->group_by('LI.loop_id');
        $query = $this->db->query("SET SESSION group_concat_max_len = 5000");
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $data = array();
            foreach($query->result() as $row) {
                $data[] = '"' . implode('","', array($row->loop_id, $row->unit_ids, $row->borders)) . '"';
            }
            $table = implode("\n", $data);
        } else {
            $table = 'No loops found';
        }

        return $table;
    }

    function get_loop_info($id)
    {
        $result = array();

        // // info from loops_all
        // $this->db->select('length, seq')
        //          ->from('loop_info')
        //          ->where('loop_id',$id);
        // $query = $this->db->get();

        // if ($query->num_rows() > 0) {
        //     $loop_info = $query->row();
        //     $result['length'] = $loop_info->length;
        //     $result['sequence'] = $loop_info->seq;
        // } else {
        //     $result['length'] = '';
        //     $result['sequence'] = '';
        // }

            /*
            Having a new method to get length of sequence,
            This method is not using any more
            */
        // info from loops_all
    //     $this->db->select('length')
    //             ->from('loop_info')
    //             ->where('loop_id',$id);
    //    $query = $this->db->get();

    //    if ($query->num_rows() > 0) {
    //        $loop_info = $query->row();
    //        $result['length'] = $loop_info->length;
    //    } else {
    //        $result['length'] = '';
    //    }

        // get Unit ID section
        $this->db->select('unit_id, border')
                 ->from('loop_positions')
                 ->order_by('position_2023','asc')
                 ->where('loop_id',$id);
        $query = $this->db->get();

        $sequence = array();
        $count_border = 0;
        $length_sequence = 0;
        $modified_nucleotides = array();

        foreach ($query->result() as $row) {
            $parts = explode('|', $row->unit_id);
            // $sequence[] = $parts[3];
            if($row->border==1){
                $count_border++;
                if($count_border != 3 and $count_border != 5){
                    $sequence[] = $parts[3];
                }else{
                    $sequence[] = '*' . $parts[3];
                }
            }else{
                if (strlen($parts[3]) == 1){
                    $sequence[] = $parts[3];
                }else{
                    $sequence[] = '(' . $parts[3] . ')';
                    $modified_nucleotides[] = $parts[3];
                }
            }
            $length_sequence += 1;
        }

        $result['length'] = $length_sequence;
        $result['sequence'] = implode('', $sequence);

        $unique_modifications = array_values(array_unique($modified_nucleotides));
        $modifications = implode(', ', $unique_modifications);






        // qa info
        $this->db->select('status, modifications, nt_signature, complementary')
                 ->from('loop_qa')
                 ->where('loop_id',$id)
                 ->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $loop_qa = $query->row();
            switch ($loop_qa->status) {
                case 1:
                    $result['qa'] = 'Valid loop';
                    break;
                case 2:
                    $result['qa'] = 'Missing nucleotides: ' . $loop_qa->nt_signature;
                    break;
                case 3:
                    $result['qa'] = 'Modified nucleotides: ' . $modifications;
                    break;
                case 4:
                    $result['qa'] = 'Abnormal chains';
                    break;
                case 5:
                    $result['qa'] = 'Incomplete nucleotides: ' . $loop_qa->nt_signature;
                    break;
                case 6:
                    $result['qa'] = 'Self-complementary: ' . $loop_qa->complementary;
                    break;
            }
        } else {
            $result['qa'] = 'QA data not found';
        }

        // get Unit ID section
        $this->db->select('unit_id, border')
                 ->from('loop_positions')
                 ->order_by('position_2023','asc')
                 ->where('loop_id',$id);
        $query = $this->db->get();

        $unit_ids = array();
        $count_border = 0;

        foreach ($query->result() as $row) {
            if($row->border==1){
                $count_border++;
                if($count_border != 3 and $count_border != 5){
                    $unit_ids[] = $row->unit_id . '<br>';
                }else{
                    $unit_ids[] = '* <br>' . $row->unit_id . '<br>';
                }
            }else{
                $unit_ids[] = $row->unit_id . '<br>';
            }
        }
        $result['unit_ids'] = implode('  ', $unit_ids);

        // get list of bulges
        $this->db->select('unit_id')
                 ->from('loop_positions')
                 ->where('loop_id',$id)
                 ->where('bulge',1)
                 ->order_by('position');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $bulges = array();

            foreach ($query->result() as $row) {
                $parts = explode('|', $row->unit_id);
                $bulges[] = $parts[4] . $parts[3];
            }

            $result['bulges'] = implode(', ', $bulges);
        } else {
            $result['bulges'] = "None detected";
        }

        return $result;
    }

    function get_pdb_info($id)
    {
        $result = array();
        //general pdb info
        $result['pdb'] = substr($id,3,4);
        $result['rna3dhub_link'] = anchor_popup('pdb/' . $result['pdb'] . '/motifs', 'RNA 3D Hub');
        $result['pdb_link'] = anchor_popup('https://www.rcsb.org/structure/' . $result['pdb'], 'PDB');
        $result['NAKB_link'] = anchor_popup('https://www.nakb.org/atlas=' . $result['pdb'], 'NAKB');


        $this->db->select('title, experimental_technique, resolution')
                 ->from('pdb_info')
                 ->where('pdb_id',$result['pdb'])
                 ->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $pdb_info = $query->row();
            $result['pdb_desc'] = $pdb_info->title;
            $result['pdb_exptechnique'] = $pdb_info->experimental_technique;

            if ($pdb_info->resolution == Null) {
                $result['pdb_resolution'] = '';
            } else {
                $result['pdb_resolution'] = $pdb_info->resolution . ' &Aring;';
            }
        } else {
            $result['pdb_desc'] = '';
            $result['pdb_exptechnique'] = '';
            $result['pdb_resolution'] = '';
        }

        // representative set equivalence class info
        // get latest Representative Set release id
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $query = $this->db->get();
        $release = $query->row();

        // get equivalence classes
        $this->db->select('nr_class_name')
                 ->from('nr_pdbs')
                 ->where('pdb_id',$result['pdb'])
                 ->where('nr_release_id', $release->nr_release_id);
        $query = $this->db->get();

        $nr_classes = array();
        foreach ($query->result() as $row) {
            $nr_classes[] = anchor_popup('nrlist/view/' . $row->nr_class_name, $row->nr_class_name);
        }
        $result['nr_classes'] = implode(', ', $nr_classes);

        return $result;
    }

    function get_current_motif_id_from_loop_id($id)
    {
        // this does not work!
        // deprecated as of November 2022


        // get current motif release id
        $this->db->select('ml_release_id')
                 ->from('ml_releases')
                 ->order_by('date','desc')
                 ->where('type',substr($id, 0, 2))
                 ->limit(1);
        $query = $this->db->get();
        $release = $query->row();

        // get motif id
        $this->db->select('motif_id','ml_release_id')
                ->from('ml_loops')
                ->where('loop_id',$id);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $motif = $query->row();
            return array('motif_id' => $motif->motif_id,
                         'release_id' => $release->ml_release_id);
        } else {
            return NULL;
        }

    }

    function get_most_recent_motif_assignment($loop_id)
    {
        // This technique works properly

        $loop_type = substr($loop_id, 0, 2);
        $this->db->select('ML.motif_id as motif_id, MR.ml_release_id as release_id')
                 ->from('ml_loops AS ML')
                 ->join('ml_releases AS MR', 'MR.ml_release_id=ML.ml_release_id')
                 ->where('ML.loop_id', $loop_id)
                 ->where('MR.type', $loop_type)
                 ->order_by('MR.date', 'desc');
        $query = $this->db->get();
        if ( $query->num_rows() == 0 ) {
            return NULL;
        } else {
            $result = $query->result();
            return array('motif_id'   => $result[0]->motif_id,
                         'release_id' => $result[0]->release_id);
        }
    }

    function get_motif_info($id)
    {
        $result = array();
        $motif = $this->get_most_recent_motif_assignment($id);

        // get motif annotations
        $this->db->select()
        ->from('loop_annotations')
        ->where('loop_id', $id);
        $query = $this->db->get();
        $result['annotation_1'] = 'No text annotation';
        $result['annotation_2'] = 'No text annotation';
        if ($query->num_rows >0) {
            $annotation_1 = $query->row()->annotation_1;
            if ($annotation_1 != Null and $annotation_1 != 'NULL') {
                $result['annotation_1'] = $annotation_1;
            }
            // get annotation 2
            $annotation_2 = $query->row()->annotation_2;
            if ($annotation_2 != Null and $annotation_2 != 'NULL') {
                $result['annotation_2'] = $annotation_2;
            }
        }
        if ($motif != NULL) {
            $result['motif_id'] = $motif['motif_id'];
            $result['motif_url'] = anchor_popup('motif/view/' . $motif['motif_id'], $motif['motif_id']);

            // get basepair signature
            $this->db->select()
                     ->from('ml_motif_annotations')
                     ->where('motif_id', $result['motif_id']);
            $query = $this->db->get();
            $annotation = $query->row();

            if ($query->num_rows()>0) {
                $result['bp_signature'] = $annotation->bp_signature;
            } else {
                $result['bp_signature'] = 'Not available';
            }

            // get number of motif instances
            $this->db->select()
                     ->from('ml_loops')
                     ->where('ml_release_id',$motif['release_id'])
                     ->where('motif_id', $motif['motif_id']);
            $query = $this->db->get();
            $result['motif_instances'] = $query->num_rows();
        } else {
            $result['motif_id'] = "Not in a motif group";
            $result['motif_url'] = "Not in a motif group";
            $result['bp_signature'] = 'Not available';
            $result['motif_instances'] = 0;
        }

        return $result;
    }

    function get_nearby_chains($loop_id,$distance=10)
    {
        $result = array();
        $result['proteins'] = array();
        // $result['rna_chains'] = array(); # standard name array edit
        $this->load->model('Ajax_model', '', TRUE);

        $unit_ids = $this->Ajax_model->get_loop_units($loop_id);

        $neighbor_units = $this->Ajax_model->get_neighboring_units($unit_ids,$distance);

        if (count($neighbor_units) > 0) {

            $known_chains = array();
            foreach ($unit_ids as $ui) {
                $fields = explode('|',$ui);
                $pdb_id = $fields[0];
                $known_chains[] = $fields[2];
            }

            $new_chains = array();
            foreach ($neighbor_units as $nu) {
                $fields = explode('|',$nu);
                $chain = $fields[2];

                if (!in_array($chain,$known_chains)) {
                    $known_chains[] = $chain;
                    $new_chains[] = $chain;
                }
            }

            if (count($new_chains) > 0) {
                $this->db->select('chain_name, compound')
                         ->from('chain_info')
                         ->where('pdb_id', $pdb_id)
                         ->where_in('chain_name', $new_chains);
                $query = $this->db->get();

                foreach ($query->result() as $row) {
                    $result['proteins'][$row->chain_name]['description'] = $row->compound;
                }


                // Possibly replace with standardized. #
                $this->db->select('chain, value')
                            ->from('chain_property_value')
                            ->where('pdb_id', $pdb_id)
                            ->where('property', 'standardized_name')
                            ->where_in('chain', $new_chains);
                $query = $this->db->get();

                foreach ($query->result() as $row) {
                    $result['proteins'][$row->chain]['description'] = $row->value;
                }
            }
        }

        return $result;
    }


    function get_current_chains($loop_id)
    {
        $result = array();
        $result['current_chains'] = array();
        // $result['rna_chains'] = array(); # standard name array edit
        $this->load->model('Ajax_model', '', TRUE);

        $unit_ids = $this->Ajax_model->get_loop_units($loop_id);

        $current_chains = array();
        foreach ($unit_ids as $ui) {
            $fields = explode('|',$ui);
            $pdb_id = $fields[0];
            $current_chains[] = $fields[2];
        }

        $this->db->select('chain_name, compound')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb_id)
                 ->where_in('chain_name', $current_chains);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $result['current_chains'][$row->chain_name]['description'] = $row->compound;
        }


        // Possibly replace with standardized. #
        $this->db->select('chain, value')
                    ->from('chain_property_value')
                    ->where('pdb_id', $pdb_id)
                    ->where('property', 'standardized_name')
                    ->where_in('chain', $current_chains);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $result['proteins'][$row->chain]['description'] = $row->value;
        }

        return $result;
    }


    function get_current_motif_release($motif_type)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type', $motif_type)
                 ->order_by('date', 'desc')
                 ->limit(1);
        $query = $this->db->get();
        $row = array_shift($query->result());
        return $row->ml_release_id;
    }

    function get_similar_loops($id)
    {
        $where = "ls.disc > 0 AND (ls.loop_id_1='$id' OR ls.loop_id_2='$id')";
        $this->db->select('ls.loop_id_1, ls.loop_id_2, ls.disc, lq.status, lq.message')
                 ->from('loop_searches AS ls')
                 ->join('loop_search_qa AS lq','ls.loop_id_1 = lq.loop_id_1 AND ls.loop_id_2 = lq.loop_id_2','left')
                 ->where($where, NULL, FALSE)
                 ->order_by('ls.disc', 'asc');
        $query = $this->db->get();

        $matches = array();
        $table = array();
        $count = 0;
        $motif = $this->get_current_motif_id_from_loop_id($id);
        $ml_release_id = $this->get_current_motif_release(substr($id, 0, 2));

        foreach ($query->result() as $row) {
            // establish what loop is the match of $id
            if ($row->loop_id_1 == $id) {
                $match = $row->loop_id_2;
            } else {
                $match = $row->loop_id_1;
            }

            // exclude rows with reversed orientation of loop_id_1 and loop_id_2
            if ( array_key_exists($match, $matches) ) {
                continue;
            } else {
                $matches[$match]='';
            }

            $count++;

            $this->db->select()
                     ->from('ml_loops')
                     ->where('loop_id',$match)
                     ->where('ml_release_id',$ml_release_id);
            $q = $this->db->get();

            if ($q->num_rows() > 0) {
                $result = $q->row();
            } else {
                $result = '';
            }

            // compose the message
            if ($row->status == 4) {
                $message = 'Unmatched basepair: ' . $row->message;
            } elseif ($row->status == 5) {
                $message = 'Unmatched near pair: ' . $row->message;
            } elseif ($row->status == 6) {
                $message = 'Unmatched stacking: ' . $row->message;
            } elseif ($row->status == 7) {
                $message = 'Basepair mismatch: ' . $row->message;
            } elseif ($row->status == 7) {
                $message = 'Basepair-basestacking mismatch: ' . $row->message;
            } else {
                $message = '';
            }

            $radiobutton = "<input type='radio'
                                   name='g'
                                   id='s{$count}'
                                   class='jmolTools-loop-pairs'
                                   data-coord='{$id}:{$match}'>
                            <label for='s{$count}'>$match</label>
                            <span class='loop_link'>" . anchor_popup("loops/view/$match", '&#10140;') . "</span>";

            if ( $result == '' ) {
                $motif_link = 'not annotated yet';
            } elseif ($result->motif_id == $motif['motif_id'] ) {
                $motif_link = '<span class="label success">' .
                              anchor_popup('motif/view/' . $result->motif_id, $result->motif_id)
                              . '</span>';
            } else {
                $motif_link = anchor_popup('motif/view/' . $result->motif_id, $result->motif_id);
            }

            $table[] = array($count,
                             array('data' => $radiobutton, 'class' => 'loop'),
                             number_format($row->disc, 4),
                             $motif_link,
                             $message);
        }

        return $table;
    }

    function get_mapped_loop($id){
        $this->db->select('lm.loop_id')
                ->select('lm.query_loop_id AS mapped_loop')
                ->select('lm.discrepancy')
                ->select('lm.match_type')
                ->from('loop_mapping AS lm')
                // ->join('loop_annotations AS la', 'lm.query_loop_id = la.loop_id', 'right')
                ->where('lm.loop_id', $id)
                ->order_by('lm.loop_mapping_id', 'desc') // use (...,'desc') if opposite order
                ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row){
            // $loop_mapping = $row;
            return($row);
        }

        // return $loop_mapping;
    }

    function get_loop_stats()
    {
        // get loop counts group by loop type
        $this->db->select('status, count(status) as counts, substr(loop_id, 1, 2) as loop_type',FALSE)
                 ->from('loop_qa')
                 ->group_by(array('status', 'loop_type'));
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $results[$row->loop_type][$row->status] = $row->counts;
        }

        foreach (array_keys($results) as $loop_type) {
            $tables[$loop_type][] = array(
                                        array_sum(array_values($results[$loop_type])),
                                        $this->make_view_loops_link($results,$loop_type,1),
                                        $this->make_view_loops_link($results,$loop_type,2),
                                        $this->make_view_loops_link($results,$loop_type,3),
                                        $this->make_view_loops_link($results,$loop_type,4),
                                        $this->make_view_loops_link($results,$loop_type,5),
                                        $this->make_view_loops_link($results,$loop_type,6));
        }

        return $tables;
    }

    function make_view_loops_link($counts,$motif_type,$status)
    {
        if (!array_key_exists($status,$counts[$motif_type])) {
            return '0';
        }

        $type = $this->qa_status[$status];
        if ($type == 'complementary' and $motif_type != 'IL') {
            return 'N/A';
        }
        else {
            return anchor(
                          base_url(array('loops','view_all',$type,$motif_type)),
                          $counts[$motif_type][$status]
                          );
        }
    }

    function make_ligand_link($s)
    {
        // http://www.pdb.org/pdb/images/UR3_300.png
        $parts = explode(',',$s);
        $links = '';
        foreach ($parts as $part) {
            $part = trim($part);
            $links .= "<a href='http://www.rcsb.org/pdb/ligand/ligandsummary.do?hetId={$part}' target='_blank'>$part</a> ";
        }
        return $links;
    }

    function get_loops($type,$motif_type,$num,$offset)
    {
        $verbose = $type;
        $type = array_search($type, $this->qa_status);
        $this->db->select('qa.loop_id, qa.modifications, qa.nt_signature, qa.complementary, li.seq')
                 ->from('loop_qa AS qa')
                 ->join('loop_info AS li','qa.loop_id = li.loop_id')
                 ->where('status',$type)
                 ->where('type',$motif_type)
                 ->order_by('li.loop_id')
                 ->limit($num,$offset);
        $query = $this->db->get();

        $i = 1;
        foreach ($query->result() as $row) {
            if ($verbose == 'modified') {
                $this->table->set_heading('#','id','PDB','Modifications');
                $info = $this->make_ligand_link($row->modifications);
            } elseif ($verbose == 'complementary') {
                $this->table->set_heading('#','id','PDB','Info');
                $info = $row->complementary;
            } elseif ($verbose != 'valid') {
                $this->table->set_heading('#','id','PDB','Info');
                $info = $row->nt_signature;
            } elseif ($verbose == 'valid') {
                $this->table->set_heading('#','id','PDB','Info');
                $info = $row->seq;
            } else {
                $this->table->set_heading('#','id','PDB','Info');
                $info = '';
            }

            $this->table->add_row($offset + $i,
                                          $this->make_radio_button($row->loop_id),
                                          '<a class="pdb">' . substr($row->loop_id,3,4) . '</a>',
                                          $info);

            $i++;
        }

        if ($query->num_rows() == 0) {
            $data['table'] = "No $type $motif_type loops were found";
        } else {
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
            $this->table->set_template($tmpl);
        }

        return $this->table->generate();
    }

    function make_radio_button($id) {
        $loop_link = anchor_popup("loops/view/$id", '&#10140;');
        return array('data' => "<label><input type='radio' class='jmolInline' data-coord='{$id}'
                id={$id} data-quality='{$id}' name='l'><span>{$id}</span>
                <span class='loop_link'>{$loop_link}</span></label>", 'class' => 'loop');
    }

    function get_loops_count($type,$motif_type)
    {
        $type = array_search($type, $this->qa_status);
        $this->db->from('loop_qa')
                 ->where('status',$type)
                 ->like('loop_id',$motif_type,'after');
        return $this->db->count_all_results();
    }

    function initialize_sfdata()
    {
        $this->q   = $this->query_dcc();
        $this->avg = $this->get_averages();
        $this->low_is_good = array('mapman_Biso_mean','mapman_real_space_R','sfcheck_B_iso_main_chain',
        'sfcheck_B_iso_side_chain','sfcheck_connect','sfcheck_density_index_side_chain','sfcheck_density_index_main_chain',
        'sfcheck_real_space_R','sfcheck_real_space_R_side_chain','sfcheck_shift','sfcheck_shift_side_chain');

        $this->high_is_good = array('mapman_correlation','mapman_occupancy_mean','sfcheck_correlation',
        'sfcheck_correlation_side_chain');
    }

    function get_graphs()
    {
        $url = 'http://rna.bgsu.edu/img/MotifAtlas/dcc_loops';
        if ($handle = opendir('/Servers/rna.bgsu.edu/img/MotifAtlas/dcc_loops')) {
            /* This is the correct way to loop over the directory. */
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != ".DS_Store") {
                    $graphs[] = $entry;
                }
            }
            closedir($handle);
        }
        $text = '';
        foreach ($graphs as $graph) {
            $text .= <<<EOT
              <li>
                <a href="{$url}/{$graph}" rel='g'>
                  <img class="thumbnail span4" src="$url/{$graph}" alt="">
                  $graph
                </a>
              </li>
EOT;
        }
        return $text;
    }

    function query_dcc()
    {
        // SELECT LOOP_id,`mltest`.`dcc_residues`.* FROM `ml_loop_positions`
        // JOIN `mltest`.`dcc_residues`
        // ON nt_id = `mltest`.`dcc_residues`.`dcc_residues_id`
        // WHERE `ml_loop_positions`.release_id = '0.5'
        // ORDER BY loop_id ASC

        // SELECT LOOP_id,`mltest`.`dcc_residues`.*,loops_all.nt_ids,ml_loops.`motif_id` FROM `ml_loop_positions`
        // JOIN `mltest`.`dcc_residues`
        // JOIN loops_all
        // LEFT JOIN ml_loops
        // ON nt_id = `mltest`.`dcc_residues`.`dcc_residues_id` AND loop_info.loop_id=loop_id AND ml_loops.ml_loopsid=LOOP_id
        // WHERE `ml_loop_positions`.release_id = '0.5' AND ml_loops.ml_release_id='0.5'
        // ORDER BY loop_id ASC;

        $this->db->select()
                 ->from('ml_loop_positions')
                 ->join('__dcc_residues','nt_id = __dcc_residues.dcc_residues_id')
                 ->join('loop_info','loop_id=loop_info.loop_id')
                 ->join('ml_loops AS ML','loop_id=ML.loop_id','left')
                 ->where('ml_loop_positions.release_id','0.5')
                 ->where('ML.ml_release_id','0.5')
                 ->group_by('loop_id') // NB! comment out or leave in?
                 ->order_by('ML.motif_id','asc')
                 ->order_by('loop_id','asc');
//                  ->limit(10);
        $query = $this->db->get();

        return $query;
    }

    function get_heading()
    {
        $heading = array('#','loop id','nt id','motif');
        $i = 1;
        foreach ($this->avg as $key => $value) {
            if (preg_match('/mapman|sfcheck/',$key)) {
                $heading[] = "<a href='#' class='twipsy' title='{$key}. Avg {$value}'>$i</a>";
                $i++;
            }
        }
        return $heading;
    }


    function get_averages()
    {
        $cum = array();
        $i = 0;
        foreach ($this->q->result() as $row) {

            if ($i == 0) {
                $fields = get_object_vars($row);
                foreach ($fields as $key => $value) {
                    if (!array_key_exists($key,$cum)) {
                        $cum[$key] = 0;
                    }
                }
                $i = 1;
            }

            foreach ($fields as $key => $value) {
                $cum[$key] += $value;
            }
        }

        $total = $this->q->num_rows();
        foreach ($cum as $key => $value) {
            $avg[$key] = number_format($value / $total, 3);
        }
        return $avg;
    }

    // get a row from the query object, check if any of the fields are below
    // the average, if so, then return a formatted row for the table, otherwise
    // return an empty string.
    function analyze_nucleotide($row, $i)
    {
        $props = get_object_vars($row);

        $extreme_case = false;
        foreach ($props as $key => $value) {

            if (array_key_exists($key,$this->low_is_good)) {
                if ($value > $this->avg[$key]) {
                    $extreme_case = true;
                    break;
                }
            } else {
                if ($value < $this->avg[$key]) {
                    $extreme_case = true;
                    break;
                }

            }


            // high b values are bad, so we highlight them
//             $pos = strpos($key,'iso');
//             if ( $pos != false and $value > $this->avg[$key] ) {
//                 $extreme_case = true;
//                 break;
//             }
//             if ( array_key_exists($key,$this->avg) and $value < $this->avg[$key] ) {
//                 $extreme_case = true;
//                 break;
//             }
        }

        if ($extreme_case == true) {
            return array(
                $i,
                $this->make_checkbox($row->loop_id,$row->nt_ids),
                '<a class="pdb">' . substr($row->nt_id,0,4) . '</a>    ' . substr($row->nt_id,10),
                anchor_popup(site_url(array('motif/view/0.5',$row->motif_id)),$row->motif_id,array('width'=>'1000')),
                $this->make_label($row->sfcheck_correlation,'sfcheck_correlation'),
                $this->make_label($row->sfcheck_correlation_side_chain,'sfcheck_correlation_side_chain'),
                $this->make_label($row->sfcheck_real_space_R,'sfcheck_real_space_R'),
                $this->make_label($row->sfcheck_real_space_R_side_chain,'sfcheck_real_space_R_side_chain'),
                $this->make_label($row->sfcheck_connect,'sfcheck_connect'),
                $this->make_label($row->sfcheck_shift,'sfcheck_shift'),
                $this->make_label($row->sfcheck_shift_side_chain,'sfcheck_shift_side_chain'),
                $this->make_label($row->sfcheck_density_index_main_chain,'sfcheck_density_index_main_chain'),
                $this->make_label($row->sfcheck_density_index_side_chain,'sfcheck_density_index_side_chain'),
                $this->make_label($row->sfcheck_B_iso_main_chain,'sfcheck_B_iso_main_chain'),
                $this->make_label($row->sfcheck_B_iso_side_chain,'sfcheck_B_iso_side_chain'),
                $this->make_label($row->mapman_correlation,'mapman_correlation'),
                $this->make_label($row->mapman_real_space_R,'mapman_real_space_R'),
                $this->make_label($row->mapman_Biso_mean,'mapman_Biso_mean'),
                $this->make_label($row->mapman_occupancy_mean,'mapman_occupancy_mean')
            );
        } else {
            return array();
        }

    }

    function make_label($value, $key)
    {
        if (in_array($key,$this->low_is_good)) {
            if ($value > $this->avg[$key]) {
                return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            } else {
                return "<span class='label success twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            }
        } else {
            if ($value < $this->avg[$key]) {
                return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            } else {
                return "<span class='label success twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            }
        }
    }

    function make_checkbox($loop,$nts)
    {
        return "<label class='narrow'><input type='radio' name='loops' id='{$loop}' class='jmolInline' data-nt='$nts'><span>$loop</span></label>";
    }

    function get_sfdata_table()
    {
        $i = 1;
        foreach ($this->q->result() as $row) {
            $row_array = $this->analyze_nucleotide($row,$i);
            if (count($row_array) > 0) {
                $table[] = $row_array;
                $i++;
            }
        }
        return $table;
    }

    function get_fields_array()
    {
        return array('sfcheck_correlation','sfcheck_correlation_side_chain',
                     'sfcheck_real_space_R','sfcheck_real_space_R_side_chain',
                     'sfcheck_connect',
                     'sfcheck_shift','sfcheck_shift_side_chain',
                     'sfcheck_density_index_main_chain',
                     'sfcheck_density_index_side_chain',
                     'sfcheck_B_iso_main_chain','sfcheck_B_iso_side_chain',
                     'mapman_correlation','mapman_real_space_R',
                     'mapman_Biso_mean','mapman_occupancy_mean');
    }

    function get_min($pdb)
    {
        $this->db->select_min('sfcheck_correlation')
                 ->select_min('sfcheck_correlation_side_chain')
                 ->select_min('sfcheck_real_space_R')
                 ->select_min('sfcheck_real_space_R_side_chain')
                 ->select_min('sfcheck_connect')
                 ->select_min('sfcheck_shift')
                 ->select_min('sfcheck_shift_side_chain')
                 ->select_min('sfcheck_density_index_main_chain')
                 ->select_min('sfcheck_density_index_side_chain')
                 ->select_min('sfcheck_B_iso_main_chain')
                 ->select_min('sfcheck_B_iso_side_chain')
                 ->select_min('mapman_correlation')
                 ->select_min('mapman_real_space_R')
                 ->select_min('mapman_Biso_mean')
                 ->select_min('mapman_occupancy_mean')
                 ->from('__dcc_residues')
                 ->like('dcc_residues_id',strtoupper($pdb),'after');
        $result = $this->db->get()->result_array();
        return $result[0];
    }

    function get_max($pdb)
    {
        $this->db->select_max('sfcheck_correlation')
                 ->select_max('sfcheck_correlation_side_chain')
                 ->select_max('sfcheck_real_space_R')
                 ->select_max('sfcheck_real_space_R_side_chain')
                 ->select_max('sfcheck_connect')
                 ->select_max('sfcheck_shift')
                 ->select_max('sfcheck_shift_side_chain')
                 ->select_max('sfcheck_density_index_main_chain')
                 ->select_max('sfcheck_density_index_side_chain')
                 ->select_max('sfcheck_B_iso_main_chain')
                 ->select_max('sfcheck_B_iso_side_chain')
                 ->select_max('mapman_correlation')
                 ->select_max('mapman_real_space_R')
                 ->select_max('mapman_Biso_mean')
                 ->select_max('mapman_occupancy_mean')
                 ->from('__dcc_residues')
                 ->like('dcc_residues_id',strtoupper($pdb),'after');
        $result = $this->db->get()->result_array();
        return $result[0];
     }

    function get_dcc_pdbs()
    {
        $this->db->select('DISTINCT(substr(dcc_residues_id,1,4)) as pdb FROM __dcc_residues;',false);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $result[] = anchor(base_url(array('loops','sfjmol',$row->pdb)),$row->pdb);
        }
        return $result;
    }

}

/* End of file loops_model.php */
/* Location: ./application/model/loops_model.php */
