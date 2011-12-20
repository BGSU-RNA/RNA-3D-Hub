<?php
class Ajax extends CI_Controller {

	public function get_exemplar_coordinates()
	{
        $input = $this->input->post('motif_id');
        if ($input != false) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_exemplar_coordinates($input);
        } else {
            echo 'No input';
        }
	}

	public function get_loop_coordinates()
	{
        $input = $this->input->post('loop_id');
        if ($input != false) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_loop_coordinates($input);
        } else {
            echo 'No input';
        }
	}

    public function get_coordinates()
    {
        $input = $this->input->post('model');
        if ($input != false) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_coordinates($input);
        } else {
            echo 'No input';
        }
    }

}

/* End of file ajax.php */
/* Location: ./application/controllers/ajax.php */