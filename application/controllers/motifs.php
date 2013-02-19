<?php
class Motifs extends CI_Controller {

	public function oldhome()
	{
        $this->output->cache(8640); # 6 days

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

        $data['title']   = 'All Motif Atlas Releases';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_all_view', $data);
        $this->load->view('footer');
	}

	public function index()
	{
	    $this->load->model('Motifs_model', '', TRUE);

        $data['featured'] = $this->Motifs_model->get_featured_motifs('il');
        $data = array_merge($data, $this->Motifs_model->get_current_release_info());

        $data['title']   = 'RNA 3D Motif Atlas';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_home_view', $data);
        $this->load->view('footer');
	}

    public function polymorphs($motif_type, $release_id)
    {
        $this->load->model('Motifs_model', '', TRUE);
        $table = $this->Motifs_model->get_polymorphs($motif_type, $release_id);

        $this->table->set_heading('Sequence', 'Length', '# of motifs', 'Motifs');
        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        $this->table->set_template($tmpl);
        $data['table'] = $this->table->generate($table);

        $data['title']   = 'Polymorphic motifs';
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_polymorphs_view', $data);
        $this->load->view('footer');
    }

	public function release($motif_type, $id, $format=NULL)
	{
        $this->output->cache(8640); # 6 days

	    $this->load->model('Motifs_model', '', TRUE);

        // download requests
        if ( !is_null($format) ) {

            if ( $format != 'csv' and $format != 'json' ) {
                show_404();
            }

    	    $this->load->model('Motif_model', '', TRUE);

    	    // get all motifs in that release
            $motifs = $this->Motifs_model->get_all_motifs($id, $motif_type);

            $download = array();

            foreach($motifs as $i=>$motif_id) {

                $this->Motif_model->set_motif_id($motif_id);
                // set release id only once
                if ( $i == 0 ) {
                    $release_id = $this->Motif_model->set_release_id();
                }

                if ( $format == 'csv' ) {
                    $this->output->set_header("Content-disposition: attachment; filename={$motif_id}.csv")
                                 ->set_content_type('text/csv');
                    $download[] = ">{$motif_id}\n" . $this->Motif_model->get_csv($motif_id);
                } else {
                    $this->output->set_header("Content-disposition: attachment; filename={$motif_id}.json")
                                 ->set_content_type('application/json');
                    $download[] = $this->Motif_model->get_json($motif_id);
                }
            }

            if ( $format == 'csv' ) {
                $data['csv'] = implode('', $download);
            } else {
                $data['csv'] = '[' . implode(",\n", $download) . ']';
            }

            $this->load->view('csv_view', $data);
            return;
        }

        // not a download request
	    $motif_type = strtolower($motif_type);
        if ($id == 'current') {
            $id = $this->Motifs_model->get_latest_release($motif_type);
        }
        $result = $this->Motifs_model->get_release($motif_type,$id);

        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('#', 'Varna 2D', 'Motif', 'Nts', 'Size');
        $data['table']    = $this->table->generate($result['table']);

        $data['status']   = $this->Motifs_model->get_release_status($motif_type,$id);
        $data['counts']   = $result['counts'];
        if ( $motif_type == 'il' ) {
            $data['title']    = 'Internal Loop Motif Atlas Release ' . $id;
        } else {
            $data['title']    = 'Hairpin Loop Motif Atlas Release ' . $id;
        }
        $data['baseurl']  = base_url();
        $data['alt_view'] = base_url(array('motifs','graph',$motif_type,$id));
        $data['polymorph_url'] = base_url(array('motifs','polymorphs',$motif_type, $id));
        $data['current_url'] = current_url();
        $data['meta']['description'] = 'A list of RNA 3D motifs';

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_view', $data);
        $this->load->view('footer');
	}

	public function releaseinfo($motif_type,$id)
	{
	    $motif_type = strtolower($motif_type);
	    $this->load->model('Motifs_model', '', TRUE);
        if ($id == 'current') {
            $id = $this->Motifs_model->get_latest_release($motif_type);
        }
        $result = $this->Motifs_model->get_release_advanced($motif_type,$id);

        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('#', 'Varna 2D', 'Motif id', 'Status', 'Instances', 'Name', 'Min length', 'Max', 'Diff');
        $data['table']    = $this->table->generate($result['table']);

        $data['status']   = $this->Motifs_model->get_release_status($motif_type,$id);
        $data['counts']   = $result['counts'];
        $data['title']    = 'Motif Atlas Release ' . $id;
        $data['baseurl']  = base_url();
        $data['alt_view'] = base_url(array('motifs','graph',$motif_type,$id));
        $data['polymorph_url'] = base_url(array('motifs','polymorphs',$motif_type, $id));
        $data['meta']['description'] = 'A list of RNA 3D motifs';

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_info_view', $data);
        $this->load->view('footer');
	}

    public function graph($motif_type,$id)
    {
	    $motif_type = strtolower($motif_type);
	    $this->load->model('Motifs_model', '', TRUE);

        $data['graphml'] = $this->Motifs_model->get_graphml($motif_type,$id);

        $data['img_loc']   = strtoupper($motif_type) . $id;
        $data['alt_view']  = base_url(array('motifs','release',$motif_type,$id));
        $data['title']     = 'Motif Atlas Release ' . $id . ' (' . strtoupper($motif_type) . ')';
        $data['baseurl']   = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        if ($data['graphml'] == '') {
            $this->load->view('motifs_graph_not_exists_view', $data);
        } else {
            $this->load->view('motifs_graph_view', $data);
        }
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

        $this->table->set_heading('Release','Added groups','Removed groups','Updated groups','Added loops','Removed loops');
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

    public function secondary_structures($organism = NULL)
    {
        $data['all'] = array(

            'escherichia_coli_16s' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavZFVqNkMwZ0FFWVE/preview',
                'organism' => 'Escherichia coli',
                'type'     => '16S rRNA'
            ),

            'escherichia_coli_23s_5prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavekdrV1dTSUVLdWM/preview',
                'organism' => 'Escherichia coli',
                'type'     => "23S rRNA 5'-half"
            ),

            'escherichia_coli_23s_3prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavc1VWM081enVtYTg/preview',
                'organism' => 'Escherichia coli',
                'type'     => "23S rRNA 3'-half"
            ),

            'haloarcula_marismotrui_23s_5prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavdXhneE56bjJhLVk/preview',
                'organism' => 'Haloarcula marismortui',
                'type'     => "23S rRNA 5'-half"
            ),

            'haloarcula_marismotrui_23s_3prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavNWRnd1NQRFFJbk0/preview',
                'organism' => 'Haloarcula marismortui',
                'type'     => "23S rRNA 3'-half"
            ),

            'deinococcus_radiodurans_23S_5prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavTnp3dkpQMFJ5Rzg/preview',
                'organism' => 'Deinococcus radiodurans',
                'type'     => "23S rRNA 5'-half"
            ),

            'deinococcus_radiodurans_23S_3prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavc2FKREhFbXpxNjQ/preview',
                'organism' => 'Deinococcus radiodurans',
                'type'     => "23S rRNA 3'-half"
            ),

            'saccharomyces_cerevisiae_26s_5prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavUzR2eEZQclZ5eUU/preview',
                'organism' => 'Saccharomyces cerevisiae',
                'type'     => "26S rRNA 5'-half"
            ),

            'saccharomyces_cerevisiae_26s_3prime' => array(
                'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavNnhxbWtuUXdxam8/preview',
                'organism' => 'Saccharomyces cerevisiae',
                'type'     => "26S rRNA 3'-half"
            ),

//             'thermus_thermophilus_16S_23S' => array(
//                 'url'      => 'https://docs.google.com/file/d/0B1RoD7V_rQavYURtdjNoWEtrbFE/preview',
//                 'organism' => 'Thermus thermophilus',
//                 'type'     => "16S and 23S rRNAs"
//             ),

        );

        if (is_null($organism)) {
            $data['selected'] = 'all';
            $data['title'] = '2Ds with RNA 3D Motifs';
        } elseif ( array_key_exists($organism, $data['all']) ) {
            $data['selected'] = $data['all'][$organism];
            $data['title'] = $data['all'][$organism]['organism'] . ' ' . $data['all'][$organism]['type'];
        } else {
            echo ($organism);
            show_404();
        }

        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motifs_secondary_structure_view', $data);
        $this->load->view('footer');

    }

}

/* End of file motifs.php */
/* Location: ./application/controllers/motifs.php */