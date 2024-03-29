<?php
class Unitid extends CI_Controller {

	public function index()
	{
        // redirect to Wordpress
        header('Location: http://rna.bgsu.edu/main/unit-ids/');
    }

    public function describe($unit_id)
    {
        $unit_id = urldecode($unit_id);

        $this->load->model('Unitid_model', '', TRUE);

        $data = $this->Unitid_model->get_unit_id_info($unit_id);
        if ( !$data ) {
            show_404();
        }

        $data['title']   = $unit_id;
        $data['baseurl'] = base_url();
        $data['unit_id'] = $unit_id;
        $data['pageicon'] = base_url() . 'icons/U_icon.png';
        $this->load->view('header_view', $data);
        $this->load->view('unit_id_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('footer');
	}

}

/* End of file unitid.php */
/* Location: ./application/controllers/unitid.php */