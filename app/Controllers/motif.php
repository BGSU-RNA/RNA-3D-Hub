<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\motif_model;


class Motif extends BaseController {

    // public function __construct()
    // {
    //     parent::__construct();
    // }

    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function save_annotation()
    {
        $params = array(
            'column'   => $this->input->post('id'),
            'value'    => $this->input->post('value'),
            'motif_id' => $this->input->post('motif_id'),
            'author'   => $this->input->post('author')
        );

        $this->load->model('Motif_model', '', TRUE);
        echo $model->save_annotation($params);
    }

// 	public function view($motif_id, $format=NULL)
// 	{
//         // $this->output->cache(2*7*24*60); # cache for 2 weeks

// 	    // $this->load->model('Motif_model', '', TRUE);
//         $model = model(motif_model::class);

// 	    if ( !$model->is_valid_motif_id($motif_id) ) {
// 	        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This motif id is invalid.");
// 	    }

// 	    $model->set_motif_id($motif_id);
// 	    $release_id = $model->set_first_release_id();
//         $last_release_id = $model->set_release_id();

//         // handle download requests
//         if ( !is_null($format) ) {
//             if ( $format == 'csv' ) {
//                 $filename = "{$motif_id}.csv";
//                 $data['csv'] = $model->get_csv($motif_id,$last_release_id);
//                 // bypass the view system to avoid debugging comments
//                 $response = service('response');
//                 $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
//                         ->setHeader('Access-Control-Allow-Origin', '*')
//                         ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
//                         ->setContentType('text/csv');
//                 $response->setBody($data['csv']);
//                 return $response;
//             } elseif ( $format == 'json' ) {
//                 $filename = "{$motif_id}.json";
//                 $data['csv'] = $model->get_json($motif_id,$last_release_id);
//                 // bypass the view system to avoid debugging comments
//                 $response = service('response');
//                 $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
//                         ->setHeader('Access-Control-Allow-Origin', '*')
//                         ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
//                         ->setContentType('application/json');
//                 $response->setBody($data['csv']);
//                 return $response;
//             } else {
//                 throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Download request failed. Try different motifs.");
//             }
//         }

//         // pairwise interactions table
// 	    $table_array = $model->get_interaction_table();
//         $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table zebra-striped pairwise-interactions" id="sort">' );
//         $table = new \CodeIgniter\View\Table($tmpl);
//         $table->setHeading($model->header);
//         $data['table'] = $table->generate($table_array);

//         //annotations
//         $data['annotation'] = $model->get_annotations($motif_id);
//         $data['annotation_test'] = $model->get_annotations_count();

//         // NEW mutual discrepancy matrix widget
//         // $this->benchmark->mark('c');
//         if ( $model->num_loops > 1 ) {
//             $data['discrepancy_matrix'] = $model->get_mutual_discrepancy_matrix_efficient();
//         } else {
//             $data['discrepancy_matrix'] = '';
//         }

//         // sequence variability
//         $seq_var = $model->get_sequence_variants($motif_id);
//         $tmpl = array( 'table_open'  => '<table class="condensed-table zebra-striped" id="complete_seq_var">' );
//         $table = new \CodeIgniter\View\Table($tmpl);
//         $table->setHeading('Sequence', 'Counts');
//         $data['sequence_variation']['complete'] = $table->generate($seq_var['complete']);

//         $tmpl = array( 'table_open'  => '<table class="condensed-table zebra-striped" id="nwc_seq_var">' );
//         $table = new \CodeIgniter\View\Table($tmpl);
//         $table->setHeading('Sequence', 'Counts');
//         $data['sequence_variation']['nwc'] = $table->generate($seq_var['nwc']);

//         $data['title']      = $motif_id;
//         $data['pageicon'] = base_url() . 'icons/M_icon.png';
//         $data['release_id'] = $release_id;
//         $data['last_release_id'] = $last_release_id;
//         $data['release_id_label'] = $model->is_current_motif($motif_id);
//         $data['motif_id']   = $motif_id;
//         // $data['author'] = $this->session->userdata('username');
//         $data['author'] = $this->session->get('username');

//         // linkage
//         $data = array_merge($data, $model->get_linkage_data($motif_id));

//         // history widget
//         $motif_release_history = $model->get_motif_release_history($motif_id);
//         $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
//         $table = new \CodeIgniter\View\Table($tmpl);
//         $data['motif_release_history'] = $table->generate($motif_release_history);

//         $history_tables = $model->get_history($motif_id);
//         if ( count($history_tables['parents']) > 0 ) {
//             $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
//             $table = new \CodeIgniter\View\Table($tmpl);
//             $table->setHeading('Parent motif','Common motif instances',"Only in $motif_id",'Only in the parent motif');
//             $data['history']['parents'] = $table->generate($history_tables['parents']);
//         } else {
//             $data['history']['parents'] = 'This motif has no parent motifs.';
//         }
//         if ( count($history_tables['children']) > 0 ) {
//             $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
//             $table = new \CodeIgniter\View\Table($tmpl);
//             $table->setHeading('Child motif','Common motif instances',"Only in $motif_id",'Only in the child motif');
//             $data['history']['children'] = $table->generate($history_tables['children']);
//         } else {
//             $data['history']['children'] = 'This motif has no children motifs.';
//         }

//         $data['current_url'] = current_url();
//         $data['baseurl'] = base_url();

//         return view('header_view', $data)
//                 . view('menu_view', $data)
//                 . view('motif_view', $data)
//                 . view('footer');

// //         $this->output->enable_profiler(TRUE);
// 	}

    public function compare($motif1, $motif2)
    {
        $this->load->model('Motif_model', '', TRUE);

        $tmpl = array( 'table_open'  => '<table class="condensed-table">' ,
                       'class' => 'mdmatrix-table');
        $table->setTemplate($tmpl);

        // use make_columns to avoid generation of th tags
        $compare = $model->compare_motifs($motif1, $motif2);
        $data['matrix'] = $table->generate(
                                                 $this->table->make_columns($compare['table'], $compare['columns'])
                                                );
        $data['motif1'] = $motif1;
        $data['motif2'] = $motif2;
        $data['title']  = "$motif1 vs. $motif2";
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['baseurl'] = base_url();
        return view('header_view', $data)
             . view('menu_view', $data)
             . view('motif_compare_view', $data)
             . view('footer');
    }

	public function view($motif_id, $format=NULL) {
        // $this->output->cache(2*7*24*60); # cache for 2 weeks

	    // $this->load->model('Motif_model', '', TRUE);
        $model = model(motif_model::class);

	    if ( !$model->is_valid_motif_id($motif_id) ) {
	        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This motif id is invalid.");
	    }

	    $model->set_motif_id($motif_id);
	    $release_id = $model->set_first_release_id();
        $last_release_id = $model->set_release_id();

        // handle download requests
        if ( !is_null($format) ) {
            if ( $format == 'csv' ) {
                $filename = "{$motif_id}.csv";
                $data['csv'] = $model->get_csv($motif_id,$last_release_id);
                // bypass the view system to avoid debugging comments
                $response = service('response');
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                        ->setHeader('Access-Control-Allow-Origin', '*')
                        ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                        ->setContentType('text/csv');
                $response->setBody($data['csv']);
                return $response;
            } elseif ( $format == 'json' ) {
                $filename = "{$motif_id}.json";
                $data['csv'] = $model->get_json($motif_id,$last_release_id);
                // bypass the view system to avoid debugging comments
                $response = service('response');
                $response->setHeader('Content-Disposition', "attachment; filename={$filename}")
                        ->setHeader('Access-Control-Allow-Origin', '*')
                        ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                        ->setContentType('application/json');
                $response->setBody($data['csv']);
                return $response;
            } else {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Download request failed. Try different motifs.");
            }
        }

        // get the data for the motif group page table
        $table_array = $model->get_interaction_table();
        if (count($table_array) == 0) {
            return 'No alignment available for this motif group; it seems that alignment data has been lost.  However, membership of this group can be found by downloading annotations for the motif atlas release of interest.';
        }

        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table zebra-striped pairwise-interactions" id="sort">' );
        $table = new \CodeIgniter\View\Table($tmpl);
        $table->setHeading($model->header);
        $data['table'] = $table->generate($table_array);

        // annotations, including basepair signature
        $data['annotation'] = $model->get_annotations($motif_id);
        $data['annotation_test'] = $model->get_annotations_count();

        // mutual discrepancy matrix widget
        // $this->benchmark->mark('c');
        if ( $model->num_loops > 1 ) {
            $data['discrepancy_matrix'] = $model->get_mutual_discrepancy_matrix_efficient();
        } else {
            $data['discrepancy_matrix'] = '';
        }

        // sequence variability
        $seq_var = $model->get_sequence_variants($motif_id);
        $tmpl = array( 'table_open'  => '<table class="condensed-table zebra-striped" id="complete_seq_var">' );
        $table = new \CodeIgniter\View\Table($tmpl);
        $table->setHeading('Sequence', 'Counts');
        $data['sequence_variation']['complete'] = $table->generate($seq_var['complete']);

        $table = new \CodeIgniter\View\Table($tmpl);
        $tmpl = array( 'table_open'  => '<table class="condensed-table zebra-striped" id="nwc_seq_var">' );
        $table->setHeading('Sequence', 'Counts');
        $data['sequence_variation']['nwc'] = $table->generate($seq_var['nwc']);

        $data['title']      = $motif_id;
        $data['pageicon'] = base_url() . 'icons/M_icon.png';
        $data['release_id'] = $release_id;
        $data['last_release_id'] = $last_release_id;
        $data['release_id_label'] = $model->is_current_motif($motif_id);
        $data['motif_id']   = $motif_id;
        // $data['author'] = $this->session->userdata('username');
        $data['author'] = $this->session->get('username');

        // linkage
        $data = array_merge($data, $model->get_linkage_data($motif_id));

        // history widget
        // $this->benchmark->mark('d');

        $motif_release_history = $model->get_motif_release_history($motif_id);
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $table = new \CodeIgniter\View\Table($tmpl);
        $data['motif_release_history'] = $table->generate($motif_release_history);

        $history_tables = $model->get_history($motif_id);
        if ( count($history_tables['parents']) > 0 ) {
            $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
            $table->setHeading(array('Parent motif','Common motif instances',"Only in $motif_id",'Only in the parent motif'));
            $table->setTemplate($tmpl);
            $data['history']['parents'] = $table->generate($history_tables['parents']);
        } else {
            $data['history']['parents'] = 'This motif has no parent motifs.';
        }
        if ( count($history_tables['children']) > 0 ) {
            $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
            $table->setHeading(array('Child motif','Common motif instances',"Only in $motif_id",'Only in the child motif'));
            $table->setTemplate($tmpl);
            $data['history']['children'] = $table->generate($history_tables['children']);
        } else {
            $data['history']['children'] = 'This motif has no children motifs.';
        }

        // similar motifs
        // $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        // $table->setHeading(array('#', 'Min linkage', 'Motif', '', 'Description'));
        // $table->setTemplate($tmpl);
        // $data['similar_motifs'] = $table->generate($model->get_similar_motifs($motif_id));

        $data['current_url'] = current_url();
        $data['baseurl'] = base_url();
        return view('header_view', $data)
                . view('menu_view', $data)
                . view('motif_view', $data)
                . view('footer');

//         $this->output->enable_profiler(TRUE);
	}

}
/* End of file motif.php */
/* Location: ./application/controllers/motif.php */