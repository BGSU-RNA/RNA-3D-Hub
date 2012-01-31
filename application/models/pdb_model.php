<?php
class Pdb_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

        // Call the Model constructor
        parent::__construct();
    }

    function get_all_pdbs()
    {
        $this->db->select('pdb')
                 ->distinct()
                 ->from('loops_all');
        $query = $this->db->get();
        $pdbs = array();
        foreach ($query->result() as $row) {
            $pdbs[] = anchor(base_url(array('pdb','loops',$row->pdb)), $row->pdb );
        }
        return $pdbs;
    }

    function get_loops($pdb_id)
    {
        $release_id = $this->get_latest_loop_release();
//         $release_id = '0.2';
        $this->db->select()
                 ->from('loop_qa')
                 ->join('loops_all', 'loops_all.id=loop_qa.id')
                 ->where('pdb', $pdb_id)
                 ->where('release_id', $release_id);
        $query = $this->db->get();

        $loop_types = array('IL','HL','J3');
        foreach ($loop_types as $loop_type) {
            $valid_tables[$loop_type] = array();
            $invalid_tables[$loop_type] = array();
        }

        foreach ($query->result() as $row) {
            $loop_type = substr($row->id,0,2);
            if ($row->status == 1) {
                $valid_tables[$loop_type][] = array(count($valid_tables[$loop_type])+1,
                                                    $this->get_checkbox($row->id, $row->nt_ids),
                                                    $row->loop_name);
            } else {
                if (!is_null($row->complementary)) {
                    $annotation = $row->complementary;
                } elseif (!is_null($row->modifications)) {
                    $annotation = $row->modifications;
                } else {
                    $annotation = $row->nt_signature;
                }
                $invalid_tables[$loop_type][] = array(count($invalid_tables[$loop_type])+1,
                                                      $this->get_checkbox($row->id, $row->nt_ids),
                                                      $this->make_reason_label($row->status),
                                                      $annotation);
            }
        }
        return array('valid' => $valid_tables, 'invalid' => $invalid_tables);
    }

    function make_reason_label($status)
    {
        return '<label class="label important">' . $this->qa_status[$status] . '</label>';
    }

    function get_checkbox($id, $nt_ids)
    {
        return "<input type='radio' name='p' id='{$id}' class='jmolInline' data-nt='{$nt_ids}'>{$id}";
    }

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