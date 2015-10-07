<?php
class Release_history_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        // Call the Model constructor
        parent::__construct();
    }

    function set_releases($release1, $release2)
    {
        $this->rel1 = $release1;
        $this->rel2 = $release2;
    }

    function get_summary()
    {
        $data['intersection'] = $this->get_intersection();
        $data['updated']      = $this->get_updated();
        $data['diff']         = $this->get_diff();
        $data['num_motifs1']  = $this->count_motifs($this->rel1);
        $data['num_motifs2']  = $this->count_motifs($this->rel2);
        $data['loops']        = $this->get_loop_stats();
        return $data;
    }

    function get_loop_stats()
    {
        $l1 = $this->get_loops($this->rel1);
        $l2 = $this->get_loops($this->rel2);
        $d['intersection'] = array_merge(array_intersect($l1, $l2));
        $d['only_in_1']    = array_merge(array_diff($l1, $l2));
        $d['only_in_2']    = array_merge(array_diff($l2, $l1));
        return $d;
    }

    function get_loops($rel)
    {
        $this->db->select('ml_loops_id')
                 ->from('ml_loops')
                 ->where('release_id', $rel);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
           $loops[] = $row->id;
        }
        return $loops;
    }

    function count_motifs($rel)
    {
        $this->db->select('ml_motifs_id')
                 ->from('ml_motifs')
                 ->where('release_id', $rel);
        return $this->db->count_all_results();
    }

    function make_link($id, $rel)
    {
        return anchor(base_url(array("motif/view",$rel,$id)), $id);
    }

    function get_intersection()
    {
        $this->db->select('ml_motifs.ml_motifs_id')
                 ->from('ml_motifs')
                 ->join('ml_motifs t', 'ml_motifs.ml_motifs_id=t.ml_motifs_id')
                 ->where('ml_motifs.release_id', $this->rel1)
                 ->where('t.release_id', $this->rel2);
        $result = $this->db->get()->result_array();
        $list = array();
        for ($i = 0; $i < count($result); $i++) {
            $list[] = $this->make_link($result[$i]['ml_motifs_id'], $this->rel1);
        }
        return $list;
    }

    function get_diff()
    {
        $this->db->select('ml_motifs_id, handle');
        $this->db->from('ml_motifs');
        $this->db->where('release_id', $this->rel1);
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $rel1_ids[$result[$i]['handle']] = $result[$i]['id'];
        }

        $this->db->select('id, handle');
        $this->db->from('ml_motifs');
        $this->db->where('release_id', $this->rel2);
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $rel2_ids[$result[$i]['handle']] = $result[$i]['id'];
        }

        foreach (array_merge(array_diff_key($rel1_ids, $rel2_ids)) as $handle => $id) {
            $d['only_in_1'][] = $this->make_link($id, $this->rel1);
        }
        foreach (array_merge(array_diff_key($rel2_ids, $rel1_ids)) as $handle => $id) {
            $d['only_in_2'][] = $this->make_link($id, $this->rel2);
        }
        return $d;
    }

    function get_updated()
    {
        $this->db->select('ml_motifs.ml_motifs_id');
        $this->db->from('ml_motifs');
        $this->db->join('ml_motifs t', 'ml_motifs.handle=t.handle');
        $this->db->where('ml_motifs.release_id', $this->rel1);
        $this->db->where('t.release_id', $this->rel2);
//        $this->db->where('t.version >', 1);
        $this->db->where('t.version != ml_motifs.version');
        $result = $this->db->get()->result_array();
        $list = array();
        for ($i = 0; $i < count($result); $i++) {
            $list[] = $this->make_link($result[$i]['ml_motifs_id'], $this->rel1);
        }
        return $list;
    }

}

/* End of file history_model.php */
/* Location: ./application/model/history_model.php */