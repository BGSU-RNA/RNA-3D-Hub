<?php
class Motifs extends CI_Controller {

	public function index()
	{
        $this->load->helper('url');
	    $this->load->model('Motifs_model', '', TRUE);
        $result = $this->Motifs_model->get_all_releases();

        $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('Release id', 'All changes', 'Description', 'Loops', 'Motifs');
        $data['table']['ils'] = $this->table->generate($result['IL']);

        $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('Release id', 'All changes', 'Description', 'Loops', 'Motifs');
        $data['table']['hls'] = $this->table->generate($result['HL']);

        $data['title'] = 'All Motif Atlas Releases';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_all_view', $data);
        $this->load->view('footer');
	}

	public function release($motif_type,$id)
	{
	    $motif_type = strtolower($motif_type);
	    $this->load->model('Motifs_model', '', TRUE);
        if ($id == 'current') {
            $id = $this->Motifs_model->get_latest_release($motif_type);
        }
        $result = $this->Motifs_model->get_release($motif_type,$id);

        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('#', 'Varna 2D', 'Motif id', 'Status', 'Instances');
        $data['table']  = $this->table->generate($result['table']);
        $data['status'] = $this->Motifs_model->get_release_status($motif_type,$id);
        $data['counts'] = $result['counts'];
        $data['title']  = 'Motif Atlas Release ' . $id;
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_view', $data);
        $this->load->view('footer');
	}

    public function compare_releases()
    {
        $this->load->model('Motifs_model', '', TRUE);

        $result = $this->Motifs_model->get_compare_radio_table();

        $result['IL'] = $this->table->make_columns($result['IL'], 2);
        $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('Release 1', 'Release 2');
        $data['table']['ils'] = $this->table->generate($result['IL']);

        $result['HL'] = $this->table->make_columns($result['HL'], 2);
        $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('Release 1', 'Release 2');
        $data['table']['hls'] = $this->table->generate($result['HL']);

        $data['title']      = 'Compare Motif Atlas Releases';
        $data['baseurl']    = base_url();
        $data['action_il']  = base_url('motifs/compare/il');
        $data['action_hl']  = base_url('motifs/compare/hl');
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_compare_view', $data);
        $this->load->view('footer');
    }

	public function compare($motif_type, $rel1 = NULL, $rel2 = NULL)
	{
        if ($rel1 == NULL and $rel2 == NULL) {
            $rel1 = $this->input->post('release1');
            $rel2 = $this->input->post('release2');
        }
	    $motif_type = strtolower($motif_type);

        $this->load->model('Motifs_model', '' , TRUE);
        $data = $this->Motifs_model->get_release_diff($motif_type,$rel1,$rel2);

        $data['title'] = "{$rel1} | {$rel2}";
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_compare_results_view', $data);
        $this->load->view('footer');
	}

    public function release_history()
    {
        $this->load->model('Motifs_model', '' , TRUE);
        $result = $this->Motifs_model->get_complete_release_history();

        $this->table->set_heading('Release','Added groups','Removed groups','Updated groups','Added pdbs','Removed pdbs');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped' id='sort'>" );
        $this->table->set_template($tmpl);
        $data['table']['ils'] = $this->table->generate($result['IL']);

        $this->table->set_heading('Release','Added groups','Removed groups','Updated groups','Added pdbs','Removed pdbs');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped' id='sort'>" );
        $this->table->set_template($tmpl);
        $data['table']['hls'] = $this->table->generate($result['HL']);

        $data['title'] = 'Release History';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_history_view', $data);
        $this->load->view('footer');
    }

}

/* End of file motifs.php */
/* Location: ./application/controllers/motifs.php */