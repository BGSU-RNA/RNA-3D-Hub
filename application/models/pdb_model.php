<?php
class Pdb_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');

        $this->latest_release = $this->get_latest_loop_release();

        // Call the Model constructor
        parent::__construct();
    }

    function get_all_pdbs()
    {
        $this->db->select('pdb')
                 ->distinct()
                 ->from('all_loops');
        $query = $this->db->get();
        $pdbs = array();
        foreach ($query->result() as $row) {
            $pdbs[] = anchor(base_url(array('pdb','loops',$row->pdb)), $row->pdb );
        }
        return $pdbs;
    }

    function get_checkbox($id, $nt_ids)
    {
        return "<label><input type='checkbox' id='{$id}' class='jmolInline' data-nt='{$nt_ids}'>{$id}</label>";
    }

    function get_all_valid_loops($pdb_id, $loop_type)
    {
        $this->db->select()
                 ->from('all_loops')
                 ->join('loop_qa','all_loops.id=loop_qa.id')
                 ->where('release_id',$this->latest_release)
                 ->where('type',$loop_type)
                 ->where('pdb',$pdb_id)
                 ->where('valid',1)
                 ->order_by('length','desc')
                 ->order_by('all_loops.id');
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
        }
        return $ils;
    }

    function get_all_modified_loops($pdb_id, $loop_type)
    {
        $this->db
             ->select()
             ->from('all_loops as t1')
             ->join('loop_qa as t2','t1.id=t2.id')
             ->join('loop_modifications as t3','t2.id=t3.id')
             ->where('release_id',$this->latest_release)
             ->where('type', $loop_type)
             ->where('pdb', $pdb_id)
             ->where('modified_nt',1)
             ->order_by('length','desc')
             ->order_by('t1.id');
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->modification);
        }
        return $ils;
    }

    function get_all_missing_nts_loops($pdb_id, $loop_type)
    {
        $this->db->select()
             ->from('all_loops as t1')
             ->join('loop_qa as t2','t1.id=t2.id')
             ->where('release_id',$this->latest_release)
             ->where('type', $loop_type)
             ->where('pdb', $pdb_id)
             ->where('missing_nt',1)
             ->order_by('length','desc')
             ->order_by('t1.id');
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
        }
        return $ils;
    }

//     function get_all_valid_hairpin_loops($id)
//     {
//         $this->db->select()
//              ->from('all_loops as t1')
//              ->join('loop_qa as t2','t1.id=t2.id')
//              ->where('release_id',$this->latest_release)
//              ->where('type','HL')
//              ->where('pdb',$id)
//              ->where('valid',1)
//              ->order_by('length','desc')
//              ->order_by('t1.id');
//         $query = $this->db->get();
//         $ils = array();
//         foreach ($query->result() as $row) {
//             $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
//         }
//         return $ils;
//     }
//
//     function get_all_modified_hairpin_loops($id)
//     {
//         $this->db->select()
//                  ->from('all_loops as t1')
//                  ->join('loop_qa as t2','t1.id=t2.id')
//                  ->join('loop_modifications as t3','t2.id=t3.id')
//                  ->where('release_id',$this->latest_release)
//                  ->where('type','HL')
//                  ->where('pdb',$id)
//                  ->where('modified_nt',1)
//                  ->order_by('length','desc')
//                  ->order_by('t1.id');
//         $query = $this->db->get();
//         $ils = array();
//         foreach ($query->result() as $row) {
//             $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->modification);
//         }
//         return $ils;
//     }
//
//     function get_all_missing_nts_hairpin_loops($id)
//     {
//         $this->db->select()
//                  ->from('all_loops as t1')
//                  ->join('loop_qa as t2','t1.id=t2.id')
//                  ->where('release_id',$this->latest_release)
//                  ->where('type','HL')
//                  ->where('pdb',$id)
//                  ->where('missing_nt',1)
//                  ->order_by('length','desc')
//                  ->order_by('t1.id');
//         $query = $this->db->get();
//         $ils = array();
//         foreach ($query->result() as $row) {
//             $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
//         }
//         return $ils;
//     }

    function get_latest_loop_release()
    {
        $this->db->select('id')
                 ->from('loop_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['id'];
    }


}

/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */