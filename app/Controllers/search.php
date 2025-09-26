<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

class Search extends BaseController
{
    public function index()
    {
        $data = [
            'title' => "Search RNA 3D Hub",
            'pageicon' => base_url('icons/S_icon.png'),
            'baseurl' => base_url(),
        ];

        return view('header_view', $data)
        . view('menu_view', $data)
        . view('search_view', $data)
        . view('footer');
    }
}


// <?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// class Search extends MY_Controller {

//     public function __construct()
//     {
//         parent::__construct();
//     }

//     public function index()
//     {
//         $data['title']  = "Search RNA 3D Hub";
//         $data['pageicon'] = base_url() . 'icons/S_icon.png';
//         $data['baseurl'] = base_url();
//         $this->load->view('header_view', $data);
//         $this->load->view('menu_view', $data);
//         $this->load->view('search_view', $data);
//         $this->load->view('footer');
//     }

// }

/* End of file search.php */
/* Location: ./application/controllers/search.php */