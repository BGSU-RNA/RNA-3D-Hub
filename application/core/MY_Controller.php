<?php

class  MY_Controller  extends  CI_Controller  {

    public function __construct ()
    {
        parent::__construct();

        $this->_check_auth();
    }

    private function _check_auth()
    {
        if ( ! $this->session->userdata('username') and ! $this->session->userdata('next') ) {
            $this->session->set_userdata('next', uri_string() );
        }
    }

}


/* End of file MY_Controller.php */
/* Location: ./application/libraries/MY_Controller.php */