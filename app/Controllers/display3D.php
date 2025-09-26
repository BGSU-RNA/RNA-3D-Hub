<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\router;

class Display3D extends BaseController {

	public function unitid($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('display3D_unitid', $data);

	}

        public function chain($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);
        // Router.php was in the system/core directory
        // Seems to return 'index' or something like that
        // 'method' does not seem to be used anyway
        // $data['method'] = $this->router->fetch_method();

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('display3D_unitid', $data);
	}

        public function multiple($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);
        $data['coord'] = str_replace('%20', '', $data['coord']);

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('display3D_unitid_multiple', $data);

	}

        public function pdb_chain_range($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('display3D_unitid', $data);

	}


}

/* End of file display3D.php */