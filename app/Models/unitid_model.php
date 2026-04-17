<?php
namespace App\Models;
use CodeIgniter\Model;

class unitid_model extends Model {

    // function __construct()
    // {
    //     $CI = & get_instance();
    //     $CI->load->helper('url');

    //     $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

    //     // Call the Model constructor
    //     parent::__construct();
    // }

    function look_up_unit_id($old_id)
    {
        // Build the query
        $query = $this->db->table('pdb_unit_id_correspondence')
                        ->select('unit_id')
                        ->where('old_id', $old_id)
                        ->get();

        if ( $query->getNumRows() > 0 ) {
            return $query->getRow()->unit_id;
        } else {
            return 'unknown';
        }
    }

    function get_unit_id_info($unit_id)
    {
        // Build the query
        $query = $this->db->table('unit_info')
                        ->select('*')
                        ->where('unit_id', $unit_id)
                        ->get();

        if ($query->getNumRows() == 0) {
            // id not found
            return FALSE;
        } else {
            $row = $query->getRow();
            $result = array();
            $result[] = array('unit_id'  => $row->unit_id,
                                'model'    => $row->model,
                                'chain'    => $row->chain,
                                'seq_id'   => $row->number,
                                'comp_id'  => $row->unit,
                                'alt_id'   => '',
                                'ins_code' => $row->ins_code,
                                'sym_op'   => 'not available',
                                'pdb_id'   => $row->pdb_id
                                );
            return array('result' => $result, 'id_type' => 'old');
        }
    }
}

/* End of file unitid_model.php */
