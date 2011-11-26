<?php
class Release_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->helper('form');
        // Call the Model constructor
        parent::__construct();
    }

    function db_get_all_releases()
    {
        $this->db->select('ml_releases.id,ml_releases.date,ml_releases.description,count(ml_loops.id) AS loops, count(DISTINCT(motif_id)) AS motifs')
                 ->from('ml_releases')
                 ->join('ml_loops','ml_releases.id=ml_loops.release_id')
                 ->group_by('ml_releases.id')
                 ->order_by('ml_releases.date','desc');
        return $this->db->get();
    }

    function get_change_counts_by_release()
    {
        $this->db->select('release_id1')
                 ->select_sum('num_added_groups','nag')
                 ->select_sum('num_removed_groups','nrg')
                 ->select_sum('num_updated_groups','nug')
                 ->from('ml_release_diff')
                 ->group_by('release_id1')
                 ->where('direct_parent',1);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $changes[$row->release_id1] = $row->nag + $row->nug + $row->nrg;
        }
        return $changes;
    }

    function get_release_precedence()
    {
        $this->db->select('id')->from('ml_releases')->order_by('date','desc');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $ids[] = $row->id;
        }
        for ($i=0; $i<count($ids)-1; $i++) {
            $releases[$ids[$i]] = $ids[$i+1];
        }
        return $releases;
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

    function get_all_releases()
    {
        $changes = $this->get_change_counts_by_release();
        $compare = $this->get_release_precedence();
        $query   = $this->db_get_all_releases();

        $i = 0;
        foreach ($query->result() as $row) {
            if ($i == 0) {
                $id = anchor(base_url(array("release/view",$row->id)), $row->id . ' (current)');
                $i++;
            } else {
                $id = anchor(base_url(array("release/view",$row->id)), $row->id);
            }
            if (array_key_exists($row->id, $changes)) {
                $label = $this->get_label_type($changes[$row->id]);
                $compare_url = base_url(array('release','compare',$row->id,$compare[$row->id]));
                $num_changes = "<a href='$compare_url' class='nodec'><span class='label {$label}'>{$changes[$row->id]} changes</span></a>";
            } else {
                $num_changes = '';
            }

            $table[] = array($id,
                             $num_changes,
                             $row->description,
                             $row->loops,
                             $row->motifs);
        }
        return $table;
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

    function get_complete_release_history()
    {
        $releases = $this->get_release_precedence();

        $this->db->select()
                 ->from('ml_releases')
                 ->join('ml_release_diff','ml_releases.id=ml_release_diff.release_id1')
                 ->where('direct_parent',1)
                 ->order_by('date','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->release_id2 == $releases[$row->id]) {
                $table[] = array(
                    anchor(base_url(array('release','compare',$row->id,$releases[$row->id])), $row->id),
                    $this->make_release_label($row->num_added_groups),
                    $this->make_release_label($row->num_removed_groups),
                    $this->make_release_label($row->num_updated_groups),
                    $this->make_release_label($row->num_added_loops),
                    $this->make_release_label($row->num_removed_loops)
                );
            }
        }
        return $table;
    }

    function get_release($id)
    {
        $this->db->select('motif_id,count(id) AS instances')
                 ->from('ml_loops')
                 ->where('release_id', $id)
                 ->group_by('motif_id')
                 ->order_by('instances','desc');
        $result = $this->db->get()->result_array();

        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['motif_id'] = anchor(base_url(array("motif/view",$id,$result[$i]['motif_id'])), $result[$i]['motif_id']);
        }
        return $result;
    }

    function get_compare_radio_table()
    {
        $changes = $this->get_change_counts_by_release();
        $query   = $this->db_get_all_releases();
        $table = array();
        foreach ($query->result() as $row) {
            if (array_key_exists($row->id, $changes)) {
                $label = $this->get_label_type($changes[$row->id]);
                $num_changes = "<span class='label {$label}'>{$changes[$row->id]} changes</span>";
            } else {
                $num_changes = '';
            }

            $table[] = form_radio(array('name'=>'release1','value'=>$row->id)) . $row->id . $num_changes;
            $table[] = form_radio(array('name'=>'release2','value'=>$row->id)) . $row->id;
        }
        return $table;
    }

    function get_latest_release()
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['id'];
    }

}

/* End of file release_model.php */
/* Location: ./application/model/release_model.php */