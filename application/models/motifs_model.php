<?php

function add_url($n)
{
    return anchor(base_url(array('motif','view',$n)), $n);
}

class Motifs_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->helper('form');

        $this->types = array('IL','HL');
        // Call the Model constructor
        parent::__construct();
    }

    function get_current_release_info()
    {
        $ils = $this->get_latest_release('il', 1);

        $data['release_info']['il_release'] = $ils['id'];
        $data['release_info']['hl_release'] = $this->get_latest_release('hl');
        $data['release_info']['j3_release'] = $this->get_latest_release('j3');
        $data['release_info']['last_update'] = strtotime($ils['date']);
        $data['release_info']['next_update'] = strtotime("{$ils['date']} + 4 weeks");

        return $data;
    }

    function get_featured_motifs($motif_type)
    {
        $release_id = $this->get_latest_release($motif_type);

        if ( $motif_type == 'il' ) {
            $motifs = array('kink-turn', 'c-loop', 'sarcin', 'triple sheared', 'double sheared');
        } else {
            $motifs = array('T-loop', 'GNRA');
        }

        $data = array();

        foreach($motifs as $motif) {
            $this->db->select('MA.motif_id')
                     ->select('count(ML.loop_id) AS members')
                     ->from('ml_motif_annotations AS MA')
                     ->join('ml_loops AS ML', 'MA.motif_id = ML.motif_id')
                     ->like('MA.common_name', $motif)
                     ->where('ML.ml_release_id', $release_id)
                     ->group_by('ML.motif_id')
                     ->order_by('members', 'desc')
                     ->limit(1);
            $query = $this->db->get();

            if ( $query->num_rows() > 0 ) {
                $data[$motif] = $query->row()->motif_id;
            }
        }

        return $data;
    }

    function get_all_motifs($release_id, $motif_type)
    {
        if ( $release_id == 'current' ) {
            $release_id = $this->get_latest_release($motif_type);
        }

        $this->db->select('motif_id')
                 ->from('ml_motifs_info')
                 ->where('ml_release_id', $release_id)
                 ->where('type', $motif_type);
        $query = $this->db->get();

        $motif_ids = array();

        foreach($query->result() as $row) {
            $motif_ids[] = $row->motif_id;
        }

        return $motif_ids;
    }

    function db_get_all_releases($motif_type)
    {
        $this->db->select('STRAIGHT_JOIN MR.ml_release_id, MR.type, MR.date, MR.description, MR.annotation, MR.index, MR.nr_release_id, MR.parent_ml_release_id, count(ML.loop_id) AS loops, count(DISTINCT(motif_id)) AS motifs', FALSE)
                 ->from('ml_releases AS MR')
                 ->join('ml_loops AS ML','MR.ml_release_id = ML.ml_release_id')
                 ->where('MR.type',$motif_type)
                 ->like('ML.loop_id',$motif_type,'after')
                 ->group_by('MR.ml_release_id')
                 ->order_by('MR.index','desc');
        return $this->db->get();
    }

    // get motifs with same sequences
    function get_polymorphs($motif_type, $release_id)
    {
        $query_string = "
            seq, length, group_concat(motif_id) AS motifs, count(motif_id) AS motif_num
            FROM (
                SELECT DISTINCT(seq AND motif_id),seq, length, motif_id FROM ml_loops AS t1
                JOIN loop_info AS t2
                ON t1.loop_id = t2.loop_id
                WHERE t1.ml_release_id = '{$release_id}'
                AND t2.`type` = '{$motif_type}'
                ORDER BY length DESC
            ) AS t3
            GROUP BY seq
            HAVING count(motif_id) > 1
            ORDER BY length DESC;
        ";
        $query = $this->db->select($query_string, FALSE)->get();

        if ($query->num_rows() == 0) { return 'No polymorphs found in this release'; }

        $table = array();

        foreach ($query->result() as $row) {
            $table[] = array($row->seq,
                             $row->length,
                             $row->motif_num,
                             $this->format_polymorphic_motif_list($row->motifs) );
        }

        return $table;
    }

    function format_polymorphic_motif_list($motif_list)
    {
        $motifs = explode(',', $motif_list);
        $output = '<ul class="inputs-list">';

        foreach ($motifs as $motif) {
            $loop_link = anchor_popup("motif/view/$motif", '&#10140;');
            $shuffled = str_shuffle($motif); // to avoid id collision
            $output .=
           "<li class='loop'>
                <label>
                    <input type='radio' class='jmolInline' name='m' data-coord='{$motif}' id='{$shuffled}'>
                    <span>$motif</span>
                    <span class='loop_link'>{$loop_link}</span>
                </label>
            </li>";
        }

        $compare_link = anchor_popup(base_url(array('motif', 'compare', $motifs[0], $motifs[1])), 'Compare');
        return $output . "<li>$compare_link</li></ul>";
    }

    function get_change_counts_by_release($motif_type)
    {
        $this->db->select('release_id1')
                 ->select_sum('num_added_groups','nag')
                 ->select_sum('num_removed_groups','nrg')
                 ->select_sum('num_updated_groups','nug')
                 ->from('ml_release_diff')
                 ->where('type',$motif_type)
                 ->where('direct_parent',1)
                 ->group_by('release_id1');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $changes[$row->release_id1] = $row->nag + $row->nug + $row->nrg;
        }

        return $changes;
    }

    function get_release_precedence($motif_type)
    {
        $this->db->select('ml_release_id')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('index','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $ids[] = $row->ml_release_id;
        }

        for ($i=0; $i<count($ids)-1; $i++) {
            $releases[$ids[$i]] = $ids[$i+1];
        }
        
        return $releases;
    }

    function get_release_status($motif_type,$id)
    {
        $this->db->select('ml_release_id')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('index','desc')
                 ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $current_release = $row->ml_release_id;
        }

        if ($id == $current_release) {
            return 'Current';
        } else {
            return 'Obsolete';
        }
    }

    function get_label_type($changes)
    {
        if ($changes == 0) {
            $label = 'success';
        } elseif ($changes <= 20) {
            $label = 'notice';
        } elseif ($changes <= 100) {
            $label = 'warning';
        } else {
            $label = 'important';
        }

        return $label;
    }

    function make_release_label($num, $id1, $id2, $motif_type)
    {
        $text = anchor(base_url(array('motifs','compare',$motif_type,$id1,$id2)), $num);

        if ($num == 0) {
            $reltype = "default";
        } elseif ($num <= 10) {
            $reltype = "notice";
        } elseif ($num <= 100) {
            $reltype = "warning";
        } else {
            $reltype = "important";
        }

        return "<span class='label $reltype'>$text</span>";
    }

    function get_all_releases()
    {
        foreach ($this->types as $motif_type) {
            $changes = $this->get_change_counts_by_release($motif_type);
            $compare = $this->get_release_precedence($motif_type);
            $query   = $this->db_get_all_releases($motif_type);

            $i = 0;

            foreach ($query->result() as $row) {
                if ($i == 0) {
                    $id = anchor(base_url(array("motifs/release",$motif_type,$row->id)), $row->id . ' (current)');
                    $i++;
                } else {
                    $id = anchor(base_url(array("motifs/release",$motif_type,$row->id)), $row->id);
                }

                if (array_key_exists($row->id, $changes)) {
                    $label = $this->get_label_type($changes[$row->id]);
                    $compare_url = base_url(array('motifs','compare',$motif_type,$row->id,$compare[$row->id]));
                    $num_changes = "<a href='$compare_url' class='nodec'><span class='label {$label}'>{$changes[$row->id]} changes</span></a>";
                } else {
                    $num_changes = '';
                }

                $table[$motif_type][] = array($id,
                                        $num_changes,
                                        $row->description,
                                        $row->loops,
                                        $row->motifs);
            }
        }

        return $table;
    }

    function get_complete_release_history()
    {
        foreach ($this->types as $motif_type) {
            $query = $this->db_get_all_releases($motif_type);

            foreach($query->result() as $row){
                $data[$row->ml_release_id]['loops'] = $row->loops;
                $data[$row->ml_release_id]['motifs'] = $row->motifs;
                $data[$row->ml_release_id]['description'] = $row->description;
                $data[$row->ml_release_id]['annotation'] = $row->annotation;
                $data[$row->ml_release_id]['date'] = $row->date;
            }

            $releases = $this->get_release_precedence($motif_type);

            $this->db->select('MR.ml_release_id')
                     ->select('RD.release_id2')
                     ->select('RD.num_added_groups')
                     ->select('RD.num_removed_groups')
                     ->select('RD.num_updated_groups')
                     ->select('RD.num_added_loops')
                     ->select('RD.num_removed_loops')
                     ->from('ml_releases AS MR')
                     ->join('ml_release_diff AS RD','MR.ml_release_id = RD.release_id1')
                     ->where('MR.type',$motif_type)
                     ->where('RD.type',$motif_type)
                     ->where('direct_parent',1)
                     ->order_by('MR.index','desc');
            $query = $this->db->get();

            foreach ($releases as $release) {

                // echo "debug info check the order of releases$release\n"; correct order 

                $found = FALSE;

                foreach ($query->result() as $row) {
                    if ($row->ml_release_id == $release) {
                        // echo "debuging info building table now in if, diff found and release id is (string)$row->ml_release_id , (string)$release\n";
                        $found = true;
                        $table[$motif_type][] = array(
                            anchor(base_url(array('motifs','release',$motif_type,$release)), $release),
                            $this->make_release_label($row->num_added_groups, $release, $row->release_id2, $motif_type),
                            $this->make_release_label($row->num_removed_groups, $release, $row->release_id2, $motif_type),
                            $this->make_release_label($row->num_updated_groups, $release, $row->release_id2, $motif_type),
                            $this->make_release_label($row->num_added_loops, $release, $row->release_id2, $motif_type),
                            $this->make_release_label($row->num_removed_loops, $release, $row->release_id2, $motif_type),
                            $data[$release]['loops'],
                            $data[$release]['motifs'],
                            date('m-d-Y', strtotime($data[$release]['date'])),
                            $data[$release]['annotation']
                        );
                        break;
                    } 
                }

                if (!$found and $release != "3.0" and $release != "3.1"){
                    // echo "debuging info building table now in no, diff found and release id is (string)$row->ml_release_id , (string)$release\n";
                    $table[$motif_type][] = array(
                        anchor(base_url(array('motifs','release',$motif_type,$release)), $release),
                        "<span>pending</span>",
                        "<span>pending</span>",
                        "<span>pending</span>",
                        "<span>pending</span>",
                        "<span>pending</span>",
                        $data[$release]['loops'],
                        $data[$release]['motifs'],
                        date('m-d-Y', strtotime($data[$release]['date'])),
                        $data[$release]['annotation']
                    );
                }
            }

            // // show the first release that has nothing to compare it with
            // $table[$motif_type][] = array(
            //     anchor(base_url(array('motifs','release',$motif_type,'0.1')), '0.1'),
            //     0,
            //     0,
            //     0,
            //     0,
            //     0,
            //     $data['0.1']['loops'],
            //     $data['0.1']['motifs'],
            //     date('m-d-Y', strtotime($data['0.1']['date'])),
            //     $data['0.1']['annotation']
            // );
        }

        return $table;
    }

    function get_annotation_label_type($comment)
    {
        if ($comment == 'Exact match') {
            return 'success';
        } elseif ($comment == 'New id, no parents') {
            return 'notice';
        } elseif ($comment == '> 2 parents') {
            return 'important';
        } else {
            return 'warning';
        }
    }

    function add_annotation_label($class_id,$reason)
    {
        if (array_key_exists($class_id,$reason)) {
            $label = $this->get_annotation_label_type($reason[$class_id]);
            return " <span class='label $label'>{$reason[$class_id]}</span>";
        } else {
            return '';
        }
    }

    function get_graphml($motif_type, $id)
    {
        $this->db->select('graphml')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->where('ml_release_id',$id);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            return $graphml = $row->graphml;
        }
    }

    function make_fancybox_link($id, $motif_type, $release_id)
    {

         $image =$this->config->item('home_url') . '/rna3dhub/img/MotifAtlas/' . strtoupper($motif_type) . $release_id . '/' . $id . '.png';
        //  return "<ul class='media-grid'><li><a href='#$id'><img class='thumbnail varna' src='$image' alt='$id' /></a></li></ul>";
         return "<ul class='media-grid'><li><a href='#$id'><img class='varna' src='$image' alt='$id' /></a></li></ul>";

    }

    function get_loop_annotations($motif_id, $id)
    {

        $loop_annotation = array();

        $this->db->select('la.loop_id, la.annotation_1, la.annotation_2')
        ->from('loop_annotations as la')
        ->join('ml_loops as ml', 'ml.loop_id = la.loop_id')
        ->where('ml.ml_release_id', $id)
        ->where('ml.motif_id', $motif_id);

        $query = $this->db->get();

        foreach($query->result() as $row){
            $loop_annotation[$row->loop_id] = $row->annotation_1;
        }
        return $loop_annotation;
    }

    function get_loop_annotations_count($loop_annotations)
    {
        # Check whether the loop_annotations array has values
        if ($loop_annotations) {

            # get the annotation from the loop_annotation1 array
            $annotation = array_values($loop_annotations);
            # count the number of unique annotation label. Key would be the annotation label and the value would be the count
            $annotation_count = array_count_values($annotation);
            # sort the array by desc value
            arsort($annotation_count);

            return $annotation_count;
        }

    }

    // This code creates the table that constitutes an IL or HL release
    function get_release($motif_type,$id)
    {
        // get annotations: updated/>2 parents etc
        $this->db->select('motif_id, comment')
                 ->from('ml_motifs_info')
                 ->where('type',$motif_type)
                 ->where('ml_release_id',$id);
        $query = $this->db->get();

        $counts_text = '';
        $table = array();

        if ( $query->num_rows() > 0 ) {
            foreach ($query->result() as $row) {
                $reason[$row->motif_id]  = $row->comment;
                $reason_flat[]           = $row->comment;
            }

        // count all loop annotation types for this motif
        $counts = array_count_values($reason_flat);

        foreach ($counts as $comment => $count) {
            $label = $this->get_annotation_label_type($comment);
            $counts_text .= "<span class='label $label'>$comment</span> <strong>$count</strong>;    ";
        }

        $counts_text .= '<br><br>';

        // get common names and basepair signatures
        // however, we are no longer using common_name annotations
        $this->db->select('motif_id')
                 ->select('bp_signature')
                 ->select('common_name')
                 ->from('ml_motif_annotations');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $annotations[$row->motif_id]['bp_signature'] = $row->bp_signature;
            $annotations[$row->motif_id]['common_name'] = $row->common_name;
        }

        // get the motif ids and counts
        $this->db->select('motif_id, count(loop_id) AS instances')
                 ->from('ml_loops')
                 ->like('motif_id',strtoupper($motif_type),'after')
                 ->where('ml_release_id', $id)
                 ->group_by('motif_id')
                 ->order_by('instances','desc');
        $query = $this->db->get();

        $i = 1;

        foreach ($query->result() as $row) {
            if ( array_key_exists($row->motif_id, $annotations) &&
                 strlen($annotations[$row->motif_id]['common_name']) > 1 ) {
                $annotation = '<li>Name: ' . $annotations[$row->motif_id]['common_name'] . '</li>';
            } else {
                $annotation = '';
            }

            if ( array_key_exists($row->motif_id, $annotations) and array_key_exists('bp_signature', $annotations[$row->motif_id])) {
                $signature = $annotations[$row->motif_id]['bp_signature'];
            } else {
                $signature = '';
            }

            /*
            We might need to refactor the code in the next two lines since equivalent methods
            exist in motif_model.php
            */
            $loop_annotations = $this->get_loop_annotations($row->motif_id, $id);
            $annotations_count = $this->get_loop_annotations_count($loop_annotations);

            # Create an empty string for storing motif annotation
            $annotation_new = '';

            if ($annotations_count) {
                foreach($annotations_count as $key => $value) {
                    $annotation_new .= "<li>" . $key . " " . "(" . $value . ")". "</li>";
                }
            }

            $length_distribution = $this->_get_motif_length_distribution($row->motif_id, $id);

            $table[] = array($i,
                             $this->make_fancybox_link($row->motif_id, $motif_type, $id),
                             anchor_popup(base_url(array('motif','view',$row->motif_id)), $row->motif_id)
                                . "<ul class='unstyled inputs-list'>"
                                . "<li><label><input type='radio' class='jmolInline' id='"
                                . str_replace('.','_',$row->motif_id)
                                . "' data-coord='{$row->motif_id}' data-quality='{$row->motif_id}' data-type='motif_id' name='ex'>"
                                . "<span>View exemplar</span></label></li>"
                                . "<li>Basepair signature: $signature</li>"
                                . '<li>History status: ' . $this->add_annotation_label($row->motif_id, $reason) . '</li>'
                                #. "$annotation"
                                #. "<li>Annotations</li>"
                                . '</ul>'
                                ."$annotation_new",
                             $length_distribution['unit_count']/$row->instances,
                             $row->instances);
                $i++;
            }
        }

        return array( 'table' => $table, 'counts' => $counts_text );
    }

    function get_release_advanced($motif_type, $release_id)
    {
        $result = $this->get_release($motif_type, $release_id);

        $table = array();

        foreach ($result['table'] as $row) {
            preg_match('/([IH]L_\d{5}\.\d+)/', $row[2], $matches);

            $motif_id = $matches[0];

            $distribution = $this->_get_motif_length_distribution($motif_id, $release_id);

            $row[] = $distribution['min'];
            $row[] = $distribution['max'];
            $row[] = $distribution['diff'];

            $table[] = $row;
        }

        $result['table'] = $table;
        return $result;
    }

    function _get_motif_length_distribution($motif_id, $release_id)
    {
        $this->db->select('LI.length')
                 ->from('ml_loops AS ML')
                 ->join('loop_info AS LI', 'ML.loop_id = LI.loop_id')
                 ->where('ML.ml_release_id', $release_id)
                 ->where('ML.motif_id', $motif_id);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $length[] = $row->length;
        }

        $distribution['max'] = max($length);
        $distribution['min'] = min($length);
        $distribution['diff'] = $distribution['max'] - $distribution['min'];

        $this->db->select('COUNT(MLP.unit_id) AS unit_count')
                 ->from('ml_loop_positions AS MLP')
                 ->where('MLP.ml_release_id', $release_id)
                 ->where('MLP.motif_id', $motif_id);
        $query = $this->db->get();
        $row = $query->row();
        $distribution['unit_count'] = $row->unit_count;

        return $distribution;
    }

    function get_compare_radio_table()
    {
        foreach ($this->types as $motif_type) {
            $changes = $this->get_change_counts_by_release($motif_type);
            $query   = $this->db_get_all_releases($motif_type);

            foreach ($query->result() as $row) {
                if (array_key_exists($row->ml_release_id, $changes)) {
                    $label = $this->get_label_type($changes[$row->ml_release_id]);
                    $num_changes = "<span class='label {$label}'>{$changes[$row->ml_release_id]} changes</span>";
                } else {
                    $num_changes = '';
                }

                $table[$motif_type][] = form_radio(array('name'=>'release1','value'=>$row->ml_release_id)) . $row->ml_release_id . $num_changes;
                $table[$motif_type][] = form_radio(array('name'=>'release2','value'=>$row->ml_release_id)) . $row->ml_release_id;
            }
        }

        return $table;
    }

    function get_latest_release($motif_type, $date=NULL)
    {
        $this->db->select('ml_release_id, date')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('index','desc')
                 ->limit(1);
        $result = $this->db->get()->row();

        if ( $date ) {
            return array('id' => $result->ml_release_id, 'date' => $result->date);
        } else {
            return $result->ml_release_id;
        }
    }

    function get_pdb_files_from_motif_release($motif_type, $release_id)
    {
        // get all loops in the release
        $this->db->select('loop_id')
                 ->from('ml_loops')
                 ->where('ml_release_id', $release_id)
                 ->like('loop_id', $motif_type, 'after');
        $query = $this->db->get();

        // extract pdb substring
        $pdbs = array();

        foreach($query->result() as $row) {
            $pdbs[] = substr($row->loop_id, 3, 4);
        }

        return array_unique($pdbs);
    }

    function get_identical_pdbs($pdbs1, $pdbs2)
    {
        return array_intersect($pdbs1, $pdbs2);
    }

    function get_new_and_replaced_pdbs($pdbs1, $pdbs2)
    {
        $only_old = array_diff($pdbs1, $pdbs2);
        $only_new = array_diff($pdbs2, $pdbs1);

        $replaced = array();
        $added    = array();

        foreach($only_new as $new_id) {
            foreach($only_old as $old_id) {
                // find a class where the new id is a rep, and the old one is not
                $this->db->select('P1.pdb_id')
                         ->from('nr_pdbs AS P1')
                         ->join('nr_pdbs AS P2', 'P1.nr_class_id = P2.nr_class_id AND ' .
                                                 'P1.nr_release_id = P2.nr_release_id')
                         ->where('P1.pdb_id', $new_id)
                         ->where('P2.pdb_id', $old_id)
                         ->where('P1.rep', 1)
                         ->where('P2.rep', 0)
                         ->like('P1.nr_class_name', 'NR_4.0_', 'after')
                         ->limit(1);
                $query = $this->db->get();

                if ( $query->num_rows() > 0 ) {
                    $replaced[$old_id] = $new_id;
                    break;
                }
            }

            // if the new id didn't replace any old rep, then it's brand new
            if ( !in_array($new_id, $replaced) ) {
                $added[] = $new_id;
            }
        }

        return array('pdbs_replaced' => $replaced, 'pdbs_added' => $added);
    }

    function get_removed_pdbs($pdbs1, $pdbs2)
    {
        // check if any of the old pdbs became obsolete

        $only_old = array_diff($pdbs1, $pdbs2);
        $removed = array();

        if ( count($only_old) == 0 ) {
            return $removed;
        }

        $this->db->select('pdb_obsolete_id')
                 ->select('replaced_by')
                 #->select('COALESCE(NULLIF(replaced_by,""), "None") AS replaced_by', FALSE)
                 ->from('pdb_obsolete')
                 ->where_in('pdb_obsolete_id', $only_old);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            if ( $row->replaced_by == '' ) {
                $row->replaced_by = 'None';
            }

            $removed[$row->pdb_obsolete_id] = $row->replaced_by;
        }

        return $removed;
    }

    function order_releases($rel1, $rel2, $motif_type)
    {
        $this->db->select('ml_release_id')
                 ->from('ml_releases')
                 ->where("type = '$motif_type' AND (ml_release_id = '$rel1' OR ml_release_id = '$rel2')")
                 ->order_by('index', 'asc');
        $query = $this->db->get();

        $releases = array();

        foreach($query->result() as $row) {
            $releases[] = $row->ml_release_id;
        }

        return $releases;
    }

    function _get_motif_instances($motif_id)
    {
        $loops = array();

        $this->db->select('loop_id')
                 ->from('ml_loops')
                 ->where('motif_id', $motif_id)
                 ->group_by('loop_id');
        $query = $this->db->get();

        foreach($query->result() as $row){
            $loops[] = $row->loop_id;
        }

        return $loops;
    }

    function _verify_updated_motifs($updated, $rel)
    {
        // check that the correct version is used
        $handles = array();

        foreach($updated as $motif){
            $handles[] = substr($motif, 3, 5); // XL_@@@@@
        }

        $this->db->select('motif_id')
                 ->from('ml_motifs_info')
                 ->where_in('handle', $handles)
                 ->where('ml_release_id', $rel);
        $query = $this->db->get();

        $updated_new = array();
        foreach($query->result() as $row){
            $updated_new[] = $row->motif_id;
        }

        return $updated_new;
    }

    function getSankeyDataJSON($rel1, $rel2, $motif_type)
    {
        // get motif ids from the two releases
        $this->db->select()
                 ->from('ml_release_diff')
                 ->where('release_id1', $rel2)
                 ->where('release_id2', $rel1)
                 ->where('type', $motif_type);
        $query = $this->db->get()->result();
        $row = $query[0];

        $removed_groups = explode(', ', $row->removed_groups);
        $removed_loops  = explode(', ', $row->removed_loops);
        $added_groups   = explode(', ', $row->added_groups);
        $added_loops    = explode(', ', $row->added_loops);
        $updated_groups = explode(', ', $row->updated_groups);

        // get motif instances
        $node_type = array(); // used for coloring nodes

        $nodes1 = array();
        $nodes1['New loops'] = $added_loops;
        $node_type['New loops'] = 'new';
        foreach($removed_groups as $motif){
            $nodes1[$motif] = $this->_get_motif_instances($motif);
            $node_type[$motif] = 'removed';
        }

        $nodes2 = array();
        $nodes2['Removed loops'] = $removed_loops;
        $node_type['Removed loops'] = 'old';
        foreach($added_groups as $motif){
            $nodes2[$motif] = $this->_get_motif_instances($motif);
            $node_type[$motif] = 'added';
        }

        // make sure that $updated corresponds to $rel1
        $updated_groups = $this->_verify_updated_motifs($updated_groups, $rel1);

        // separately process updated groups
        foreach($updated_groups as $motif){
            $version = substr($motif, 9); // everything after "XL_XXXXX."
            $next_motif = substr($motif, 0, 9) . ($version + 1);

            $node_type[$motif] = 'updated';
            $node_type[$next_motif] = 'updated';

            $nodes1[$motif] = $this->_get_motif_instances($motif);
            $nodes2[$next_motif] = $this->_get_motif_instances($next_motif);
        }

        // assign node ids
        $nodes = array();
        $i = 0;
        foreach($nodes1 as $motif => $loops){
            $ids[$motif] = $i;
            $nodes[] = array('name' => $motif, 'type' => $node_type[$motif]);
            $i += 1;
        }
        foreach($nodes2 as $motif => $loops){
            $ids[$motif] = $i;
            $nodes[] = array('name' => $motif, 'type' => $node_type[$motif]);
            $i += 1;
        }

        // compare all nodes
        foreach($nodes1 as $motif1 => $loops1){
            foreach($nodes2 as $motif2 => $loops2){
                $common = array_intersect($loops1, $loops2);
                if ( $common ) {
                    $links[] = array('source' => $ids[$motif1],
                                     'target' => $ids[$motif2],
                                     'value'  => count($common) / count($loops1),
                                     'loops'  => implode(', ', $common));
                }
            }
        }

        return json_encode(array('nodes' => $nodes, 'links' => $links));
    }

    function _get_instance_counts($motifs, $release)
    {
        $this->db->select('motif_id, count(loop_id) as instances')
                 ->from('ml_loops')
                 ->where_in('motif_id', $motifs)
                 ->where('ml_release_id', $release)
                 ->order_by('count(loop_id)', 'desc')
                 ->group_by('motif_id');
        $query = $this->db->get();

        $counts = array();
        foreach($query->result() as $row){
            $counts[] = array('motif_id' => $row->motif_id, 'instances' => $row->instances);
        }

        return $counts;
    }

    function _get_common_names($motifs)
    {
        $this->db->select('motif_id, common_name')
                 ->from('ml_motif_annotations')
                 ->where_in('motif_id', $motifs);
        $query = $this->db->get();

        $common_names = array();
        foreach($query->result() as $row){
            $common_names[$row->motif_id] = $row->common_name;
        }

        return $common_names;
    }

    function get_motif_counts($release, $motif_type)
    {
        $this->db->select()
                 ->from('ml_motifs_info')
                 ->where('ml_release_id', $release)
                 ->where('type', $motif_type);
        $query = $this->db->get();

        return $query->num_rows();
    }

    function _get_release_difference_data($rel1, $rel2, $motif_type)
    {
        $this->db->select()
                 ->from('ml_release_diff')
                 ->where("(release_id1 = '$rel1' AND release_id2 = '$rel2') OR " .
                         "(release_id2 = '$rel1' AND release_id1 = '$rel2')")
                 ->where('type', $motif_type);
        $query = $this->db->get()->result();
        return $query[0];
    }

    function get_release_difference_summary($rel1, $rel2, $motif_type)
    {
        $diff = $this->_get_release_difference_data($rel1, $rel2, $motif_type);

        return array('num_added_groups'   => $diff->num_added_groups,
                     'num_removed_groups' => $diff->num_removed_groups,
                     'num_updated_groups' => $diff->num_updated_groups,
                     'num_same_groups'    => $diff->num_same_groups);
    }

    function get_motif_summary_table($rel1, $rel2, $motif_type, $target)
    {
        $diff = $this->_get_release_difference_data($rel1, $rel2, $motif_type);

        // $target is the column: added_groups, removed_groups, updated_groups, same_groups
        $motifs = explode(', ', $diff->$target);

        // get counts
        $counts = $this->_get_instance_counts($motifs, $rel1);
        if ( count($counts) == 0 ) {
            $counts = $this->_get_instance_counts($motifs, $rel2);
        }

        // get common_names
        $common_names = $this->_get_common_names($motifs);

        $table = array();
        $i = 1;
        foreach($counts as $count){
            $table[] = array($i,
                             anchor_popup("motif/view/{$count['motif_id']}", $count['motif_id']),
                             $count['instances'],
                             $common_names[$count['motif_id']]);
            $i += 1;
        }

        return $table;
    }

}

/* End of file motifs_model.php */
/* Location: ./application/model/motifs_model.php */
