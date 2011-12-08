<?php
class Ajax_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        // Call the Model constructor
        parent::__construct();
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

        // find all constituent nucleotides
        $this->db->distinct('nt_id')
                 ->from('ml_loop_positions')
                 ->where('loop_id',$row->loop_id);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $nt_ids[] = $row->nt_id;
        }

        // get their coordinates
        $this->db->select('coordinates')
                 ->from('motifatlas.coordinates_copy')
                 ->where_in('nt_id',$nt_ids);
        $query = $this->db->get();

        $final_result = "MODEL     1\n";
        foreach ($query->result() as $row) {
            $final_result .= $row->coordinates . "\n";
        }
        $final_result .= "ENDMDL\n";

        // get neighborhood
        // SELECT DISTINCT(t1.Coordinates)
        // FROM distances_copy AS t2
        // JOIN coordinates_copy AS t1
        // ON t1.nt_id = t2.id1
        // WHERE t2.id2 IN ($nts) AND t2.id1 NOT IN ($nts);
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('motifatlas.coordinates_copy')
                 ->join('motifatlas.distances_copy','coordinates_copy.nt_id=distances_copy.id1')
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

}

/* End of file ajax_model.php */
/* Location: ./application/model/ajax_model.php */