<?php
class Pdb extends CI_Controller {

	public function index()
	{
	    $this->load->model('Pdb_model', '', TRUE);
        $pdbs = $this->Pdb_model->get_all_pdbs();

        $this->load->library('table');
        $list = $this->table->make_columns($pdbs, 16);
        $tmpl = array( 'table_open'  => '<table class="bordered-table">' );
        $this->table->set_template($tmpl);
        $data['table'] = $this->table->generate($list);
        $data['title'] = 'All RNA-containing 3D structures with 3D motifs';
        $data['pdb_count'] = count($pdbs);

        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('pdb_view', $data);
        $this->load->view('footer');
	}

	public function loops($id)
	{
//         $this->output->cache(1000000);
	    $this->load->model('Pdb_model', '', TRUE);

        $results = $this->Pdb_model->get_loops($id);
        $loop_types = array('IL', 'HL', 'J3');
        foreach ($loop_types as $loop_type) {
            $data['loops'][$loop_type]['valid']   = $this->generate_table($results['valid'][$loop_type]);
            $data['loops'][$loop_type]['invalid'] = $this->generate_table($results['invalid'][$loop_type]);
        }
        $data['title'] = $id;
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('pdb_loops_view', $data);
        $this->load->view('footer');
	}

    function generate_table($loops)
    {
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $this->table->set_template($tmpl);
        $this->table->set_heading('#','id');
        if (count($loops) > 0) {
            return $this->table->generate($loops);
        } else {
            return '<p>No loops found</p>';
        }
    }

}

/* End of file pdb.php */
/* Location: ./application/controllers/pdb.php */