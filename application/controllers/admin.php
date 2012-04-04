<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

   public function __construct()
   {
      session_start();
      parent::__construct();
   }

    public function index()
    {
      if ( isset($_SESSION['username']) ) {
         redirect('home');
      }

      $this->load->library('form_validation');
      $this->form_validation->set_rules('username', 'Username', 'required|min_length[5]');
      $this->form_validation->set_rules('password', 'Password', 'required|min_length[5]');

      if ( $this->form_validation->run() !== false ) {
         // then validation passed. Get from db
         $this->load->model('Admin_model', '', TRUE);
         $res = $this->Admin_model->verify_user(
                     $this->input->post('username'),
                     $this->input->post('password')
                  );

         if ( $res !== false ) {
            $_SESSION['username'] = $this->input->post('username');
            redirect('home');
         }

      }

      $data['baseurl'] = base_url();
      $data['title'] = 'RNA 3D Hub Login';
      $this->load->view('header_view', $data);
      $this->load->view('menu_view', $data);
      $this->load->view('login_view');
      $this->load->view('footer');
   }

   public function logout()
   {
      session_destroy();
      $data['baseurl'] = base_url();
      $data['title'] = 'RNA 3D Hub Login';
      $this->load->view('header_view', $data);
      $this->load->view('menu_view', $data);
      $this->load->view('login_view');
      $this->load->view('footer');
   }
}
