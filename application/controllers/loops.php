<?php
class Loops extends CI_Controller {

	public function index()
	{
        echo 'Loops';


	}

    public function sfdata()
    {
	    $this->load->model('Loops_model', '', TRUE);
	    $table = $this->Loops_model->get_sfdata_table();


        $heading = $this->Loops_model->get_heading();

        $this->table->set_heading($heading);
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table draggable ' id='sftable'>" );
        $this->table->set_template($tmpl);
        $data['table'] = $this->table->generate($table);
        $data['title'] = 'Sfcheck and mapman';

        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_sfdata_view', $data);
        $this->load->view('footer');
    }


}

/* End of file loops.php */
/* Location: ./application/controllers/loops.php */