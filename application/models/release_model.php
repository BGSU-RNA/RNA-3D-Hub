<?php
class Release_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->helper('form');
        $this->db_get_all_releases(); // store an array with releases
        // Call the Model constructor
        parent::__construct();
    }

    function get_all_releases()
    {
        $result = $this->all_releases;
        for ($i = 0; $i < count($result); $i++) {
            if ($i == 0) {
                $result[$i]['id'] = anchor("release/view/".$result[$i]['id'], $result[$i]['id'].' (current)');
            } else {
                $result[$i]['id'] = anchor("release/view/".$result[$i]['id'], $result[$i]['id']);
            }
            // $result[$i]['date'] = human readable
        }
        return $result;
    }

    function get_release($id)
    {
        $this->db->select('motif_id,count(id) AS instances');
        $this->db->from('ml_loops');
        $this->db->where('release_id', $id);
        $this->db->group_by('motif_id');
        $this->db->order_by('instances','desc');
        $result = $this->db->get()->result_array();

        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['motif_id'] = anchor("motif/view/" . $id . '/' . $result[$i]['motif_id'], $result[$i]['motif_id']);
        }
        return $result;
    }

    function db_get_all_releases()
    {
        if ( !isset($this->all_releases) ) {
            $this->db->select('ml_releases.id,ml_releases.date,ml_releases.description,count(ml_loops.id) AS loops, count(DISTINCT(motif_id)) AS motifs');
            $this->db->from('ml_releases');
            $this->db->join('ml_loops','ml_releases.id=ml_loops.release_id');
            $this->db->group_by('ml_releases.id');
            $this->db->order_by('ml_releases.date','desc');
            $this->all_releases = $this->db->get()->result_array();
        }
    }

    function get_compare_radio_table()
    {
        $releases = $this->all_releases;
        $table = array();
        for ($i = 0; $i < count($releases); $i++) {
            $table[] = form_radio(array('name'=>'release1','value'=>$releases[$i]['id'])) . $releases[$i]['id'];
            $table[] = form_radio(array('name'=>'release2','value'=>$releases[$i]['id'])) . $releases[$i]['id'];
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