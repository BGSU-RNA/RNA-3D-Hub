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
        $this->db->select('pdb')->distinct();
        $this->db->from('all_loops');
        $query = $this->db->get();
        $pdbs = array();
        foreach ($query->result() as $row) {
            $pdbs[] = anchor( array('pdb','loops',$row->pdb), $row->pdb );
        }        
        return $pdbs;
    }

    function get_checkbox($id, $nt_ids)
    {
        return "<label><input type='checkbox' id='{$id}' class='jmolInline' data-nt='{$nt_ids}'>{$id}</label>";
    }

    function get_all_valid_internal_loops($id)
    {
        $this->db->select()->from('all_loops as t1');
        $this->db->join('loop_qa as t2','t1.id=t2.id');
        $this->db->where('release_id',$this->latest_release);
        $this->db->where('type','IL');
        $this->db->where('pdb',$id);
        $this->db->where('valid',1);      
        $this->db->order_by('length','desc');
        $this->db->order_by('t1.id');        
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
        }        
        return $ils;
    }
    
    function get_all_modified_internal_loops($id)
    {
        $this->db->select()->from('all_loops as t1');
        $this->db->join('loop_qa as t2','t1.id=t2.id');
        $this->db->join('loop_modifications as t3','t2.id=t3.id');        
        $this->db->where('release_id',$this->latest_release);
        $this->db->where('type','IL');
        $this->db->where('pdb',$id);
        $this->db->where('modified_nt',1);      
        $this->db->order_by('length','desc');
        $this->db->order_by('t1.id');        
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->modification);
        }        
        return $ils;        
    }

    function get_all_missing_nts_internal_loops($id)
    {
        $this->db->select()->from('all_loops as t1');
        $this->db->join('loop_qa as t2','t1.id=t2.id');
        $this->db->where('release_id',$this->latest_release);
        $this->db->where('type','IL');
        $this->db->where('pdb',$id);
        $this->db->where('missing_nt',1);    
        $this->db->order_by('length','desc');
        $this->db->order_by('t1.id');                
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
        }        
        return $ils;        
    }

    function get_all_valid_hairpin_loops($id)
    {
        $this->db->select()->from('all_loops as t1');
        $this->db->join('loop_qa as t2','t1.id=t2.id');
        $this->db->where('release_id',$this->latest_release);
        $this->db->where('type','HL');
        $this->db->where('pdb',$id);
        $this->db->where('valid',1);      
        $this->db->order_by('length','desc');
        $this->db->order_by('t1.id');                
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
        }        
        return $ils;
    }

    function get_all_modified_hairpin_loops($id)
    {
        $this->db->select()->from('all_loops as t1');
        $this->db->join('loop_qa as t2','t1.id=t2.id');
        $this->db->join('loop_modifications as t3','t2.id=t3.id');        
        $this->db->where('release_id',$this->latest_release);
        $this->db->where('type','HL');
        $this->db->where('pdb',$id);
        $this->db->where('modified_nt',1);   
        $this->db->order_by('length','desc');
        $this->db->order_by('t1.id');                
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->modification);
        }        
        return $ils;        
    }

    function get_all_missing_nts_hairpin_loops($id)
    {
        $this->db->select()->from('all_loops as t1');
        $this->db->join('loop_qa as t2','t1.id=t2.id');
        $this->db->where('release_id',$this->latest_release);
        $this->db->where('type','HL');
        $this->db->where('pdb',$id);
        $this->db->where('missing_nt',1);    
        $this->db->order_by('length','desc');
        $this->db->order_by('t1.id');                
        $query = $this->db->get();
        $ils = array();
        foreach ($query->result() as $row) {
            $ils[] = array($this->get_checkbox($row->id,$row->nt_ids), $row->seq);
        }        
        return $ils;        
    }


    function get_latest_loop_release()
    {
        $this->db->select('id')->from('loop_releases');
        $this->db->order_by('date','desc')->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['id'];
    }

    
}

/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */