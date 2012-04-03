<?php
class Motif extends CI_Controller {

	public function index()
	{
        echo "motif";
	}

	public function view($motif_id)
	{
//         $this->output->cache(10);
        $this->output->cache(1000000);
	    $this->load->model('Motif_model', '', TRUE);
	    $this->Motif_model->set_motif_id($motif_id);
	    $release_id = $this->Motif_model->set_release_id();

        // pairwise interactions table
        $this->benchmark->mark('a');
	    $table_array = $this->Motif_model->get_interaction_table();
        $this->load->library('table');
        $this->table->set_heading($this->Motif_model->header);
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table zebra-striped" id="sort">' );
        $this->table->set_template($tmpl);
        $data['table']      = $this->table->generate($table_array);

        // checkbox widget
        $this->benchmark->mark('b');
        $data['checkboxes'] = $this->Motif_model->get_checkboxes($this->Motif_model->loops);

        // mutual discrepancy matrix widget
        $this->benchmark->mark('c');
        if ( $this->Motif_model->num_loops > 1 ) {
            $matrix_linear = $this->Motif_model->get_mutual_discrepancy_matrix();
            $matrix_column = $this->table->make_columns($matrix_linear, $this->Motif_model->num_loops);
            $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
            $this->table->set_template($tmpl);
            $data['matrix'] = $this->table->generate($matrix_column);
        } else {
            $data['matrix'] = '';
        }

        $data['title']      = 'Motif ' . $motif_id;
        $data['release_id'] = $release_id;
        $data['motif_id']   = $motif_id;

        // history widget
        $this->benchmark->mark('d');
        $history = $this->Motif_model->get_history($motif_id);
//        $this->table->set_heading($this->Motif_model->get_history_header());
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $this->table->set_template($tmpl);
        $data['history'] = $this->table->generate($history);
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