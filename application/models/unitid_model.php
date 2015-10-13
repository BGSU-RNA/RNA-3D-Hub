<?php
class Unitid_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

        // Call the Model constructor
        parent::__construct();
    }

    function look_up_unit_id($old_id)
    {
        $this->db->select('unit_id')
                 ->from('pdb_unit_id_correspondence')
                 ->where('old_id', $old_id);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            return $query->row()->unit_id;
        } else {
            return 'unknown';
        }
    }

    function get_unit_id_info($unit_id)
    {
        $this->db->select()
                 ->from('pdb_unit_id_correspondence')
                 ->where('unit_id', $unit_id)
                 ->or_where('old_id', $unit_id);
        $query = $this->db->get();

        if ( $query->num_rows() == 0 ) {
            // check pdb_coordinates in case the new unit id hasn't been generated yet
            $this->db->select()
                     ->from('pdb_coordinates')
                     ->where('pdb_coordinates_id', $unit_id);
            $query = $this->db->get();

            if ( $query->num_rows() == 0 ) {
                // id not found
                return FALSE;
            } else {
                $row = $query->row();
                $result[] = array('unit_id'  => 'not available',
                                  'old_id'   => $unit_id,
                                  'model'    => $row->model,
                                  'chain'    => $row->chain,
                                  'seq_id'   => $row->number,
                                  'comp_id'  => $row->unit,
                                  'alt_id'   => '',
                                  'ins_code' => $row->ins_code,
                                  'sym_op'   => 'not available',
                                  'pdb_file' => $row->pdb_type == 'AU' ? 'pdb' : 'pdb1',
                                  'pdb_id'   => $row->pdb_id
                                 );
                return array('result' => $result, 'id_type' => 'old');
            }
        }

        // determine id type
        $parts = explode('_', $unit_id);
        if ( $parts[1] == 'AU' or $parts[1] == 'BA1' ) {
            $id_type = 'old';
        } else {
            $id_type = 'new';
        }

        $result = array();
        foreach($query->result() as $row) {
            $result[] = array('unit_id'  => $row->unit_id,
                              'old_id'   => $row->old_id,
                              'model'    => $row->model,
                              'chain'    => $row->chain,
                              'seq_id'   => $row->seq_id,
                              'comp_id'  => $row->comp_id,
                              'alt_id'   => $row->alt_id,
                              'ins_code' => $row->ins_code,
                              'sym_op'   => $row->sym_op,
                              'pdb_file' => $row->pdb_file,
                              'pdb_id'   => $row->pdb_id
                             );
        }
        return array('result' => $result, 'id_type' => $id_type);
    }

}

/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */