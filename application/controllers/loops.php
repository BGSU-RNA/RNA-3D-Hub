<?php
class Loops extends CI_Controller {

	public function index()
	{
	    $this->load->model('Loops_model', '', TRUE);
	    $tables = $this->Loops_model->get_loop_stats();
        $motif_types = array('IL','HL','J3');
        foreach ($motif_types as $motif_type) {
            $this->table->set_heading('id','Date','Total','Valid','Missing','Modified','Abnormal','Incomplete','Complementary');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
            $this->table->set_template($tmpl);
            $data['tables'][$motif_type] = $this->table->generate($tables[$motif_type]);
        }

        $data['title']   = 'All Loops';
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_all_view', $data);
        $this->load->view('footer');
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

        $motif_full_names = array('IL'=>'internal loops', 'HL' => 'hairpin loops', 'J3' => 'junction loops');
        $data['title']      = 'All ' .  $type . ' ' . $motif_full_names[$motif_type];
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
        $data['release_id'] = $release_id;
        $data['type']       = $type;
        $data['motif_type'] = $motif_type;
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        //$this->load->view('loops_paginated_view', $data);
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
        $data['pageicon'] = base_url() . 'icons/L_icon.png';

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
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
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
            $data['pageicon'] = base_url() . 'icons/L_icon.png';
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

    function download($pdb_id)
    {
        $this->load->model('Loops_model', '', TRUE);

        $data['csv'] = $this->Loops_model->get_loop_list($pdb_id);

        $filename = "{$pdb_id}_loops.csv";
        $this->output->set_header("Access-Control-Allow-Origin: *")
                     ->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin")
                     ->set_header("Content-disposition: attachment; filename=$filename")
                     ->set_content_type('text/csv');

        $this->load->view('csv_view', $data);
    }

    function view($id, $similar=NULL)
    {
        $this->output->cache(262974); # 6 months

        $this->load->model('Loops_model', '', TRUE);

        if ( !$this->Loops_model->is_valid_loop_id($id) ) {
            show_404();
        }

        $data = array();
        if ( !is_null($similar) and $similar == 'similar' ) {
            $table  = $this->Loops_model->get_similar_loops($id);
            $this->table->set_heading('#','loop id','Disc','Motif id','Conflict');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sortable'>" );
            $this->table->set_template($tmpl);
            $data['table'] = $this->table->generate($table);
            $data['show_similar'] = TRUE;
        } else {
            $data = array_merge($data, $this->Loops_model->get_loop_info($id));
            $data = array_merge($data, $this->Loops_model->get_pdb_info($id));
            $data = array_merge($data, $this->Loops_model->get_motif_info($id));
            $data = array_merge($data, $this->Loops_model->get_protein_info($id));
            $data['show_similar'] = FALSE;
        }

        $data['title'] = 'Loop ' . $id;
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
        $data['id']    = $id;
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('loops_single_view', $data);
        $this->load->view('footer');
    }

    function benchmark($kind = NULL)
    {
        $this->output->cache(262974); # 6 months

        if ($kind == NULL) {
            redirect(site_url(array('loops', 'benchmark', 'IL')));
        } else {
            $this->load->model('Loops_benchmark_model', '', TRUE);
            $table  = $this->Loops_benchmark_model->get_benchmark_table($kind);

            $this->table->set_heading('#','id','chain','FR3D','RNA3DMotif','SCOR','RLooM','RNAJunction','CoSSMos','Manual annotation');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sortable'>" );
            $this->table->set_template($tmpl);
            $data['table'] = $this->table->generate($table);
            $data['kind']    = $kind;
            $data['title']   = 'Loop extraction benchmark';
            $data['pageicon'] = base_url() . 'icons/L_icon.png';
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