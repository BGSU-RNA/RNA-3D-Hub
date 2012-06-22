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
        $data['motif_id']   = $motif_id;
        $data['author'] = $this->session->userdata('username');

        // history widget
        $this->benchmark->mark('d');
//         $history = $this->Motif_model->get_history($motif_id);
// //        $this->table->set_heading($this->Motif_model->get_history_header());
//         $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
//         $this->table->set_template($tmpl);
//         $data['history'] = $this->table->generate($history);
//         $data['history'] = array();
        $data['baseurl'] = base_url();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('motif_view', $data);
        $this->load->view('footer');

//         $this->output->enable_profiler(TRUE);
	}
}

/* End of file motif.php */
/* Location: ./application/controllers/motif.php */