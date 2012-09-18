<?php
class Motif extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
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
        echo $this->Motif_model->save_annotation($params);
    }

	public function view($motif_id)
	{
//         $this->output->cache(10);
//         $this->output->cache(1000000);

	    $this->load->model('Motif_model', '', TRUE);
	    $this->Motif_model->set_motif_id($motif_id);
	    $release_id = $this->Motif_model->set_release_id();

        //annotations
        $data['annotation'] = $this->Motif_model->get_annotations($motif_id);

        // pairwise interactions table
        $this->benchmark->mark('a');
	    $table_array = $this->Motif_model->get_interaction_table();
        $this->load->library('table');
        $this->table->set_heading($this->Motif_model->header);
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table zebra-striped pairwise-interactions" id="sort">' );
        $this->table->set_template($tmpl);
        $data['table']      = $this->table->generate($table_array);

        // checkbox widget
//         $this->benchmark->mark('b');
//         $data['checkboxes'] = array(); //$this->Motif_model->get_checkboxes($this->Motif_model->loops);

        // mutual discrepancy matrix widget
        $this->benchmark->mark('c');
        if ( $this->Motif_model->num_loops > 1 ) {
            $matrix_linear = $this->Motif_model->get_mutual_discrepancy_matrix();
            $matrix_column = $this->table->make_columns($matrix_linear, $this->Motif_model->num_loops);
            $tmpl = array( 'table_open'  => '<table class="condensed-table">' ,
                           'class' => 'mdmatrix-table');
            $this->table->set_template($tmpl);
            $data['matrix'] = $this->table->generate($matrix_column);
        } else {
            $data['matrix'] = '';
        }

        // sequence variability
        $seq_var = $this->Motif_model->get_3d_sequence_variation($motif_id);
        $this->table->set_heading(array('Sequence', 'Counts'));
        $tmpl = array( 'table_open'  => '<table class="condensed-table zebra-striped" id="complete_seq_var">' );
        $this->table->set_template($tmpl);
        $data['sequence_variation']['complete'] = $this->table->generate($seq_var['complete']);
        $this->table->set_heading(array('Sequence', 'Counts'));
        $tmpl = array( 'table_open'  => '<table class="condensed-table zebra-striped" id="nwc_seq_var">' );
        $this->table->set_template($tmpl);
        $data['sequence_variation']['nwc'] = $this->table->generate($seq_var['nwc']);

        $data['title']      = $motif_id;
        $data['release_id'] = $release_id;
        $data['release_id_label'] = $this->Motif_model->is_current_motif($motif_id);
        $data['motif_id']   = $motif_id;
        $data['author'] = $this->session->userdata('username');

        // linkage
        $data = array_merge($data, $this->Motif_model->get_linkage_data($motif_id));

        // history widget
        $this->benchmark->mark('d');

        $motif_release_history = $this->Motif_model->get_motif_release_history($motif_id);
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $this->table->set_template($tmpl);
        $data['motif_release_history'] = $this->table->generate($motif_release_history);

        $history_tables = $this->Motif_model->get_history($motif_id);
        if ( count($history_tables['parents']) > 0 ) {
            $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
            $this->table->set_heading(array('Parent class','Common motif instances',"Only in $motif_id",'Only in the parent class'));
            $this->table->set_template($tmpl);
            $data['history']['parents'] = $this->table->generate($history_tables['parents']);
        } else {
            $data['history']['parents'] = 'This motif has no parent motifs.';
        }
        if ( count($history_tables['children']) > 0 ) {
            $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
            $this->table->set_heading(array('Child class','Common motif instances',"Only in $motif_id",'Only in the child class'));
            $this->table->set_template($tmpl);
            $data['history']['children'] = $this->table->generate($history_tables['children']);
        } else {
            $data['history']['children'] = 'This motif has no children motifs.';
        }

        // similar motifs
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $this->table->set_heading(array('#', 'Min linkage', 'Motif', ''));
        $this->table->set_template($tmpl);
        $data['similar_motifs'] = $this->table->generate($this->Motif_model->get_similar_motifs($motif_id));

        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motif_view', $data);
        $this->load->view('footer');

//         $this->output->enable_profiler(TRUE);
	}

    public function compare($motif1, $motif2)
    {
        $this->load->model('Motif_model', '', TRUE);

        $tmpl = array( 'table_open'  => '<table class="condensed-table">' ,
                       'class' => 'mdmatrix-table');
        $this->table->set_template($tmpl);

        // use make_columns to avoid generation of th tags
        $compare = $this->Motif_model->compare_motifs($motif1, $motif2);
        $data['matrix'] = $this->table->generate(
                                                 $this->table->make_columns($compare['table'], $compare['columns'])
                                                );
        $data['motif1'] = $motif1;
        $data['motif2'] = $motif2;
        $data['title']  = "$motif1 vs. $motif2";
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motif_compare_view', $data);
        $this->load->view('footer');
    }

}
/* End of file motif.php */
/* Location: ./application/controllers/motif.php */