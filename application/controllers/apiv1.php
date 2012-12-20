<?php
class Apiv1 extends CI_Controller {

	public function index()
	{
        show_404();
	}

    public function look_up_unit_id($old_id)
    {
        $this->load->model('Unitid_model', '', TRUE);
        echo $this->Unitid_model->look_up_unit_id($old_id);
    }

    public function get_all_rna_pdb_ids()
    {
        $this->load->model('Pdb_model', '', TRUE);
        $pdbs = $this->Pdb_model->get_all_pdbs();

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('pdb_ids' => $pdbs)));
    }

    public function get_structure_info($pdb_id)
    {
        $this->load->model('Pdb_model', '', TRUE);
        $data = $this->Pdb_model->get_general_info($pdb_id);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($data));
    }

    public function get_equivalent_structures($pdb_id)
    {
        $this->load->model('Pdb_model', '', TRUE);
        $data = $this->Pdb_model->get_related_structures($pdb_id);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('related_pdbs'   => $data['related_pdbs'],
                                            'representative' => $data['representative'],
                                            'eq_class'       => $data['eq_class'])));
    }

    public function is_valid_pdb($pdb_id)
    {
        $this->load->model('Pdb_model', '', TRUE);
        $isValid = $this->Pdb_model->pdb_exists($pdb_id);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('valid' => $isValid)));
    }

    public function validate_nts()
    {
        if ( $this->input->post('pdb') ) {
            $pdb_id = $this->input->post('pdb');
        } elseif ( $this->input->post('PDBquery') ) {
            $pdb_id = $this->input->post('PDBquery');
        } else {
            $pdb_id = NULL;
        }

        if ( $this->input->post('chain') ) {
            $chain = $this->input->post('chain');
        } elseif ( $this->input->post('ch') ) {
            $chain = $this->input->post('ch');
        } else {
            $chain = NULL;
        }

        if ( $this->input->post('nts') ) {
            $nts = $this->input->post('nts');
        } elseif ( $this->input->post('nucleotides') ) {
            $nts = $this->input->post('nucleotides');
        } else {
            $nts = NULL; // all?
        }

        // chain is optional
        $this->load->model('Apiv1_model', '', TRUE);
        $result = $this->Apiv1_model->validate_nts($pdb_id, $chain, $nts);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($result));

    }

}

/* End of file pdb.php */
/* Location: ./application/controllers/pdb.php */