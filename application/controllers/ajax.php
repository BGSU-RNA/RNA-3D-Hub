<?php
class Ajax extends CI_Controller {

	public function get_exemplar_coordinates()
	{
        $input = $this->input->post('motif_id');
        if ($input != FALSE) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_exemplar_coordinates($input);
        } else {
            echo 'No input';
        }
	}

	public function get_loop_coordinates()
	{
        $input = $this->input->post('loop_id');
        if ($input != FALSE) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_loop_coordinates($input);
        } else {
            echo 'No input';
        }
	}

    public function get_nt_coordinates_approximate()
    {
        $input = $this->input->post('model');
        if ($input != FALSE) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_nt_coordinates_approximate($input);
        } else {
            echo 'No input';
        }
    }

    public function get_coordinates()
    {
        $input = $this->input->post('model');
        if ($input != FALSE) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_coordinates($input);
        } else {
            echo 'No input';
        }
    }

    public function get_dcc_data()
    {
        $input = $this->input->post('model');
        if ($input != FALSE) {
            $this->load->model('Ajax_model', '', TRUE);
            echo $this->Ajax_model->get_dcc_data($input);
        } else {
            echo 'No input';
        }
    }

    public function save_loop_extraction_benchmark_annotation()
    {
        $this->load->model('Ajax_model', '', TRUE);
        $content = $this->input->post('content');
        echo $this->Ajax_model->save_loop_extraction_benchmark_annotation($content);
    }

}

/* End of file ajax.php */
/* Location: ./application/controllers/ajax.php */