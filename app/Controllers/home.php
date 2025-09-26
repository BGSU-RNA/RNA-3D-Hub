<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;


class Home extends BaseController {

	public function index()
	{
        $data['title'] = 'RNA 3D Hub';
        $data['pageicon'] = base_url() . 'icons/R_icon.png';
        $data['baseurl'] = base_url();

        return view('header_view', $data)
                . view('menu_view', $data)
                . view('home_view', $data)
                . view('footer');
	}

}

/* End of file home.php */
/* Location: ./application/controllers/home.php */