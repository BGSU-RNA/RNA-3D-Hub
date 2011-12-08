<?php
class Ajax extends CI_Controller {

	public function get_exemplar_coordinates()
	{
        $motif_id = $this->input->post('motif_id');
	    $this->load->model('Ajax_model', '', TRUE);
        echo $this->Ajax_model->get_exemplar_coordinates($motif_id);
	}



}

/* End of file ajax.php */
/* Location: ./application/controllers/ajax.php */