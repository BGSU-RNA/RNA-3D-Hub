<?php
class Release extends CI_Controller {

	public function index()
	{
        $this->load->helper('url');
	    $this->load->model('Release_model', '', TRUE);
        $result = $this->Release_model->get_all_releases();

        $this->table->set_heading('Release id', 'Date', 'Description', 'Loops', 'Motifs');
        $data['table'] = $this->table->generate($result);
        $data['title'] = 'All Motif Atlas releases';
        $data['baseurl'] = base_url();

        $this->load->view('headerview', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('releaseview', $data);
        $this->load->view('footer');
	}

	public function view($id)
	{
	    $this->load->model('Release_model', '', TRUE);
        $result = $this->Release_model->get_release($id);

        $tmpl = array( 'table_open'  => '<table class="zebra-striped">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('Motif id', 'Instances');
        $data['table'] = $this->table->generate($result);
        $data['title'] = 'Release ' . $id;

        $data['baseurl'] = base_url();
        $this->load->view('headerview', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('releaseview', $data);
        $this->load->view('footer');
	}

	public function compare()
	{
	    $rel1 = $this->input->post('release1');
	    $rel2 = $this->input->post('release2');
	    if ($rel1 and $rel2) {
//             $this->output->cache(10);
	        $this->load->model('Release_history_model', '' , TRUE);
	        $this->Release_history_model->set_releases($rel1, $rel2);
	        $data = $this->Release_history_model->get_summary();
 	        $data['title'] = "{$rel1} | {$rel2}";
	        $data['rel1']  = $rel1;
	        $data['rel2']  = $rel2;
 	        $data['ul_intersection'] = ul($data['intersection']);
 	        $data['ul_updated']      = ul($data['updated']);
	        $data['ul_only_in_1']    = ul($data['diff']['only_in_1']);
	        $data['ul_only_in_2']    = ul($data['diff']['only_in_2']);
	        $data['ul_loops_intersection'] = ul($data['loops']['intersection']);
	        $data['ul_loops_only_in_1']    = ul($data['loops']['only_in_1']);
	        $data['ul_loops_only_in_2']    = ul($data['loops']['only_in_2']);

            $data['baseurl'] = base_url();
            $this->load->view('headerview', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('release_compare_results_view', $data);
            $this->load->view('footer');
	    }
	    else {
            $this->load->model('Release_model', '', TRUE);
            $table = $this->Release_model->get_compare_radio_table();
            $table = $this->table->make_columns($table, 2);
            $this->table->set_heading('Release 1', 'Release 2');
            $data['table'] = $this->table->generate($table);
            $data['title'] = 'Compare releases';

            $data['baseurl'] = base_url();
            $this->load->view('headerview', $data);
            $this->load->view('menu_view', $data);
            $this->load->view('release_compare_view', $data);
            $this->load->view('footer');
        }
	}

}

/* End of file release.php */
/* Location: ./application/controllers/release.php */