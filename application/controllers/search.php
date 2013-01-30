<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title']  = "Search RNA 3D Hub";
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('search_view', $data);
        $this->load->view('footer');
    }

}

/* End of file motif.php */
/* Location: ./application/controllers/search.php */