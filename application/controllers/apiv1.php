<?php
class Apiv1 extends CI_Controller {

	public function index()
	{
        show_404();
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

}

/* End of file pdb.php */
/* Location: ./application/controllers/pdb.php */