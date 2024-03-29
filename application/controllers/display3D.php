<?php
class Display3D extends CI_Controller {

	public function unitid($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('display3D_unitid', $data);

	}

        public function chain($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);
        $data['method'] = $this->router->fetch_method();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('display3D_unitid', $data);

	}

        public function multiple($coord)
	{

        $data['title'] = "3D Coordinate Viewer";
        $data['pageicon'] = base_url() . 'icons/V_icon.png';
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);
        $data['coord'] = str_replace('%20', '', $data['coord']);

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('display3D_unitid_multiple', $data);

	}

}

/* End of file ajax.php */