<?php

class Admin_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        // Call the Model constructor
        parent::__construct();
    }

    public function verify_user($username, $password)
    {
        $this->db->where('username', $username)
                 ->where('password', sha1($password))
                 ->limit(1);
        $q = $this->db->get('users');

        if ( $q->num_rows > 0 ) {
         // person has account with us
         return $q->row();
        }
        return false;
    }

}