<?php
class Loops extends CI_Controller {

	public function index()
	{
	    $this->load->model('Loops_model', '', TRUE);
	    $tables = $this->Loops_model->get_loop_releases();

        $motif_types = array('IL','HL','J3');
        foreach ($motif_types as $motif_type) {
            $this->table->set_heading('id','Date','Total','Valid','Modified','Missing','Complementary');
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

        $this->output->enable_profiler(TRUE);

	}

    public function view_all($type,$motif_type,$release_id)
    {
        $config['per_page'] = '20';

	    $this->load->model('Loops_model', '', TRUE);
        $data['table'] = $this->Loops_model->get_loops($type,
                                                       $motif_type,
                                                       $release_id,
                                                       $config['per_page'],
                                                       $this->uri->segment(6));
        if (count($data['table']) == 0) {
            $data['table'] = "No $type $motif_type loops were found";
        } else {
            if ($type == 'modified_nt') {
                $this->table->set_heading('#','id','PDB','modifications');
            } else {
                $this->table->set_heading('#','id','PDB');
            }
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sortable'>" );
            $this->table->set_template($tmpl);
            $data['table'] =  $this->table->generate($data['table']);
        }

        // load pagination class
        $this->load->library('pagination');
        $config['base_url']         = base_url(array('loops','view_all',$type,$motif_type,$release_id));
        $config['uri_segment']      = '6';
        $config['total_rows']       = $this->Loops_model->get_loops_count($type,$motif_type,$release_id);
        $config['num_links']        = 2;
        $config['use_page_numbers'] = TRUE;
        $config['full_tag_open']    = '<div class="pagination"><ul>';
        $config['full_tag_close']   = '</ul></div>';
        $config['cur_tag_open']     = '<li class="active"><a href="#">';
        $config['cur_tag_close']    = '</a></li>';
        $config['num_tag_open']     = '<li>';
        $config['num_tag_close']    = '</li>';
        $config['next_tag_open']    = '<li class="next">';
        $config['next_tag_close']   = '</li>';
        $config['prev_tag_open']    = '<li class="prev">';
        $config['prev_tag_close']   = '</li>';
        $config['prev_link']        = '&larr;';
        $config['next_link']        = '&rarr;';
        $config['first_tag_open']   = '<li>';
        $config['first_tag_close']  = '</li>';
        $config['last_tag_open']    = '<li>';
        $config['last_tag_close']   = '</li>';

        $this->pagination->initialize($config);

        $data['title']      = 'All ' . $motif_type . ' (' . $type . ')';
        $data['type']       = $type;
        $data['motif_type'] = $motif_type;
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_paginated_view', $data);
        $this->load->view('footer');

//         $this->output->enable_profiler(TRUE);
    }

    public function sfdata()
    {
	    $this->load->model('Loops_model', '', TRUE);
        $this->Loops_model->initialize_sfdata();
	    $table = $this->Loops_model->get_sfdata_table();

        $heading = $this->Loops_model->get_heading();

        $this->table->set_heading($heading);
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table draggable ' id='sftable'>" );
        $this->table->set_template($tmpl);
        $data['table'] = $this->table->generate($table);
        $data['title'] = 'Sfcheck and mapman';

        $data['fields']  = $this->Loops_model->get_fields_array();
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_sfdata_view', $data);
        $this->load->view('footer');
    }

    public function graphs()
    {
	    $this->load->model('Loops_model', '', TRUE);
        $data['graphs'] = $this->Loops_model->get_graphs();

        $data['title'] = 'Graphs';
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_graphs_view', $data);
        $this->load->view('footer');
    }

    function sfjmol($pdb = NULL)
    {
        if ($pdb == NULL) {
            $this->load->model('Loops_model', '', TRUE);
            $pdbs = $this->Loops_model->get_dcc_pdbs();
            $list = $this->table->make_columns($pdbs, 16);
            $tmpl = array( 'table_open'  => '<table class="bordered-table">' );
            $this->table->set_template($tmpl);
            $data['pdb_count']= count($pdbs);
            $data['table']   = $this->table->generate($list);
            $data['title']   = 'Sfcheck and Mapman';
            $data['baseurl'] = base_url();
            $this->load->view('header_view', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('loops_jmolpdbs_view', $data);
            $this->load->view('footer');
        } else {
            $this->load->model('Loops_model', '', TRUE);
            $data['min']     = $this->Loops_model->get_min($pdb);
            $data['max']     = $this->Loops_model->get_max($pdb);
            $data['files']   = 'http://rna.bgsu.edu/img/MotifAtlas/dcc_files/';
            $data['pdb']     = $pdb;
            $data['title']   = 'Sfcheck and Mapman: ' . $pdb;
            $data['baseurl'] = base_url();
            $data['fields']  = $this->Loops_model->get_fields_array();
            $this->load->view('header_view', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('loops_sfjmol_view', $data);
            $this->load->view('footer');
        }
    }

    function view($id)
    {
//             $this->load->model('Loops_model', '', TRUE);

            $data['title'] = 'Loop ' . $id;
            $data['id']    = $id;
            $data['baseurl'] = base_url();
            $this->load->view('header_view', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('loops_single_view', $data);
            $this->load->view('footer');
    }

    function benchmark($kind = NULL)
    {
        if ($kind == NULL) {
            echo 'Overview and statistics. Coming soon';
        } else {
            $this->load->model('Loops_model', '', TRUE);
            $table  = $this->Loops_model->get_benchmark_table($kind);

            $this->table->set_heading('id','FR3D','RNA3DMotif','SCOR','RLooM','RNAJunction','CoSSMos','Manual annotation');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sortable'>" );
            $this->table->set_template($tmpl);
            $data['table'] = $this->table->generate($table);
            $data['kind']    = $kind;
            $data['title']   = 'Loop extraction benchmark';
            $data['baseurl'] = base_url();
            $this->load->view('header_view', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('loops_benchmark_view', $data);
            $this->load->view('footer');
        }

    }

}

/* End of file loops.php */
/* Location: ./application/controllers/loops.php */