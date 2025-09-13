<?php
namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\loops_model;

class Loops extends BaseController {

    public $Loops_model;

    public function __construct()
    {
        $this->Loops_model = new loops_model(); // use Ajax_model like in previous code below
    }

	public function index()
	{
	    $tables = $this->Loops_model->get_loop_stats();
        $motif_types = array('IL','HL','J3');
        foreach ($motif_types as $motif_type) {
            $table = new \CodeIgniter\View\Table();

            $table->setHeading('Total', 'Valid', 'Missing', 'Modified', 'Abnormal', 'Incomplete', 'Complementary');
            $tmpl = array('table_open' => "<table class='condensed-table zebra-striped bordered-table'>");
            $table->setTemplate($tmpl);
            $data['tables'][$motif_type] = $table->generate($tables[$motif_type]);
        }

        $data['title']   = 'All Loops';
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('loops_all_view', $data)
             . view('footer');
	}

    public function view_all($type,$motif_type)
    {
        $config['per_page'] = '20';

        $data['table'] = $this->Loops_model->get_loops($type,
                                                       $motif_type,
                                                       $config['per_page'],
                                                       $this->uri->segment(6));
        // load pagination class
        $this->load->library('pagination');
        $config['base_url']         = base_url(array('loops','view_all',$type,$motif_type));
        $config['uri_segment']      = '6';
        $config['total_rows']       = $this->Loops_model->get_loops_count($type,$motif_type);
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
        $data['type']       = $type;
        $data['motif_type'] = $motif_type;
        $data['baseurl'] = base_url();

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('loops_paginated_view', $data)
             . view('footer');
    }

    public function sfdata()
    {
        $this->Loops_model->initialize_sfdata();
	    $table = $this->Loops_model->get_sfdata_table();

        $heading = $this->Loops_model->get_heading();

        $table->setHeading($heading);
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table draggable ' id='sftable'>" );
        $table->setTemplate($tmpl);
        $data['table'] = $table->generate($table);
        $data['title'] = 'Sfcheck and mapman';
        $data['pageicon'] = base_url() . 'icons/L_icon.png';

        $data['fields']  = $this->Loops_model->get_fields_array();
        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('loops_sfdata_view', $data)
             . view('footer');
    }

    public function graphs()
    {
        $data['graphs'] = $this->Loops_model->get_graphs();

        $data['title'] = 'Graphs';
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('loops_graphs_view', $data)
             . view('footer');
    }

    function sfjmol($pdb = NULL)
    {
        if ($pdb == NULL) {
            $pdbs = $this->Loops_model->get_dcc_pdbs();
            // $list = $this->table->make_columns($pdbs, 16);
            $tmpl = array( 'table_open'  => '<table class="bordered-table">' );
            $table->setTemplate($tmpl);
            $data['pdb_count']= count($pdbs);
            $data['table']   = $table->generate($list);
            $data['title']   = 'Sfcheck and Mapman';
            $data['pageicon'] = base_url() . 'icons/L_icon.png';
            $data['baseurl'] = base_url();
            return view('header_view', $data)
                 . view('menu_view', $data)
                 . view('loops_jmolpdbs_view', $data)
                 . view('footer');
        } else {
            $data['min']     = $this->Loops_model->get_min($pdb);
            $data['max']     = $this->Loops_model->get_max($pdb);
            $data['files']   = 'http://rna.bgsu.edu/img/MotifAtlas/dcc_files/';
            $data['pdb']     = $pdb;
            $data['title']   = 'Sfcheck and Mapman: ' . $pdb;
            $data['baseurl'] = base_url();
            $data['fields']  = $this->Loops_model->get_fields_array();
            return view('header_view', $data)
                 . view('menu_view', $data)
                 . view('loops_sfjmol_view', $data)
                 . view('footer');
        }
    }

    function download($pdb_id)
    {
        $data = $this->Loops_model->get_loop_list($pdb_id);

        $filename = "{$pdb_id}_loops.csv";

        // bypass the view system to avoid debugging comments
        $response = service('response');
        $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                 ->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                 ->setContentType('text/csv');
        $response->setBody($data);
        return $response;
    }

    function download_with_breaks($pdb_id)
    {
        $data = $this->Loops_model->get_loop_list_with_breaks($pdb_id);
        $filename = "{$pdb_id}_loops_with_breaks.csv";
        // bypass the view system to avoid debugging comments
        $response = service('response');
        $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setHeader('Access-Control-Allow-Origin', '*')
                ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                ->setContentType('text/csv');
        $response->setBody($data);
        return $response;
    }

    function view($id, $similar=NULL)
    {
        // view an individual loop
        //$this->output->cache(262974); # 6 months

        if ( !$this->Loops_model->is_valid_loop_id($id) ) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = array();
        if ( !is_null($similar) and $similar == 'similar' ) {
            $table  = $this->Loops_model->get_similar_loops($id);
            $table->setHeading('#','loop id','Disc','Motif id','Conflict');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sortable'>" );
            $table->setTemplate($tmpl);
            $data['table'] = $table->generate($table);
            $data['show_similar'] = TRUE;
        } else {

            $data = array_merge($data, $this->Loops_model->get_loop_info($id));
            // $data = array_merge($data, $this->Loops_model->get_unit_id($id));
            $data = array_merge($data, $this->Loops_model->get_pdb_info($id));
            $data = array_merge($data, $this->Loops_model->get_motif_info($id));
            $data = array_merge($data, $this->Loops_model->get_current_chains($id));
            $data = array_merge($data, $this->Loops_model->get_nearby_chains($id));

            // below is WEAKKKKK with this string matching
            if($data['motif_id'] == "Not in a motif group"){
                if($data['annotation_1'] == 'No text annotation'){
                    $mapping_data = $this->Loops_model->get_mapped_loop($id);
                    if(!is_null($mapping_data) && $id != $mapping_data->mapped_loop){
                        $data = array_merge($data, $this->Loops_model->get_motif_info($mapping_data->mapped_loop));
                        $data['is_mapped_to'] = $mapping_data->mapped_loop;
                        $data['mapping_discrepancy'] = $mapping_data->discrepancy;

                        // $data['is_mapped_to'] = anchor_popup("loops/view/$mapping_data->mapped_loop", $mapping_data->mapped_loop);

                        if($mapping_data->match_type == "geometric"){
                            $data['match_type'] = "Geometric match";
                        }
                        if($mapping_data->match_type == "homologous"){
                            $data['match_type'] = "Homologous match";
                        }
                    }
                }
            }
            $data['show_similar'] = FALSE;
        }

        $data['title'] = 'Loop ' . $id;
        $data['pageicon'] = base_url() . 'icons/L_icon.png';
        $data['id']    = $id;
        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('loops_single_view', $data)
             . view('footer');
    }

    function benchmark($kind = NULL)
    {
        $this->output->cache(262974); # 6 months

        if ($kind == NULL) {
            redirect(site_url(array('loops', 'benchmark', 'IL')));
        } else {
            $this->load->model('Loops_benchmark_model', '', TRUE);
            $table  = $this->Loops_benchmark_model->get_benchmark_table($kind);

            $table->setHeading('#','id','chain','FR3D','RNA3DMotif','SCOR','RLooM','RNAJunction','CoSSMos','Manual annotation');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sortable'>" );
            $table->setTemplate($tmpl);
            $data['table'] = $table->generate($table);
            $data['kind']    = $kind;
            $data['title']   = 'Loop extraction benchmark';
            $data['pageicon'] = base_url() . 'icons/L_icon.png';
            $data['baseurl'] = base_url();
            return view('header_view', $data)
                 . view('menu_view', $data)
                 . view('loops_benchmark_view', $data)
                 . view('footer');
        }
    }
}

/* End of file loops.php */
/* Location: ./application/controllers/loops.php */