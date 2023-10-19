<?php
class Pdb extends CI_Controller {

	public function index()
	{
        // $this->output->cache(262974); # 6 months

        $this->load->model('Pdb_model', '', TRUE);
        $data['pdbs'] = $this->Pdb_model->get_all_pdbs();
        $data['recent'] = $this->Pdb_model->get_recent_rna_containing_structures(7);
        $data['title'] = 'RNA Structure Atlas';
        $data['pageicon'] = base_url() . 'icons/S_icon.png';
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('pdb_view', $data);
        $this->load->view('footer');
	}

    public function general_info($id)
    {
        // main pdb landing page with general info and links to specific pages

        // $this->output->cache(262974); # 6 months

        $this->load->model('Pdb_model', '', TRUE);
        $pdb_status = $this->is_valid_pdb($id, 'il');
        $data['valid'] = $pdb_status['valid'];
        $data['pdbs'] = $this->Pdb_model->get_all_pdbs();
        if ( $pdb_status['valid'] ) {
            $data = array_merge($data, $this->Pdb_model->get_general_info($id));
            $data = array_merge($data, $this->Pdb_model->get_nrlist_info($id));
            $data = array_merge($data, $this->Pdb_model->get_loops_info($id));
            $data = array_merge($data, $this->Pdb_model->get_related_structures($id));
            $data['il_counts'] = $this->Pdb_model->get_motifs_info($id, 'IL');
            $data['hl_counts'] = $this->Pdb_model->get_motifs_info($id, 'HL');
            $data['bp_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_lwbp');
            $data['bst_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_stacks');
            $data['bph_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_bphs');
            $data['brb_counts'] = $this->Pdb_model->get_pairwise_info($id, 'f_brbs');
            $data['baa_counts'] = $this->Pdb_model->get_baseaa_info($id);
        } else {
            $data['message'] = $pdb_status['message'];
        }

        $data['title'] = $id . ' Summary';
        $data['pageicon'] = base_url() . 'icons/S_icon.png';
        $data['baseurl'] = base_url();
        $data['method'] = 'fr3d';
        $data['pdb_id'] = $id;
        $this->load->view('header_view', $data);
        $this->load->view('pdb_summary_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('footer');

//         $this->output->enable_profiler(TRUE);
    }

    public function interactions($id, $method, $interaction_type, $format=NULL)
    {
        // when caching is enabled, headers are not sent correctly
        // and the download functionality is compromised

        //print $id;
        //print $method;
        //print $interaction_type;

        // validate inputs
        $interaction_types = array('basepairs', 'stacking', 'basephosphate', 'baseribose', 'baseaa', 'all');
        if ( !preg_match('/fr3d/i', $method) or array_search($interaction_type, $interaction_types) === false ) {
            show_404();
        }

        //print $interaction_type;

        // detect download requests
        if ( !is_null($format) ) {
            if ( $format != 'csv' ) { // Only csv format is supported for now
                show_404();
            }
            $is_download = true;
            $filename = "{$id}_{$method}_{$interaction_type}.csv";
            $this->output->set_header("Access-Control-Allow-Origin: *")
                         ->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin")
                         ->set_header("Content-disposition: attachment; filename=$filename")
                         ->set_content_type('text/csv');
        } else {
            $is_download = false;
        }

        $this->load->model('Pdb_model', '', TRUE);

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
            $result = $this->Pdb_model->get_interactions($id, $interaction_type);
            // if there are pairwise interactions in the structure
            if ( $result['data'] != '' ) {
                $tmpl = array( 'table_open'  => '<table class="bordered-table zebra-striped span8">' );
                $this->table->set_template($tmpl);
                $this->table->set_heading($result['header']);
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
        if ( $is_download ) {
            $data['csv'] = $result['csv'];
            $this->load->view('csv_view', $data);
        } else {
            $data['title'] = strtoupper($id) . ' ' . $interaction_type;
            $data['pageicon'] = base_url() . 'icons/S_icon.png';
            $data['interaction_type'] = $interaction_type;
            $data['method'] = $method;
            $data['pdb_id'] = $id;
            $data['baseurl'] = base_url();
            $data['current_url'] = current_url();
            $this->load->view('header_view', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('pdb_interactions_view', $data);
            $this->load->view('footer');
        }
    }

	public function motifs($id)
	{
        // $this->output->cache(262974); # 6 months

	    $this->load->model('Pdb_model', '', TRUE);
        // check the pdb id
        $pdb_status = $this->is_valid_pdb($id, 'il');
        $data['valid'] = $pdb_status['valid'];

        if ( $pdb_status['valid'] ) {
            $data['pdbs'] = $this->Pdb_model->get_all_pdbs();
            $results = $this->Pdb_model->get_loops($id);
            $loop_types = array('IL', 'HL', 'J3');
            foreach ($loop_types as $loop_type) {
                // valid loops
                $tmpl = array(
                    'table_open'  => '<table class="condensed-table bordered-table">',
                    'cell_start' => '<td style ="white-space: nowrap">',
                    'cell_end' => '</td>'
                );
                $this->table->set_template($tmpl);
                $this->table->set_heading('#', 'loop id', 'location', 'direct info', 'mapped info');
                if (count($results['valid'][$loop_type]) > 0) {
                    $data['loops'][$loop_type]['valid'] = $this->table->generate($results['valid'][$loop_type]);
                } else {
                    $data['loops'][$loop_type]['valid'] = '<p>No loops found</p>';
                }

                // problematic loops
                $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
                $this->table->set_template($tmpl);
                $this->table->set_heading('#', 'loop id', 'Problem', 'Sequence');
                if (count($results['invalid'][$loop_type]) > 0) {
                    $data['loops'][$loop_type]['invalid'] = $this->table->generate($results['invalid'][$loop_type]);
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
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('pdb_loops_view', $data);
        $this->load->view('footer');
	}

    public function two_d($pdb_id)
    {
	    $this->load->model('Pdb_model', '', TRUE);
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
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view($view, $data);
        $this->load->view('footer');
    }

    private function is_valid_pdb($pdb_id, $interaction_type=NULL )
    {
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
