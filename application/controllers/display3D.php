<?php
class Display3D extends CI_Controller {

	public function unitid($coord)
	{
	 
        $data['baseurl']  = base_url();
        $data['coord'] = str_replace('%7C', '|', $coord);
        
        $this->load->view('header_view', $data);
        $this->load->view('display3D_unitid', $data);
        
	}

}

/* End of file ajax.php */