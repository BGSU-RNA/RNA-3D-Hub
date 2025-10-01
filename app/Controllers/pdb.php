<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\pdb_model;
class Pdb extends BaseController {

    public $Pdb_model;

    function __construct() {
        $this->Pdb_model = new pdb_model();
    }

	public function index() {
        // $this->cachePage(262974); # 6 months

        $data['pdbs'] = $this->Pdb_model->get_all_pdbs();
        $data['recent'] = $this->Pdb_model->get_recent_rna_containing_structures(18);
        $data['title'] = 'RNA Structure Atlas';
        $data['pageicon'] = base_url() . 'icons/S_icon.png';
        $data['baseurl'] = base_url();
        return view('header_view', $data)
                . view('menu_view', $data)
                . view('pdb_view', $data)
                . view('footer');
	}

    public function data() {
    // dictionary from pdb id to selected data
        $data = json_encode($this->Pdb_model->get_all_pdbs_data());
        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                 ->setContentType('application/json');
        $response->setBody($data);
        return $response;
    }

    public function general_info($id) {
        // main landing page for a single PDB structure with general info and links to specific pages

        // $this->cachePage(262974); # 6 months

        $id = explode('|',$id)[0];

        $pdb_status = $this->is_valid_pdb($id, 'il');
        $data['valid'] = $pdb_status['valid'];
        $data['pdbs'] = $this->Pdb_model->get_all_pdbs();
        if ( $pdb_status['valid'] ) {
            $data = array_merge($data, $this->Pdb_model->get_general_info($id));
            $data = array_merge($data, $this->Pdb_model->get_nrlist_info($id));
            $data = array_merge($data, $this->Pdb_model->get_loops_info($id));
            $data = array_merge($data, $this->Pdb_model->get_related_structures($id));
            $data['hl_counts'] = $this->Pdb_model->get_motifs_info($id, 'HL');
            $data['il_counts'] = $this->Pdb_model->get_motifs_info($id, 'IL');
            $data['j_counts'] = $this->Pdb_model->get_motifs_info($id, 'J');
            $data['bp_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_lwbp');
            $data['bst_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_stacks');
            $data['bph_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_bphs');
            $data['brb_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_brbs');
            $data['baa_counts'] = $this->Pdb_model->get_baseaa_info($id);
        } else {
            $data['message'] = $pdb_status['message'];
            $data['title'] = '';
        }

        // Page title and structure title were both using $data['title']!
        $data['structure_title'] = $data['title'];
        $data['title'] = "$id summary";
        $data['pageicon'] = base_url() . 'icons/S_icon.png';
        $data['baseurl'] = base_url();
        $data['method'] = 'fr3d';
        $data['pdb_id'] = $id;

        return view('header_view', $data)
                . view('menu_view', $data)
                . view('pdb_summary_view', $data)
                . view('footer');

//         $this->output->enable_profiler(TRUE);
    }

    public function interactions($id, $method="fr3d", $interaction_type="basepairs", $format=NULL) {
        // strip off model and chain if present
        $id = explode('|',$id)[0];

        // validate inputs
        $interaction_types = array('basepairs', 'basepair_detail', 'stacking', 'basephosphate', 'baseribose', 'baseaa', 'oxygen_stacking', 'sugar_ribose', 'all', 'ligand');
        if (!preg_match('/fr3d/i', $method) && !preg_match('/matlab/i', $method)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Unknown annotation method");
        }
        if (array_search($interaction_type, $interaction_types) === false ) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Unknown interaction type");
        }

        // detect download requests
        if (!is_null($format)) {
            if ( $format != 'csv' && $format != 'tsv') { // csv and tsv
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Unknown download format");
            }
            $is_download = true;
            $filename = "{$id}_{$method}_{$interaction_type}.{$format}";
        } else {
            $is_download = false;
        }

        // check the pdb id
        $pdb_status = $this->is_valid_pdb($id, 'interactions');
        if ( !$pdb_status['valid'] ) {
            if ( $is_download ) {
                echo $pdb_status['message'];
                return;
            } else {
                $data['table'] = $pdb_status['message'];
            }
        }

        // generate interactions
        if ( $pdb_status['valid'] ) {
            if ($method == 'matlab' && $interaction_type == 'basepair_detail') {
                $interaction_type = 'basepairs';
            }
            $result = $this->Pdb_model->get_interactions($id, $interaction_type, $method, $format);
            // if there are pairwise interactions in the structure
            if ( $result['count'] > 0 ) {
                $tmpl = array( 'table_open'  => '<table class="bordered-table zebra-striped span8">' );
                $tmpl = array('table_open' => '<table id="filterable-table" class="bordered-table zebra-striped span8">');
                $table = new \CodeIgniter\View\Table();
                $table->setTemplate($tmpl);
                $table->setHeading($result['header']);
                $data['table'] = $result['data'];
            } else {
                $message = 'No interactions of this type found';
                if ( $is_download ) {
                    echo $message;
                    return;
                } else {
                    $data['table'] = $message;
                }
            }
        }

        // send out the results
        if ($is_download && $format == 'csv') {
            $data['csv'] = $result['csv'];
            // bypass the view system to avoid debugging comments
            $response = service('response');
            $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                     ->setHeader('Access-Control-Allow-Origin', '*')
                     ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                     ->setContentType('text/csv');
            $response->setBody($data['csv']);
            return $response;
        } elseif ($is_download && $format == 'tsv') {
            $data['tsv'] = $result['tsv'];
            // bypass the view system to avoid debugging comments
            $response = service('response');
            $response->setHeader('Content-Type', 'text/tab-separated-values; charset=utf-8')
                     ->setHeader('Content-Disposition', "attachment; filename={$filename}")
                     ->setHeader('Access-Control-Allow-Origin', '*')
                     ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin');
            $response->setBody($data['tsv']);
            return $response;
        } else {
            $data['title'] = strtoupper($id) . ' ' . $interaction_type;
            $data['pageicon'] = base_url() . 'icons/S_icon.png';
            $data['interaction_type'] = $interaction_type;
            $data['method'] = $method;
            $data['pdb_id'] = $id;
            $data['baseurl'] = base_url();
            $data['current_url'] = current_url();
            return view('header_view', $data)
                . view('menu_view', $data)
                . view('pdb_interactions_view', $data)
                . view('footer');
        }
    }

	public function motifs($id) {
        // $this->cachePage(262974); # 6 months

        // strip off model and chain if present
        $id = explode('|',$id)[0];

        // check the pdb id
        $pdb_status = $this->is_valid_pdb($id, 'il');
        $data['valid'] = $pdb_status['valid'];

        // We no longer have a "Choose a structure" dropdown, so don't get the data; faster
        // $data['pdbs'] = $this->Pdb_model->get_all_pdbs();

        if ( $pdb_status['valid'] ) {
            $results = $this->Pdb_model->get_loops($id);
            $loop_types = array('IL', 'HL', 'J');
            foreach ($loop_types as $loop_type) {
                // valid loops
                $tmpl = array(
                    'table_open'  => '<table class="condensed-table bordered-table">',
                    'cell_start' => '<td style ="overflow-wrap: normal">',
                    'cell_end' => '</td>'
                );
                $table = new \CodeIgniter\View\Table($tmpl);
                $table->setHeading(
                    array('data' => '3D', 'style' => 'width: 8%'),
                    array('data' => 'Loop id', 'style' => 'width: 24%'),
                    array('data' => 'Location', 'style' => 'width: 20%'),
                    array('data' => 'Annotation; motif group', 'style' => 'width: 24%'),
                    array('data' => 'Mapped info', 'style' => 'width: 24%')); // test
                if (count($results['valid'][$loop_type]) > 0) {
                    $data['loops'][$loop_type]['valid'] = $table->generate($results['valid'][$loop_type]);
                } else {
                    $data['loops'][$loop_type]['valid'] = '<p>No loops found</p>';
                }

                // problematic loops
                $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
                $table = new \CodeIgniter\View\Table();
                $table->setTemplate($tmpl);
                $table->setHeading('#', 'loop id', 'Problem', 'Sequence');
                if (count($results['invalid'][$loop_type]) > 0) {
                    $data['loops'][$loop_type]['invalid'] = $table->generate($results['invalid'][$loop_type]);
                } else {
                    $data['loops'][$loop_type]['invalid'] = '<p>No problematic loops</p>';
                }

                $data['counts'][$loop_type] = count($results['valid'][$loop_type]) + count($results['invalid'][$loop_type]);
            }
        } else {
            $data['message'] = $pdb_status['message'];
        }
        $data['title'] = "$id | Motifs";
        $data['pageicon'] = base_url() . 'icons/S_icon.png';
        $data['baseurl'] = base_url();
        $data['method'] = 'fr3d';
        $data['pdb_id'] = $id;
        return view('header_view', $data)
                . view('menu_view', $data)
                . view('pdb_loops_view', $data)
                . view('footer');
	}

    public function two_d($pdb_id) {
        // strip off model and chain if present
        $id = explode('|',$pdb_id)[0];

        $pdb_status = $this->is_valid_pdb($pdb_id, 'il');
        $data['pdbs'] = $this->Pdb_model->get_all_pdbs();
        $data['valid'] = $pdb_status['valid'];
        $data['title'] = "$pdb_id | 2D representation";
        $data['pageicon'] = base_url() . 'icons/S_icon.png';
        $data['baseurl'] = base_url();
        $data['method'] = 'fr3d';
        $data['pdb_id'] = $pdb_id;
        $data['sub_heading'] = "2D representation";
        $data['has_airport'] = FALSE;
        $view = 'pdb_2d_view';

        $data['related_pdbs'] = array();
        $related = $this->Pdb_model->get_related_structures($pdb_id);
        $related = $related['related_pdbs'];
        foreach($related as $pdb) {
            if (strcasecmp($pdb_id, $pdb)) {
                $data['related_pdbs'][] = $pdb;
            }
        };

        if ( $pdb_status['valid'] ) {
            // Only a few airport diagrams work, and we need to link to R2DT diagrams instead
            // Comment out this code, and remove the Airport and Circular buttons
            //$nts = $this->Pdb_model->get_airport($pdb_id);
            //if ($nts) {
            //    $data['has_airport'] = TRUE;
            //    $data['nts'] = $nts;
            //} else {
            //    $data['nts'] = json_encode(array_values($this->Pdb_model->get_ordered_nts($pdb_id)));
            //}
            $data['long_range'] = json_encode($this->Pdb_model->get_longrange_bp($pdb_id));
            $data['nts'] = json_encode(array_values($this->Pdb_model->get_ordered_nts($pdb_id)));
        } else {
            $data['message'] = $pdb_status['message'];
            $view = 'pdb_invalid_view';
        }
        return view('header_view', $data)
                . view('menu_view', $data)
                . view($view, $data)
                . view('footer');
    }

    private function is_valid_pdb($pdb_id, $interaction_type=NULL ) {
        $messages = array( 'invalid_id'      => "Not a valid PDB id.",
                           'not_annotated'   => "This structure has not been annotated with pairwise interactions or does not contain RNA. Please check back later.");
        $message = '';
        $valid = False;
        // report invalid pdb file
        if ( !$this->Pdb_model->pdb_exists($pdb_id) ) {
            $message = $messages['invalid_id'];
        } else {
            if ( is_null($interaction_type) ) {
                $valid = True;
            } else {
                // check if annotated
                if ( !$this->Pdb_model->pdb_is_annotated($pdb_id, $interaction_type) ) {
                    $message = $messages['not_annotated'];
                } else {
                    $valid = True;
                }
            }
        }
        return array( 'message' => $message, 'valid' => $valid );
    }
}

/* End of file pdb.php */
/* Location: ./application/controllers/pdb.php */
