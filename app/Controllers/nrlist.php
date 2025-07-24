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

    public function release($arg1, $arg2='current', $arg3='4.0A') {
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
    // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/csv
    // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/3.0A/tsv/full
    // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/json
    // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.348/3.0A/csv
    // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.348/all/csv
    public function download($arg1, $arg2, $arg3='all', $arg4='csv', $scope='') {
        if (strtoupper($arg1) == 'RNA') {
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/RNA/3.349/3.0A/csv
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
        } elseif (strtoupper($arg1) == 'PREVIOUS') {
            // http://rna.bgsu.edu/rna3dhub/nrlist/download/previous/2.5A/csv
            $class_type = 'NR';
            $id = 'previous';
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
        } elseif ($id == 'previous') {
            $id = $this->Nrlist_model->get_previous_release();
        } elseif ( !$this->Nrlist_model->is_valid_release($id) ) {
            echo 'Invalid release id';
            return;
        }

        // improve the appearance of the URL, replacing full_csv with csv/full
        if ($scope == 'full') {
            $format = 'full_' . $format;
        }

        if ($format == 'full') {
            $format = 'full_csv';
        }

        if ($format == 'csv' || $format == 'tsv' || $format == 'json') {
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/csv
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/tsv
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/json
            $data = $this->Nrlist_model->get_class_rep_members($id, $res, $class_type, $format);

            if ($class_type == 'DNA') {
                $filename = "nrlist_dna_{$id}_{$res}.{$format}";
            } else {
                $filename = "nrlist_{$id}_{$res}.{$format}";
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            if ($format == 'csv') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/csv');
            } elseif ($format == 'tsv') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/tab-separated-values');
            } elseif ($format == 'json') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/csv');
            }
            $response->setBody($data);
            return $response;

        } elseif ($format == 'full_csv' || $format == 'full_tsv' || $format == 'full_json') {
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/csv/full
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/tsv/full
            // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.388/2.5A/json/full
            $format = str_replace("full_","",$format);
            $data = $this->Nrlist_model->get_release_full($id, $res, $class_type, $format);

            if ($class_type == 'DNA') {
                $filename = "ifes_dna_{$id}_{$res}_full.{$format}";
            } else {
                $filename = "ifes_{$id}_{$res}_full.{$format}";
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            if ($format == 'csv') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/csv');
            } elseif ($format == 'tsv') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/tab-separated-values');
            } elseif ($format == 'json') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('application/json');
            }
            $response->setBody($data);
            return $response;
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function nonredundant($arg1='rna', $id='current', $res='4.0A', $criterion='clan', $count_limit='1', $format='html') {
        // non-redundant lists based on rfam clan
        // $arg1 is rna or dna, but only rna is supported now
        // $arg2 is release like 3.387 or current or previous
        // $arg3 is resolution cutoff like 3.5A
        // $arg4 is the grouping criterion like clan, clan_domain
        // $arg5 is the number from each group to take
        // $arg6 is 'csv' or 'tsv' or 'json' or blank for human readable html
        // https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/current/2.5A/clan/3/csv
        // https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/current/2.5A/clan/3/tsv
        // https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/current/2.5A/clan/3/json
        // https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/current/2.5A/clan/3/

        $type = strtoupper($arg1);
        if ($type == 'RNA') {
            $class_type = 'NR';
        } elseif ($type == 'DNA') {
            $class_type = 'DNA';
        } else {
            echo 'Invalid molecule type';
            return;
        }

        if ($criterion != 'clan' && $criterion != 'clan_domain') {
            echo 'Invalid Rfam scope; use clan or clan_domain';
            return;
        }

        if (strtolower($id) == 'current') {
            $id = $this->Nrlist_model->get_latest_release();
        } elseif (strtolower($id) == 'previous') {
            $id = $this->Nrlist_model->get_previous_release();
        } elseif ( !$this->Nrlist_model->is_valid_release($id) ) {
            echo 'Invalid release id';
            return;
        }

        // https://rna.bgsu.edu/rna3dhub/nrlist/download/NR/3.386/2.5A/
        $all_output = $this->Nrlist_model->get_non_redundant($class_type, $id, $res, $criterion, $count_limit);

        if ($class_type == 'DNA') {
            $filename = "nonredundant_dna_{$id}_{$res}_{$criterion}_{$count_limit}.{$format}";
        } else {
            $filename = "nonredundant_rna_{$id}_{$res}_{$criterion}_{$count_limit}.{$format}";
        }

        // change the format to what was requested
        $header = $all_output[0];
        $nc = count($header);
        if ($format == 'csv' || $format == 'tsv') {
            $all_lines = array();
            foreach ($all_output as $row) {
                // leave off the sorting column, the last column
                $all_lines[] = $this->Nrlist_model->format_line(array_slice($row,0,$nc),$format);
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            if ($format == 'csv') {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/csv');
            } else {
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                ->setContentType('text/tab-separated-values');
            }
            $response->setBody(implode("",$all_lines));
            return $response;

        } elseif ($format == 'json') {
            $json_data = array();
            foreach (array_slice($all_output,1) as $line) {
                $json_row = array();
                foreach ($line as $index => $entry) {
                    if ($index < $nc) {
                        $json_row[$header[$index]] = $entry;
                    }
                }
                $json_data[] = $json_row;
            }

            // bypass the view system to avoid debugging comments
            $response = service('response');
            $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                        ->setContentType('application/json');
            $response->setBody(json_encode($json_data));
            return $response;

        } elseif ($format == 'html') {

            $data['title'] = "Non-redundant set based on release $id";
            if ($class_type == 'DNA'){
                $data['pageicon'] = base_url() . 'icons/D_icon.png';
                $all_output = array();
            } else {
                $data['pageicon'] = base_url() . 'icons/R_icon.png';
                $data_header = array('ife_id', 'pdb_resolution', 'pdb_experimental_technique', 'rfam', 'clan_or_rfam', 'standardized_name', 'pdb_species', 'source', 'nts_observed', 'clan_cqs2', 'pdb_release_date');
                $html_header = array('#','IFE','Resolution','Method','Rfam family','Clan or Rfam','Standardized name','Organism','Source','#NTs','Clan CQS2','Date');
            }

            $header_index = array();
            foreach ($header as $index => $htext) {
                $header_index[$htext] = $index;
            }

            $tax_url = 'https://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=';
            $rfam_url = 'https://rfam.org/family/';
            $clan_url = 'https://rfam.org/clan/';

            $html_output = array();
            foreach (array_slice($all_output,1) as $index => $row) {
                $html_row = array($index+1);
                foreach ($data_header as $field) {
                    if ($field == 'ife_id') {
                        $html_row[] = "<a class='pdb'>" . $row[$header_index[$field]] . "</a>";
                    } elseif ($field == 'pdb_species') {
                        $tid = $row[$header_index['pdb_taxid']];
                        $sid = $row[$header_index['pdb_species']];
                        $html_row[] = anchor_popup("$tax_url$tid", "$sid");
                    } elseif ($field == 'rfam') {
                        $r = array();
                        foreach (explode('+',$row[$header_index['rfam']]) as $rfam) {
                            $r[] = anchor_popup("$rfam_url$rfam", "$rfam");
                        }
                        $html_row[] = implode('+',$r);
                    } elseif ($field == 'clan_or_rfam') {
                        $r = array();
                        foreach (explode(',',$row[$header_index[$field]]) as $rfam) {
                            if ($rfam[0] == 'R') {
                                $r[] = anchor_popup("$rfam_url$rfam", "$rfam");
                            } else {
                                $r[] = anchor_popup("$clan_url$rfam", "$rfam");
                            }
                        }
                        $html_row[] = implode(',',$r);
                    } elseif ($field == 'clan_cqs2') {
                        $html_row[] = sprintf('%.4f', $row[$header_index[$field]]);
                    } else {
                        $html_row[] = $row[$header_index[$field]];
                    }
                }
                $html_output[] = $html_row;
            }

            $data['release_id']  = $id;
            $data['description'] = $this->Nrlist_model->get_release_description($id);
            $data['resolution'] = $res;
            $data['type'] = $type;
            $data['type_upper'] = strtoupper($type);
            $data['class_type'] = $class_type;
            $data['criterion'] = $criterion;
            $data['count_limit'] = $count_limit;

            $table = new \CodeIgniter\View\Table();
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='sort'>" );
            $table->setTemplate($tmpl);
            $table->setHeading($html_header);
            $data['class'] = $table->generate($html_output);

            $data['baseurl'] = base_url();
            return view('header_view', $data)
                 . view('menu_view', $data)
                 . view('release_nonredundant_view', $data)
                 . view('footer');
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function view($id)
    // View an equivalence class
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
        $table->setHeading('#','IFE','Standardized name', 'Molecule', 'Organism', 'Source', 'Rfam', 'Title','Method','Res.&nbsp;&Aring','#NTs','Date');

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
            $table->setHeading('#S','View','PDB','Title','Method','Resolution','#NTs','NAKB NA annotation','NAKB protein annotation');
        } else {
            $table->setHeading('#S','View','PDB','Title','Method','Resolution','#NTs');
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
