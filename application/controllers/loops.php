<?php
class Loops extends CI_Controller {

	public function index()
	{
	    $this->load->model('Loops_model', '', TRUE);
	    $tables = $this->Loops_model->get_loop_releases();

        $motif_types = array('IL','HL','J3');
        foreach ($motif_types as $motif_type) {
            $this->table->set_heading('id','Total','Valid','Modified','Missing','Complementary');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sftable'>" );
            $this->table->set_template($tmpl);
            $data['tables'][$motif_type] = $this->table->generate($tables[$motif_type]);
        }

        $data['title']   = 'All Loops';
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_all_view', $data);
        $this->load->view('footer');
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