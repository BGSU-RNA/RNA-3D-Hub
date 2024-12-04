<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\nrlist_model;
class Nrlist extends BaseController {

    public $Nrlist_model;

    function __construct()
    {
        $this->Nrlist_model = new nrlist_model();
    }

    public function index()
    {
        // list release history.  takes a while to generate, but seems to be OK.
        // caching really helps
        // http://rna.bgsu.edu/rna3dhub/nrlist
        $this->cachePage(60*60*24*7); # 1 week, in seconds
        ini_set('max_execution_time', 300);

        // This query takes around 70 seconds, just over the time limit
        $result = $this->Nrlist_model->get_all_releases('NR');

        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
        $table = new \CodeIgniter\View\Table($tmpl);
        $table->setHeading('Release id', 'All changes', 'Date', 'Number of IFEs');
        $data['table']   = $table->generate($result);
        $data['title']   = 'Representative Sets of RNA 3D Structures';
        $data['pageicon'] = base_url() . 'icons/R_icon.png';
        $data['baseurl'] = base_url();
        $data['molecule'] = 'rna';

        // $data['images'] = $this->Nrlist_model->get_newest_pdb_images();
        $data['total_pdbs'] = $this->Nrlist_model->get_total_pdb_count();

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('nrlist_all_releases_view', $data)
             . view('footer');
    }

    // public function rna()
    // {
    //     // Default page
    //     // https://rna.bgsu.edu/rna3dhub/nrlist/rna
    //     // $this->cachePage(60*60*24*7); # 1 week, in seconds
    //     // ini_set('max_execution_time', 300); // Set to 300 seconds (5 minutes)

    //     $result = $this->Nrlist_model->get_all_releases('NR');

    //     $table = new \CodeIgniter\View\Table();
    //     $table->setHeading('Release id', 'All changes', 'Date', 'Number of IFEs');
    //     $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
    //     $table->setTemplate($tmpl);
    //     $data['table']   = $table->generate($result);
    //     $data['title']   = 'Representative Sets of RNA 3D Structures';
    //     $data['pageicon'] = base_url() . 'icons/R_icon.png';
    //     $data['baseurl'] = base_url();
    //     $data['molecule'] = 'rna';

    //     // $data['images'] = $this->Nrlist_model->get_newest_pdb_images();
    //     $data['total_pdbs'] = $this->Nrlist_model->get_total_pdb_count();

    //     return view('header_view', $data)
    //          . view('menu_view', $data)
    //          . view('nrlist_all_releases_view', $data)
    //          . view('footer');
    // }

    public function dna()
    {
        // https://rna.bgsu.edu/rna3dhub/nrlist/dna
        // List all DNA releases
        // Do not cache until there are many releases; fast to re-generate
        $this->cachePage(60*60*24*7); # 1 week, in seconds

        $result = $this->Nrlist_model->get_all_releases('DNA');

        $table = new \CodeIgniter\View\Table();
        $table->setHeading('Release id', 'All changes', 'Date', 'Number of IFEs');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
        $table->setTemplate($tmpl);
        $data['table']   = $table->generate($result);
        $data['title']   = 'Representative Sets of DNA 3D Structures';
        $data['pageicon'] = base_url() . 'icons/D_icon.png';
        $data['baseurl'] = base_url();
        $data['molecule'] = 'dna';

        // $data['images'] = $this->Nrlist_model->get_newest_pdb_images();
        $data['total_pdbs'] = $this->Nrlist_model->get_total_pdb_count();

        return view('header_view', $data)
             . view('menu_view', $data)
             . view('nrlist_dna_releases_view', $data)
             . view('footer');
    }

    public function release($arg1, $arg2='current', $arg3='4.0A')
    {
        if ( strtoupper($arg1) == 'RNA'){
            $type = 'rna';
            $type_upper = 'RNA';
            $class_type = 'NR';
            $id = $arg2;
            $res = $arg3;
        } elseif (strtoupper($arg1) == 'DNA'){
            $type = 'dna';
            $type_upper = 'DNA';
            $class_type = 'DNA';
            $id = $arg2;
            $res = $arg3;
        } else {
            $type = 'rna';
            $type_upper = 'RNA';
            $class_type = 'NR';
            $id = $arg1;
            if ($arg2 == 'current'){
                $res = '4.0A';
            } else {
                $res = $arg2;
            }
        }

        $this->cachePage(60*60*24*7); # 1 week

        if ($id == 'current') {
            $id = $this->Nrlist_model->get_latest_release($type);
            $this->cachePage(10000); # 1 week, should stay current this way
        } elseif ( !$this->Nrlist_model->is_valid_release($id) ) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['title'] = "Representative set $id";
        if ($type == 'dna'){
            $data['pageicon'] = base_url() . 'icons/D_icon.png';
        } else {
            $data['pageicon'] = base_url() . 'icons/R_icon.png';
        }
        $data['release_id']  = $id;
        $data['description'] = $this->Nrlist_model->get_release_description($id);
        $data['resolution'] = $res;
        $data['type'] = $type;
        $data['type_upper'] = $type_upper;
        $data['class_type'] = $class_type;

        $temp = $this->Nrlist_model->get_release($id, $res, $type);
        $data['counts'] = $temp['counts'];
        $table = new \CodeIgniter\View\Table();
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sort'>" );
        $table->setTemplate($tmpl);
        if ($type == 'dna'){
            $table->setHeading('#', 'Equivalence class', 'Representative', 'Resolution', 'Nts', 'Class members','NAKB NA annotation','NAKB protein annotation');
        } else {
            $table->setHeading('#', 'Equivalence class', 'Representative', 'Resolution', 'Nts', 'Class members');
        }
        $data['class'] = $table->generate($temp['table']);

        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('nrlist_release_view', $data)
             . view('footer');
    }

    // Examples for RNA:
    // http://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.348/3.0A/csv
    // http://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.348/all/csv
    public function download($arg1, $arg2, $arg3='all', $arg4='csv')
    {
        if (strtoupper($arg1) == 'RNA') {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.349/3.0A/csv
            $class_type = 'NR';
            $id = $arg2;
            $res = $arg3;
            $format = $arg4;
        } elseif (strtoupper($arg1) == 'NR') {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.349/3.0A/csv
            $class_type = 'NR';
            $id = $arg2;
            $res = $arg3;
            $format = $arg4;
        } elseif (strtoupper($arg1) == 'DNA') {
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/DNA/0.2/3.0A/csv
            $class_type = 'DNA';
            $id = $arg2;
            $res = $arg3;
            $format = $arg4;
        } elseif (strtoupper($arg1) == 'CURRENT') {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/current/2.5A/csv
            $class_type = 'NR';
            $id = 'current';
            $res = $arg2;
            $format = $arg3;
        } else {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/3.349/3.0A/csv
            $class_type = 'NR';
            $id = $arg1;
            $res = $arg2;
            $format = $arg3;
        }

        if ($id == 'current') {
            $id = $this->Nrlist_model->get_latest_release();
        } elseif ( !$this->Nrlist_model->is_valid_release($id) ) {
            echo 'Invalid release id';
            return;
        }

        if ($format == 'csv') {
            $data['csv'] = $this->Nrlist_model->get_csv($id, $res, $class_type);

            if ($class_type == 'DNA') {
                $filename = "nrlist_dna_{$id}_{$res}.{$format}";
            } else {
                $filename = "nrlist_{$id}_{$res}.{$format}";
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                     ->setContentType('text/csv');
            $response->setBody($data['csv']);
            return $response;

        } elseif ($format == 'full' || $format == 'full_csv') {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.343/2.5A/json
            $data['csv'] = $this->Nrlist_model->get_csv_full($id, $res, $class_type, 'csv');

            if ($class_type == 'DNA') {
                $filename = "ifes_dna_{$id}_{$res}_full.csv";
            } else {
                $filename = "ifes_{$id}_{$res}_full.csv";
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                        ->setContentType('text/csv');
            $response->setBody($data['csv']);
            return $response;

        } elseif ($format == 'full_tsv') {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.343/2.5A/json
            $data['csv'] = $this->Nrlist_model->get_csv_full($id, $res, $class_type, 'tsv');

            if ($class_type == 'DNA') {
                $filename = "ifes_dna_{$id}_{$res}_full.tsv";
            } else {
                $filename = "ifes_{$id}_{$res}_full.tsv";
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                        ->setContentType('text/tab-separated-values');
            $response->setBody($data['csv']);
            return $response;

        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function view($id)
    // Example:  http://rna.bgsu.edu/rna3dhub/nrlist/view/NR_4.0_06650.37
    {
        $this->cachePage(60*60*24*30); # 30 days

        if ( !$this->Nrlist_model->is_valid_class($id) ) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Unknown equivalence class: $id");
        }

        $releases = $this->Nrlist_model->get_releases_by_class($id);

        $table = new \CodeIgniter\View\Table();
        $data['releases'] = $table->generate($releases);

        list($type, $resolution, $temp) = explode('_', $id);
        list($handle, $version) = explode('.', $temp);
        $data['resolution'] = $resolution;
        $data['version']    = $version;

        $data['status'] = $this->Nrlist_model->get_status($id,$type);

        $members = $this->Nrlist_model->get_members($id);
        $tmpl = array( 'table_open'  => "<table class='condensed-table bordered-table zebra-striped' id='members_id'>" );
        $table = new \CodeIgniter\View\Table($tmpl);
        $table->setHeading('#','IFE','Standardized name', 'Molecule', 'Organism', 'Source', 'Rfam', 'Title','Method','Res.&nbsp;&Aring','Date');

        $data['members'] = $table->generate($members);
        $data['num_members'] = count($members);

        $history = $this->Nrlist_model->get_history($id,'parents');
        $table = new \CodeIgniter\View\Table();
        $table->setHeading('This class','Parent classes','Release id','Intersection','Added to this class','Only in parent');
        $data['parents'] = $table->generate($history);

        $history = $this->Nrlist_model->get_history($id,'children');
        $table = new \CodeIgniter\View\Table();
        $table->setHeading('This class           ','Descendant classes','Release id','Intersection','Only in this class','Added to child');
        $data['children'] = $table->generate($history);

        // generate the data for the heat map tab
        $statistics = $this->Nrlist_model->get_statistics($id);
        $tmpl = array( 'table_open'  => "<table class='condensed-table bordered-table zebra-striped' id='sort'>" );
        $table = new \CodeIgniter\View\Table($tmpl);
        if ($type == 'DNA'){
            $table->setHeading('#S','View','PDB','Title','Method','Resolution','Length','NAKB NA annotation','NAKB protein annotation');
        } else {
            $table->setHeading('#S','View','PDB','Title','Method','Resolution','Length');
        }
        $data['statistics'] = $table->generate($statistics);
        $revised_stat = $data['statistics'];

        $heatmap = $this->Nrlist_model->get_heatmap_data($id);

        $data['heatmap_data'] = $heatmap;

        $data['title'] = $id;
        $data['baseurl'] = base_url();
        $data['pageicon'] = base_url() . 'icons/E_icon.png';
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('nrlist_class_view', $data)
             . view('footer');
    }


    public function compare_releases()
    {
        # This is the overall page, which presents all pairs of releases to compare
        # That is a lot of possible pairs; if bots randomly request, we have trouble
        # rm /var/www/rna3dhub/application/cache/dfae0becbfbfb657ae06976147838179

        $this->cachePage(720); # 12 hours, in minutes

        // this was kind of updated, but does not put things in the correct column
        $data = $this->Nrlist_model->get_compare_radio_table();
        // $table = $table->make_columns($table, 3);
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $table = new \CodeIgniter\View\Table($tmpl);
        $table->setHeading('Release 1', 'Release 2', 'Release date');
        $data['table'] = $table->generate($data);
        $data['title'] = 'Compare releases';

        $data['baseurl'] = base_url();
        $data['pageicon'] = base_url() . 'icons/R_icon.png';
        $data['action']  = base_url('nrlist/compare');
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('nrlist_release_compare_view', $data)
             . view('footer');
    }

    public function compare($rel1 = NULL, $rel2 = NULL)
    {

        list($home1, $home2) = $this->Nrlist_model->get_two_newest_releases();

        $request = $this->request;

        // Retrieve 'release1' from POST data, with a default value of $home1 if not set
        if ($rel1 == NULL) {
            $rel1 = $request->getPost('release1') ?? $home1;
        }

        // Retrieve 'release1' from POST data, with a default value of $home1 if not set
        if ($rel2 == NULL) {
            $rel2 = $request->getPost('release2') ?? $home2;
        }

        // # If no release is specified ...
        // if ($rel1 == NULL and $rel2 == NULL) {
        //     $rel1 = ( $this->input->post('release1') ) ? $this->input->post('release1') : $home1;
        //     $rel2 = ( $this->input->post('release2') ) ? $this->input->post('release2') : $home2;
        // }

        # Only launch the query if Release 2 is the most recent release
        # That is noted on the page for human users
        # That will cut way down on bots randomly comparing releases
        if ($rel2 == $home1) {

            $data = $this->Nrlist_model->get_release_diff($rel1,$rel2);

            $data['title'] = "{$rel1} | {$rel2}";
            $data['pageicon'] = base_url() . 'icons/R_icon.png';

            $data['rel1']  = $rel1;
            $data['rel2']  = $rel2;

            $data['baseurl'] = base_url();

            #var_dump($data); ### DEBUG

            return view('header_view', $data)
                 . view('menu_view', $data)
                 . view('nrlist_release_compare_results_view', $data)
                 . view('footer');
        }
    }

    public function release_history()
    {

        // $this->cachePage(262974); # 6 months

        $tables = $this->Nrlist_model->get_complete_release_history();
        $resolutions = array('1.5','2.0','2.5','3.0','3.5','4.0','20.0','all');
        $labels      = array('1_5A','2_0A','2_5A','3_0A','3_5A','4_0A','20_0A','all');

        $i = 0;
        foreach ($resolutions as $res) {
            $table = new \CodeIgniter\View\Table();
            $table->setHeading('Release','Date','Compare parent','Added groups','Removed groups','Updated groups','Added pdbs','Removed pdbs');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped' id='{$labels[$i]}table'>" );
            $table->setTemplate($tmpl);
            $data['tables'][$labels[$i]] = $table->generate($tables[$res]);
            $i++;
        }

        $data['title'] = 'Release History';
        $data['pageicon'] = base_url() . 'icons/R_icon.png';
        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('nrlist_release_history_view', $data)
             . view('footer');
    }
}
/* End of file nrlist.php */
/* Location: ./application/controllers/nrlist.php */
