<?php
class Unitid extends CI_Controller {

	public function index()
	{

        // redirect to Wordpress
        echo 'redirecting...';

    }

    public function describe($unit_id)
    {
        $this->load->model('Unitid_model', '', TRUE);

        $data = $this->Unitid_model->get_unit_id_info($unit_id);
        if ( !$data ) {
            show_404();
        }

        $data['title']   = $unit_id;
        $data['baseurl'] = base_url();
        $data['unit_id'] = $unit_id;
        $this->load->view('header_view', $data);
        $this->load->view('unit_id_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('footer');
	}

}

/* End of file unitid.php */
/* Location: ./application/controllers/unitid.php */