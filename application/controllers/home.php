<?php
class Home extends CI_Controller {

	public function index()
	{
        $data['title'] = 'BGSU RNA Site';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('home_view', $data);
        $this->load->view('footer');
	}

}

/* End of file home.php */
/* Location: ./application/controllers/home.php */