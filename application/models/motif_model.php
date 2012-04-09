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
                'common_name' => $result->common_name,
                'annotation' => $result->annotation
            );
        } else {
            return array(
                'common_name' => '',
                'annotation' => ''
            );
        }
    }

    function save_annotation($motif_id, $common_name, $annotation)
    {
        $this->db->select()
                  ->from('ml_motif_annotations')
                  ->where('motif_id', $motif_id)
                  ->limit(1);
        $query = $this->db->get();

        $this->db->set('common_name', $common_name)
                 ->set('annotation', $annotation);

        if ( $query->num_rows > 0 ) {
            return $this->db
                        ->where('motif_id', $motif_id)
                        ->update('ml_motif_annotations');
        } else {
            return $this->db->set('motif_id', $motif_id)
                        ->insert('ml_motif_annotations');
        }
    }

    // history widget
    function get_history($motif_id)
    {
        $this->db->select('id')
                 ->from('ml_releases')
                 ->order_by("date", "desc")
                 ->where('type', substr($motif_id, 0, 2))
                 ->limit(2);
        $result = $this->db->get()->result_array();
        $prev_release = $result[1]['id'];

        $this->db->select('*');
        $this->db->from('ml_set_diff');
        $this->db->where('release_id', $this->release_id);
        $this->db->where('motif_id1', $this->motif_id);
        $this->db->order_by("overlap", "desc");
        $result = $this->db->get()->result_array();

        $rows[0] = array('Parents','Intersection','Overlap','Only in the child','Only in the parent');
        for ($i = 0; $i < count($result); $i++) {

            $link = "http://rna.bgsu.edu/MotifAtlas/motif/view/{$prev_release}/{$result[$i]['motif_id2']}";
            $chbx = $this->get_checkboxes_with_color($result[$i]['two_minus_one'],
                                                     $result[$i]['intersection'],
                                                     $prev_release);
            $rows[] = array(
                                "<a href='{$link}'>" . $result[$i]['motif_id2'] . "</a>",
                                str_replace(',',', ',$result[$i]['intersection']),
                                number_format($result[$i]['overlap']*100,1) . '%',
                                str_replace(',',', ',$result[$i]['one_minus_two']),
//                                str_replace(',',', ',$result[$i]['two_minus_one'])
                                $chbx
                           );
        }
        return $rows;
    }

    function get_nucleotide_correspondence($parent, $prev_release)
    {
        $this->db->select('t1.position')->from('ml_loop_positions as t1');
        $this->db->join('ml_loop_positions as t2','t1.loop_id=t2.loop_id AND t1.nt_id=t2.nt_id');
        $this->db->where('t1.loop_id', $parent);
        $this->db->where('t1.release_id', $prev_release);
        $this->db->where('t2.release_id', $this->release_id);
        $this->db->order_by('t2.position');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $positions[] = $row->position;
        }
        return $positions;
    }

    function get_corresponding_nucleotides($loop_id, $release, $positions)
    {

// SELECT * FROM loop_positions
// WHERE loop_id = 'IL_1FJG_051'
// AND release_id='0.3'
// AND position IN (3,4,5,1,2)
// ORDER BY field (position,3,4,5,1,2);

        $this->db->select()->from('ml_loop_positions')
                           ->where('loop_id', $loop_id)
                           ->where('release_id', $release)
                           ->where_in('position', $positions);
//         $this->db->order_by('position');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $nts[$row->position] = $row->nt_id;
        }
//         ksort($nts);
        foreach ($positions as $p) {
            $nt_string[] = $nts[$p];
        }
        return implode(',', $nts);
    }

    function get_checkboxes_with_color($loops_string, $intersection, $prev_release)
    {
        if ($loops_string == '') {
            return '';
        }
        $intersection = explode(',', $intersection);
        $corr_positions = $this->get_nucleotide_correspondence($intersection[0], $prev_release);

        $checkboxes = '<ul class="inputs-list">';
        $loops = explode(',', $loops_string);
        foreach ($loops as $loop) {
            $this->db->select()->from('ml_mutual_discrepancy');
            $this->db->where('loop_id1',$loop);
            $this->db->where_in('loop_id2',$intersection);
            $this->db->order_by('discrepancy')->limit(1);
            $query = $this->db->get();
            if ( $query->num_rows() > 0 ) {
                $row = $query->first_row();
                $class = $this->get_css_class($row->discrepancy);
                $checkboxes .= "<li><label><input type='checkbox' id='{$loop}' class='jmolInline' ";
                $checkboxes .= 'data-nt="'. $this->get_corresponding_nucleotides($loop,$prev_release,$corr_positions) . '">';
//                 $checkboxes .= 'data-nt="'. implode(',',$corr_positions) . '">';
                $checkboxes .= "<span class='$class'>$loop</span></label></li>";
            } else {
                $checkboxes .= "<li><label><input type='checkbox' id='{$loop}' class='jmolInline' ";
                $checkboxes .= 'data-nt="">$loop</label></li>';
            }
        }
        $checkboxes .= '</ul>';
        return $checkboxes;
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
                              'class' => $this->get_css_class($disc[$loop_id1][$loop_id2]) );
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
            $checkbox_div .= "data-nt='" . implode(",", $this->full_nts[$loops[$i]]) . "'>";
            $checkbox_div .= "&nbsp;{$loops[$i]}";
            $checkbox_div .= '</label></li>';
            //<input type='checkbox' id='s1' class='jmolInline' data-nt='1S72_1_0_1095,1S72_1_0_1261'><label for='s1'>IL_1S72_038</label><br>
        }
        $checkbox_div .= '</ul>';
        return $checkbox_div;
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
        $header = array('#', 'Loop id', 'PDB', 'Discrepancy');
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
            if ( $key == '#' ) {
                $row[$i] = $id;
            } elseif ( $key == 'Loop id' ) {
                $row[$i] = $this->loops[$id];
            } elseif ( $key == 'PDB' ) {
                $parts = explode("_", $this->loops[$id]);
                $row[$i] = '<a class="pdb">' . $parts[1] . '</a>';
            } elseif ( is_int($key) ) {
                $row[$i] = $this->nts[$this->loops[$id]][$key];
            } elseif ( $key == 'Discrepancy' ) {
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
        $this->db->select('loop_id,original_order,similarity_order');
        $this->db->from('ml_loop_order');
        $this->db->where('release_id', $this->release_id);
        $this->db->where('motif_id', $this->motif_id);
        $this->db->order_by('original_order');
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
        $this->db->select('loop_id,nt_id,position');
        $this->db->from('ml_loop_positions');
        $this->db->where('release_id', $this->release_id);
        $this->db->where('motif_id', $this->motif_id);
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


}

/* End of file motif_model.php */
/* Location: ./application/model/motif_model.php */