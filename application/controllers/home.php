<?php
class Home extends CI_Controller {

	public function index()
	{
        $data['title'] = 'All Motif Atlas releases';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('home_view', $data);
	}

}

/* End of file home.php */
/* Location: ./application/controllers/home.php */