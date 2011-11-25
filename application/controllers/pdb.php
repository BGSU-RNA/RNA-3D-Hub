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
        $data['title'] = 'All RNA-containing 3D structures';
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
        $ils['valid']    = $this->Pdb_model->get_all_valid_loops($id, 'IL');
        $ils['modified'] = $this->Pdb_model->get_all_modified_loops($id, 'IL');
        $ils['missing']  = $this->Pdb_model->get_all_missing_nts_loops($id, 'IL');

        $hls['valid']    = $this->Pdb_model->get_all_valid_loops($id, 'HL');
        $hls['modified'] = $this->Pdb_model->get_all_modified_loops($id, 'HL');
        $hls['missing']  = $this->Pdb_model->get_all_missing_nts_loops($id, 'HL');

        $jls['valid']    = $this->Pdb_model->get_all_valid_loops($id,'J3');
        $jls['modified'] = $this->Pdb_model->get_all_modified_loops($id, 'J3');
        $jls['missing']  = $this->Pdb_model->get_all_missing_nts_loops($id, 'J3');

        $data['title'] = 'PDB ' . $id;
        // internal loops
        list($data['ils']['valid'],$data['ils']['valid_count']) =
              $this->generate_table_block($ils['valid'], array('Loop id', 'Sequence'));
        list($data['ils']['modified'],$data['ils']['modified_count']) =
              $this->generate_table_block($ils['modified'], array('Loop id', 'Modification'));
        list($data['ils']['missing'],$data['ils']['missing_count']) =
              $this->generate_table_block($ils['missing'], array('Loop id', 'Sequence'));

        // hairpin loops
        list($data['hls']['valid'],$data['hls']['valid_count']) =
              $this->generate_table_block($hls['valid'], array('Loop id', 'Sequence'));
        list($data['hls']['modified'],$data['hls']['modified_count']) =
              $this->generate_table_block($hls['modified'], array('Loop id', 'Modification'));
        list($data['hls']['missing'],$data['hls']['missing_count']) =
              $this->generate_table_block($hls['missing'], array('Loop id', 'Sequence'));

        // j3 loops
        list($data['jls']['valid'],$data['jls']['valid_count']) =
              $this->generate_table_block($jls['valid'], array('Loop id', 'Sequence'));
        list($data['jls']['modified'],$data['jls']['modified_count']) =
              $this->generate_table_block($jls['modified'], array('Loop id', 'Modification'));
        list($data['jls']['missing'],$data['jls']['missing_count']) =
              $this->generate_table_block($jls['missing'], array('Loop id', 'Sequence'));


        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('pdb_loops_view', $data);
        $this->load->view('footer');
	}

    function generate_table_block($input,$header)
    {
        if (count($input) > 0) {
            $this->table->set_heading($header);
            $tmpl = array( 'table_open'  => '<table class="condensed-table">' );
            $this->table->set_template($tmpl);
            $num    = count($input);
            $output = $this->table->generate($input);
        } else {
            $num = 0;
            $output = '<p>No loops found</p>';
        }
        return array($output, $num);
    }

}

/* End of file pdb.php */
/* Location: ./application/controllers/pdb.php */