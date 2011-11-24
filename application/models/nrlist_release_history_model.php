<?php
class Nrlist_release_history_model extends CI_Model {

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

    function set_resolution($resolution)
    {
        $this->resolution = $resolution;
    }

    function get_loops($rel)
    {
        $this->db->select('id');
        $this->db->from('loops');
        $this->db->where('release_id', $rel);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
           $loops[] = $row->id;
        }
        return $loops;
    }

    function count_motifs($rel)
    {
        $this->db->select('id')->from('nr_classes');
        $this->db->where('release_id', $rel)
                 ->where('resolution', $this->resolution);
        return $this->db->count_all_results();
    }

    function make_link($id, $rel)
    {
        return anchor("nrlist/view/" . $id, $id);
    }

    function get_intersection()
    {
        $this->db->select('nr_classes.id');
        $this->db->from('nr_classes');
        $this->db->join('nr_classes t', 'nr_classes.id=t.id');
        $this->db->where('nr_classes.resolution',$this->resolution);
        $this->db->where('t.resolution',$this->resolution);
        $this->db->where('nr_classes.release_id', $this->rel1);
        $this->db->where('t.release_id', $this->rel2);

        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $list[] = $this->make_link($result[$i]['id'], $this->rel1);
        }
        return $list;
    }

    function get_diff()
    {
        $this->db->select('id, handle')->from('nr_classes');
        $this->db->where('release_id', $this->rel1)
                 ->where('resolution', $this->resolution);
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $rel1_ids[$result[$i]['handle']] = $result[$i]['id'];
        }

        $this->db->select('id, handle')->from('nr_classes');
        $this->db->where('release_id', $this->rel2)
                 ->where('resolution', $this->resolution);
        $result = $this->db->get()->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $rel2_ids[$result[$i]['handle']] = $result[$i]['id'];
        }

        $d = array();
        $d['only_in_1'] = array();
        $d['only_in_2'] = array();
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
        $this->db->select('nr_classes.id')
                 ->from('nr_classes')
                 ->join('nr_classes t', 'nr_classes.handle=t.handle')
                 ->where('nr_classes.release_id', $this->rel1)
                 ->where('t.release_id', $this->rel2)
                 ->where('nr_classes.resolution', $this->resolution)
                 ->where('t.resolution', $this->resolution)
                 ->where('t.version != nr_classes.version');
        $result = $this->db->get()->result_array();
        $list = array();
        for ($i = 0; $i < count($result); $i++) {
            $list[] = $this->make_link($result[$i]['id'], $this->rel1);
        }
        return $list;
    }

}

/* End of file nrlist_release_history_model.php */
/* Location: ./application/model/nrlist_release_history_model.php */