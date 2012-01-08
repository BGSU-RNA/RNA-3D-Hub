<?php
class Ajax_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        // Call the Model constructor
        parent::__construct();
    }

    function get_dcc_data($s)
    {
        // detect what $s is in the future

        // assume nt_list for now
        $nt_ids = explode(',',$s);
        return $this->get_nt_json_dcc($nt_ids);
    }

    function get_nt_json_dcc($nt_ids)
    {
        $lengths = array('C' => 24, 'U' => 23, 'A' => 26, 'G' => 27);

        $list_ids = "'" . implode("','",$nt_ids) . "'";

        $sql_command = '* FROM dcc_residues where id IN ('. $list_ids .') order by(FIELD(id,'.$list_ids.'));';
        $this->db->select($sql_command, FALSE);
        $query = $this->db->get();
        //         $this->db->select()
        //                  ->from('dcc_residues')
        //                  ->where_in('id',$nt_ids)
        //                  ->order_by($list_ids);
        //         $query = $this->db->get();

        $s = array();
        foreach ($query->result() as $row) {
            $parts   = explode('_',$row->id);
            $nt_type = $parts[5];

            $fields = get_object_vars($row);
            unset($fields['id']);

            foreach ($fields as $key => $value) {
                if (!array_key_exists($key,$s)) {
                    $s[$key] = '';
                }
            }
            foreach ($fields as $key => $value) {
                $s[$key] .= str_repeat($value . ' ', $lengths[$nt_type]);
            }
        }
        return json_encode($s);
    }

    function get_coordinates($s)
    {
        // 1S72_AU_1_0_30_U_
        // $is_nt_list = preg_match('/([a-z]|[A-Z]|[0-9]){4}_[a-zA-Z0-9]{2,3}_\d+_\d+_\d+_\[a-zA-Z]/',$s);
        $is_nt_list = substr_count($s,'_');
        if ($is_nt_list > 3) {
            $nt_ids = explode(',',$s);
            return $this->get_nt_coordinates($nt_ids);
        }

        $is_motif_id = preg_match('/(IL|HL|J3)_\d{5}\.\d+/',$s);
        if ($is_motif_id != 0) {
            return $this->get_exemplar_coordinates($s);
        }

        $is_loop_id  = preg_match('/(IL|HL|J3)_\w{4}_\d{3}/',$s);
        if ($is_loop_id != 0) {
            return $this->get_loop_coordinates($s);
        } else {
            return 'Input was not recognized';
        }
    }

    function get_nt_coordinates($nt_ids)
    {
        $this->db->select('coordinates')
                 ->from('coordinates')
                 ->where_in('nt_id',$nt_ids);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop coordinates not found'; }

        $final_result = "MODEL     1\n";
        foreach ($query->result() as $row) {
            $final_result .= $row->coordinates . "\n";
        }
        $final_result .= "ENDMDL\n";

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('coordinates')
                 ->join('distances','coordinates.nt_id=distances.id1')
                 ->where_in('id2',$nt_ids)
                 ->where_not_in('id1',$nt_ids);
        $query = $this->db->get();

         $final_result .= "MODEL     2\n";
         if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $final_result .= $row->coordinates . "\n";
            }
         }
         $final_result .= "ENDMDL";

        return $final_result;
    }

    function get_loop_coordinates($loop_id)
    {
        // find all constituent nucleotides
        $this->db->select('nt_ids')
                 ->distinct()
                 ->from('loops_all')
                 ->where('id',$loop_id);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id not found'; }

        foreach ($query->result() as $row) {
            $nt_ids = explode(',',$row->nt_ids);
        }

        // get their coordinates
        $this->db->select('coordinates')
                 ->from('coordinates')
                 ->where_in('nt_id',$nt_ids);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop coordinates not found'; }

        $final_result = "MODEL     1\n";
        foreach ($query->result() as $row) {
            $final_result .= $row->coordinates . "\n";
        }
        $final_result .= "ENDMDL\n";

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('coordinates')
                 ->join('distances','coordinates.nt_id=distances.id1')
                 ->where_in('id2',$nt_ids)
                 ->where_not_in('id1',$nt_ids);
        $query = $this->db->get();

         $final_result .= "MODEL     2\n";
         if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $final_result .= $row->coordinates . "\n";
            }
         }
         $final_result .= "ENDMDL";

        return $final_result;
    }

    function get_exemplar_coordinates($motif_id)
    {
        // given a motif_id find the representative loop
        $this->db->select('loop_id')
                 ->from('ml_loop_order')
                 ->where('motif_id',$motif_id)
                 ->where('original_order',1);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            return '';
        }
        $row = $query->row();

        return $this->get_loop_coordinates($row->loop_id);
    }

}

/* End of file ajax_model.php */
/* Location: ./application/model/ajax_model.php */