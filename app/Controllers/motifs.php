<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\motifs_model;
use App\Models\motif_model;

use Config\Services;

class Motifs extends BaseController {

	public function index()
	{
        # $this->cachePage(262974); # 6 months; should not be cached, easy queries, often updated
        $model = model(motifs_model::class);

        $data['featured'] = $model->get_featured_motifs('il');
        $data['featured'] = array_merge($data['featured'], $model->get_featured_motifs('hl'));
        $data = array_merge($data, $model->get_current_release_info());

        $data['title']   = 'RNA 3D Motif Atlas';
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl'] = base_url();

        return view('header_view', $data)
             . view('motifs_home_view', $data)
             . view('menu_view', $data)
             . view('footer');
	}

    public function polymorphs($motif_type, $release_id)
    {
        // $this->cachePage(262974); # 6 months

        $model = model(motifs_model::class);
        $table = $model->get_polymorphs($motif_type, $release_id);

        $table = new \CodeIgniter\View\Table();

        $table->setHeading('Sequence', 'Length', '# of motifs', 'Motifs');
        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        $table->setTemplate($tmpl);
        $data['table'] = $table->generate($table);

        $data['title']   = 'Polymorphic motifs';
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl'] = base_url();

        return view('header_view', $data)
        . view('menu_view', $data)
        . view('motifs_polymorphs_view', $data)
        . view('footer');
    }

	public function release($motif_type, $release_id, $format=NULL)	{
        // This code generates the page for each HL, IL, J3, ... release
        // It also handles download requests

        $motifs_model = model(motifs_model::class);

        $motif_type = strtolower($motif_type);
        if ($release_id == 'current') {
            $release_id = $motifs_model->get_latest_release($motif_type);
        }

        if (!is_null($format)) {
            // download request
            if ( $format != 'csv' and $format != 'json' and $format != 'annotations') {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Unknown download format");
            }

            if ($format == 'annotations') {
                // retrieve the motif group ids and loop ids and annotations all at once
                $annotations = $motifs_model->get_annotations($release_id, $motif_type);

                $filename = "{$motif_type}_{$release_id}_annotations.tsv";
                // use double quotes below so \n is not a string literal.  Crazy.
                $data = implode("\n", $annotations);
                // bypass the view system to avoid debugging comments
                $response = service('response');
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                        ->setHeader('Access-Control-Allow-Origin', '*')
                        ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                        ->setContentType('text/tab-separated-values');
                $response->setBody($data);
                return $response;
            } else {
                // get all motif ids in that release
                $motifs = $motifs_model->get_all_motifs($release_id, $motif_type);

                // if $motifs is empty, return nothing
                if (empty($motifs)) {
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Motif atlas release not found");
                }

                $motif_model = model(motif_model::class);
                $download = array();

                foreach ($motifs as $i=>$motif_id) {
                    // use the most recent release of each motif group
                    $motif_model->release_id = $motif_model->get_latest_release_for_motif($motif_id);
                    $motif_model->motif_id = $motif_id;

                    if ($format == 'csv') {
                        $download[] = ">{$motif_id}\n" . $motif_model->get_csv($motif_id,$release_id);
                    } else if ($format == 'json') {
                        $download[] = $motif_model->get_json($motif_id,$release_id);
                    }
                }

                if ($format == 'csv') {
                    $filename = "{$motif_type}_{$release_id}.csv";
                    $data = implode('', $download);
                    // bypass the view system to avoid debugging comments
                    $response = service('response');
                    $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                            ->setHeader('Access-Control-Allow-Origin', '*')
                            ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                            ->setContentType('text/csv');
                    $response->setBody($data);
                    return $response;
                } else {
                    $filename = "{$motif_type}_{$release_id}.json";
                    $data = '[' . implode(",\n", $download) . ']';
                    // bypass the view system to avoid debugging comments
                    $response = service('response');
                    $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                            ->setHeader('Access-Control-Allow-Origin', '*')
                            ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                            ->setContentType('application/json');
                    $response->setBody($data);
                    return $response;
                }
            }
        }

        // $this->cachePage(262974); # 6 months

        // not a download request, show a web page for all motif groups of this type in this release
        // get all the data to display in a table for all motif groups of this type in this release
        $result = $motifs_model->get_release($motif_type,$release_id);

        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        // $table->setTemplate($tmpl);
        // $table->setHeading('#', 'Varna 2D', 'Motif', 'Core Nts', 'Instances');

        // $table = Services::table();
        $table = new \CodeIgniter\View\Table($tmpl);

        // $table->setTemplate($tmpl);
        $table->setHeading('#', 'Varna 2D', 'Motif', 'Core Nts', 'Instances');
        $data['table']    = $table->generate($result['table']);

        $data['status']   = $motifs_model->get_release_status($motif_type,$release_id);

        if ($data['status'] == 'error') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Release " . $motif_type . " " . $release_id . " not found");
        }
        $data['counts']   = $result['counts'];
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        if ( $motif_type == 'il' ) {
            $data['title']    = 'Internal Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'hl' ) {
            $data['title']    = 'Hairpin Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j3' ) {
            $data['title']    = '3-way Junction Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j4' ) {
            $data['title']    = '4-way Junction Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j5' ) {
            $data['title']    = '5-way Junction Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j6' ) {
            $data['title']    = '6-way Junction Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j7' ) {
            $data['title']    = '7-way Junction Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j8' ) {
            $data['title']    = '8-way Junction Loop Motif Atlas Release ' . $release_id;
        } else if ( $motif_type == 'j9' ) {
            $data['title']    = '9-way Junction Loop Motif Atlas Release ' . $release_id;
        }
        $data['baseurl']  = base_url();
        $data['alt_view'] = base_url(array('motifs','graph',$motif_type,$release_id));
        $data['polymorph_url'] = base_url(array('motifs','polymorphs',$motif_type, $release_id));
        $data['current_url'] = current_url();
        $data['meta']['description'] = 'A list of RNA 3D motifs';

        return view('header_view', $data)
            . view('menu_view', $data)
            . view('motifs_view', $data)
            . view('footer');
	}

	public function releaseinfo($motif_type,$id)
	{
        // $this->cachePage(262974); # 6 months

	    $motif_type = strtolower($motif_type);
	    // $this->load->model('Motifs_model', '', TRUE);
        $model = model(motifs_model::class);
        if ($id == 'current') {
            $id = $model->get_latest_release($motif_type);
        }
        $result = $model->get_release_advanced($motif_type,$id);

        $table = new \CodeIgniter\View\Table();

        $tmpl = array( 'table_open'  => '<table class="zebra-striped condensed-table bordered-table" id="sort">' );
        $table->setTemplate($tmpl);
        $table->setHeading('#', 'Varna 2D', 'Motif id', 'Status', 'Instances', 'Name', 'Min length', 'Max', 'Diff');
        $data['table']    = $table->generate($result['table']);

        $data['status']   = $model->get_release_status($motif_type,$id);
        $data['counts']   = $result['counts'];
        $data['title']    = 'Motif Atlas Release ' . $id;
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl']  = base_url();
        $data['alt_view'] = base_url(array('motifs','graph',$motif_type,$id));
        $data['polymorph_url'] = base_url(array('motifs','polymorphs',$motif_type, $id));
        $data['meta']['description'] = 'A list of RNA 3D motifs';

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('motifs_info_view', $data)
             . view('footer');

	}

    public function graph($motif_type,$id)
    {
        // $this->cachePage(262974); # 6 months

	    $motif_type = strtolower($motif_type);
	    // $this->load->model('Motifs_model', '', TRUE);
        $model = model(motifs_model::class);

        $data['graphml'] = $model->get_graphml($motif_type,$id);

        $data['img_loc']   = strtoupper($motif_type) . $id;
        $data['alt_view']  = base_url(array('motifs','release',$motif_type,$id));
        $data['title']     = 'Motif Atlas Release ' . $id . ' (' . strtoupper($motif_type) . ')';
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl']   = base_url();

        if ($data['graphml'] == '') {
            return view('header_view', $data)
            . view('menu_view', $data)
            . view('motifs_graph_not_exists_view', $data)
            . view('footer');
        } else {
            return view('header_view', $data)
            . view('menu_view', $data)
            . view('motifs_graph_view', $data)
            . view('footer');
        }
    }

    public function compare_releases()
    {
        // $this->load->model('Motifs_model', '', TRUE);
        $model = model(motifs_model::class);

        $result = $model->get_compare_radio_table();

        $result['IL'] = $table->make_columns($result['IL'], 2);

        $table = new \CodeIgniter\View\Table();

        $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
        $table->setTemplate($tmpl);
        $table->setHeading('Release 1', 'Release 2');
        $data['table']['ils'] = $table->generate($result['IL']);

        $result['HL'] = $table->make_columns($result['HL'], 2);
        $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
        $table->setTemplate($tmpl);
        $table->setHeading('Release 1', 'Release 2');
        $data['table']['hls'] = $table->generate($result['HL']);

        $data['title']      = 'Compare Motif Atlas Releases';
        $data['pageicon']   = base_url() . 'icons/M_icon.png';
        $data['baseurl']    = base_url();
        $data['action_il']  = base_url('motifs/compare/il');
        $data['action_hl']  = base_url('motifs/compare/hl');

        return view('header_view', $data)
        . view('menu_view', $data)
        . view('motifs_compare_view', $data)
        . view('footer');
    }

	public function compare($motif_type, $rel1 = NULL, $rel2 = NULL)
	{
	    $motif_type = strtolower($motif_type);
        // $model = model(motifs_model::class);
        $model = model(motifs_model::class);

        if ($rel1 == NULL and $rel2 == NULL) {
            $rel1 = $this->input->post('release1');
            $rel2 = $this->input->post('release2');
        }

        // order the releases
        list($rel1, $rel2) = $model->order_releases($rel1, $rel2, $motif_type);

        // compare PDB files between two releases
        $pdbs1 = $model->get_pdb_files_from_motif_release($motif_type, $rel1);
        $pdbs2 = $model->get_pdb_files_from_motif_release($motif_type, $rel2);

        $data['pdbs1'] = count($pdbs1);
        $data['pdbs2'] = count($pdbs2);

        $data['pdbs_identical'] = $model->get_identical_pdbs($pdbs1, $pdbs2);
        // pdbs_replaced, pdbs_added
        $data = array_merge($data, $model->get_new_and_replaced_pdbs($pdbs1, $pdbs2));
        $data['pdbs_removed']   = $model->get_removed_pdbs($pdbs1, $pdbs2);

        // get identical motifs table
        $data['same_groups'] = $this->_get_motif_summary_table($rel1, $rel2, $motif_type, 'same_groups');

        // get updated motifs table
        $data['updated_groups'] = $this->_get_motif_summary_table($rel1, $rel2, $motif_type, 'updated_groups');

        // get added motifs table
        $data['added_groups'] = $this->_get_motif_summary_table($rel1, $rel2, $motif_type, 'added_groups');

        // get removed motifs table
        $data['removed_groups'] = $this->_get_motif_summary_table($rel1, $rel2, $motif_type, 'removed_groups');

        // get counts for identical, updated, added, and removed groups
        $data = array_merge($data, $model->get_release_difference_summary($rel1, $rel2, $motif_type));

        // get motif counts
        $data['num_motifs_release1'] = $model->get_motif_counts($rel1, $motif_type);
        $data['num_motifs_release2'] = $model->get_motif_counts($rel2, $motif_type);

        $data['rel1'] = $rel1;
        $data['rel2'] = $rel2;
        $data['title'] = "{$rel1} | {$rel2}";
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['motif_type'] = $motif_type;
        $data['baseurl'] = base_url();
        return view('header_view', $data)
        . view('menu_view', $data)
        . view('motifs_compare_results_view', $data)
        . view('footer');
	}

    private function _get_motif_summary_table($rel1, $rel2, $motif_type, $target)
    {
        $table = new \CodeIgniter\View\Table();
        $table->setHeading('#','Motif id','Instances','Description');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table sortable'" );
        $table->setTemplate($tmpl);
        $model = model(motifs_model::class);

        return $table->generate($model->get_motif_summary_table($rel1, $rel2, $motif_type, $target));
    }

    public function release_history()
    {
        // $this->cachePage(10000); # number of minutes to cache; 1 week

        $model = model(motifs_model::class);
        $result = $model->get_complete_release_history();

        $table = new \CodeIgniter\View\Table();

        $table->setHeading('Release','Added groups','Removed groups',
                                  'Updated groups','Added loops','Removed loops',
                                  'All loops', 'Motifs', 'Date', 'Description');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped' id='sort'>" );
        $table->setTemplate($tmpl);
        $data['table_il'] = $table->generate($result['IL']);

        $table = new \CodeIgniter\View\Table();

        $table->setHeading('Release','Added groups','Removed groups',
                                  'Updated groups','Added loops','Removed loops',
                                  'All loops', 'Motifs', 'Date', 'Description');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped' id='sort'>" );
        $table->setTemplate($tmpl);
        $data['table_hl'] = $table->generate($result['HL']);

        $data['title'] = 'Release History';
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl'] = base_url();

        return view('header_view', $data)
        . view('menu_view', $data)
        . view('motifs_history_view', $data)
        . view('footer');
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($organism);
        }

        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl'] = base_url();

        return view('header_view', $data)
        . view('menu_view', $data)
        . view('motifs_secondary_structure_view', $data)
        . view('footer');
    }

}

/* End of file motifs.php */
/* Location: ./application/controllers/motifs.php */
