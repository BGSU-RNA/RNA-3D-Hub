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

    function db_get_all_releases($motif_type)
    {
        $this->db->select('ml_releases.*,count(ml_loops.id) AS loops, count(DISTINCT(motif_id)) AS motifs')
                 ->from('ml_releases')
                 ->join('ml_loops','ml_releases.id=ml_loops.release_id')
                 ->where('ml_releases.type',$motif_type)
                 ->like('ml_loops.id',$motif_type,'after')
                 ->group_by('ml_releases.id')
                 ->order_by('ml_releases.date','desc');
        return $this->db->get();
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
        $this->db->select('id')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('date','desc');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $ids[] = $row->id;
        }
        for ($i=0; $i<count($ids)-1; $i++) {
            $releases[$ids[$i]] = $ids[$i+1];
        }
        return $releases;
    }

    function get_release_status($motif_type,$id)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('date','desc')
                 ->limit(1);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $current_release = $row->id;
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

    function make_release_label($num)
    {
        if ($num == 0) {
            return "<span class='label default'>$num</span>";
        } elseif ($num <= 10) {
            return "<span class='label notice'>$num</span>";
        } elseif ($num <= 100) {
            return "<span class='label warning'>$num</span>";
        } else {
            return "<span class='label important'>$num</span>";
        }
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

            $releases = $this->get_release_precedence($motif_type);

            $this->db->select()
                     ->from('ml_releases')
                     ->join('ml_release_diff','ml_releases.id=ml_release_diff.release_id1')
                     ->where('ml_releases.type',$motif_type)
                     ->where('ml_release_diff.type',$motif_type)
                     ->where('direct_parent',1)
                     ->order_by('date','desc');
            $query = $this->db->get();

            foreach ($query->result() as $row) {
                if ($row->release_id2 == $releases[$row->id]) {
                    $table[$motif_type][] = array(
                        anchor(base_url(array('motifs','compare',$motif_type,$row->id,$releases[$row->id])), $row->id),
                        $this->make_release_label($row->num_added_groups),
                        $this->make_release_label($row->num_removed_groups),
                        $this->make_release_label($row->num_updated_groups),
                        $this->make_release_label($row->num_added_loops),
                        $this->make_release_label($row->num_removed_loops)
                    );
                }
            }

        }
        return $table;
    }

    function get_annotation_label_type($comment)
    {
        if ($comment == 'Exact match') {
            return 'success';
        } elseif ($comment == 'New id, no parents') {
            return 'notice';
        } else {
            return 'important';
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

    function get_release($motif_type,$id)
    {
        // get annotations: updated/>2 parents etc
        $this->db->select()
                 ->from('ml_motifs')
                 ->where('type',$motif_type)
                 ->where('release_id',$id);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $reason[$row->id]  = $row->comment;
            $reason_flat[]     = $row->comment;
        }
        // count all annotation types
        $counts = array_count_values($reason_flat);
        $counts_text = '';
        foreach ($counts as $comment => $count) {
            $label = $this->get_annotation_label_type($comment);
            $counts_text .= "<span class='label $label'>$comment</span> <strong>$count</strong>;    ";
        }
        $counts_text .= '<br><br>';

        // get the motif ids and counts
        $this->db->select('motif_id,count(id) AS instances')
                 ->from('ml_loops')
                 ->like('motif_id',strtoupper($motif_type),'after')
                 ->where('release_id', $id)
                 ->group_by('motif_id')
                 ->order_by('instances','desc');
        $query = $this->db->get();

        $table = array();
        $i = 1;
        foreach ($query->result() as $row) {
            $table[] = array($i,
                             anchor(base_url(array("motif/view",$row->motif_id)), $row->motif_id),
                             $this->add_annotation_label($row->motif_id, $reason),
                             $row->instances);
            $i++;
        }
        return array( 'table' => $table, 'counts' => $counts_text );
    }

    function get_compare_radio_table()
    {
        foreach ($this->types as $motif_type) {
            $changes = $this->get_change_counts_by_release($motif_type);
            $query   = $this->db_get_all_releases($motif_type);
            foreach ($query->result() as $row) {
                if (array_key_exists($row->id, $changes)) {
                    $label = $this->get_label_type($changes[$row->id]);
                    $num_changes = "<span class='label {$label}'>{$changes[$row->id]} changes</span>";
                } else {
                    $num_changes = '';
                }

                $table[$motif_type][] = form_radio(array('name'=>'release1','value'=>$row->id)) . $row->id . $num_changes;
                $table[$motif_type][] = form_radio(array('name'=>'release2','value'=>$row->id)) . $row->id;
            }
        }
        return $table;
    }

    function get_latest_release($motif_type)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['id'];
    }

    function count_motifs($motif_type,$rel)
    {
        $this->db->select('count(id) as ids')
                 ->from('ml_motifs')
                 ->where('release_id', $rel)
                 ->where('type',$motif_type);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $counts = $row->ids;
        }
        return $counts;
    }

    function get_release_diff($motif_type,$rel1, $rel2)
    {
        $attributes = array('class' => 'unstyled');

        $this->db->select()
                 ->from('ml_release_diff')
                 ->where('type',$motif_type)
                 ->where('release_id1',$rel1)
                 ->where('release_id2',$rel2);
        $query = $this->db->get();
        if ($query->num_rows == 0) {
            $this->db->select()
                     ->from('ml_release_diff')
                     ->where('type',$motif_type)
                     ->where('release_id1',$rel2)
                     ->where('release_id2',$rel1);
            $query = $this->db->get();
            $data['rel1'] = $rel2;
            $data['rel2'] = $rel1;
            $rel1 = $data['rel1'];
            $rel2 = $data['rel2'];
        } else {
            $data['rel1'] = $rel1;
            $data['rel2'] = $rel2;
        }

        $counts1 = $this->count_motifs($motif_type,$rel1);
        $counts2 = $this->count_motifs($motif_type,$rel2);

        foreach ($query->result() as $row) {

            $data['uls']['num_motifs1'] = $counts1;
            $data['uls']['num_motifs2'] = $counts2;

            if ($row->num_same_groups > 0) {
                $data['uls']['ul_intersection'] = ul(array_map("add_url", split(', ',$row->same_groups)),$attributes);
            } else {
                $data['uls']['ul_intersection'] = '';
            }
            if ($row->num_updated_groups > 0) {
                $data['uls']['ul_updated'] = ul(array_map("add_url", split(', ',$row->updated_groups)),$attributes);
            } else {
                $data['uls']['ul_updated'] = '';
            }
            if ($row->num_added_groups > 0) {
                $data['uls']['ul_only_in_1'] = ul(array_map("add_url", split(', ',$row->added_groups)),$attributes);
            } else {
                $data['uls']['ul_only_in_1'] = '';
            }
            if ($row->num_removed_groups > 0) {
                $data['uls']['ul_only_in_2'] = ul(array_map("add_url", split(', ',$row->removed_groups)),$attributes);
            } else {
                $data['uls']['ul_only_in_2'] = '';
            }
            $data['uls']['num_intersection'] = $row->num_same_groups;
            $data['uls']['num_updated']      = $row->num_updated_groups;
            $data['uls']['num_only_in_1']    = $row->num_added_groups;
            $data['uls']['num_only_in_2']    = $row->num_removed_groups;
        }
        return $data;
    }


}

/* End of file motifs_model.php */
/* Location: ./application/model/motifs_model.php */