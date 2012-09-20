<?php
class Nrlist extends CI_Controller {

	public function index()
	{
        $this->output->cache(8640); # 6 days

	    $this->load->model('Nrlist_model', '', TRUE);
        $result = $this->Nrlist_model->get_all_releases();

        $this->table->set_heading('Release id', 'All changes', 'Date', 'NR PDB files');
        $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
        $this->table->set_template($tmpl);
        $data['table']   = $this->table->generate($result);
        $data['title']   = 'All Non-redundant List Releases';
        $data['baseurl'] = base_url();

        $data['images'] = $this->Nrlist_model->get_newest_pdb_images();
        $data['total_pdbs'] = $this->Nrlist_model->get_total_pdb_count();

        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('nrlist_all_releases_view', $data);
        $this->load->view('footer');
	}

	public function release($id)
	{
        $this->output->cache(8640); # 6 days

	    $this->load->model('Nrlist_model', '', TRUE);
        if ($id == 'current') {
            $id = $this->Nrlist_model->get_latest_release();
        }

	    $data['title']       = "NR list $id";
        $data['release_id']  = $id;
        $data['description'] = $this->Nrlist_model->get_release_description($id);

        $resolution = array('1.5','2.0','2.5','3.0','3.5','4.0','20.0','all');
        foreach ($resolution as $res) {
            $temp = $this->Nrlist_model->get_release($id,$res);
            $data['counts'][$res] = $temp['counts'];
            $table_id = str_replace('.','_',$res) . 'Atable';
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table' id='{$table_id}'>" );
            $this->table->set_template($tmpl);
            $this->table->set_heading('#', 'Equivalence class', 'Status', 'PDB', 'Title', 'Resolution', 'Source', 'Represents');
            $data['class'][$res] = $this->table->generate($temp['table']);
        }

        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('nrlist_release_view', $data);
        $this->load->view('footer');
//         $this->output->enable_profiler(TRUE);
	}

	public function view($id)
	{
        $this->output->cache(8640); # 6 days

	    $this->load->model('Nrlist_model', '', TRUE);
	    $data['title'] = $id;

	    $releases = $this->Nrlist_model->get_releases_by_class($id);
        $tmpl = array( 'table_open'  => "<table class='bordered-table'>" );
        $this->table->set_template($tmpl);
        $data['releases'] = $this->table->generate($releases);

        list($type, $resolution, $temp) = split('_', $id);
        list($handle, $version) = split('.', $temp);
	    $data['resolution'] = $resolution;
	    $data['version']    = $version;

        $data['status'] = $this->Nrlist_model->get_status($id);

        $members = $this->Nrlist_model->get_members($id);
        $tmpl = array( 'table_open'  => "<table class='zebra-striped bordered-table' id='members_table'>" );
        $this->table->set_template($tmpl);
        $this->table->set_heading('#','PDB','Title','Source','Compounds','Method','Resolution','Date');
        $data['members'] = $this->table->generate($members);
        $data['num_members'] = count($members);

        $history = $this->Nrlist_model->get_history($id,'parents');
        $this->table->set_heading('This class','Parent classes','Release id','Intersection','Added to this class','Only in parent');
        $data['parents'] = $this->table->generate($history);

        $history = $this->Nrlist_model->get_history($id,'children');
        $this->table->set_heading('This class','Descendant classes','Release id','Intersection','Only in this class','Added to child');
        $data['children'] = $this->table->generate($history);

        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('nrlist_class_view', $data);
        $this->load->view('footer');
	}

    public function compare_releases()
    {
        $this->load->model('Nrlist_model', '', TRUE);
        $table = $this->Nrlist_model->get_compare_radio_table();
        $table = $this->table->make_columns($table, 3);
        $this->table->set_heading('Release 1', 'Release 2', 'Release date');
        $tmpl = array( 'table_open'  => '<table class="condensed-table bordered-table">' );
        $this->table->set_template($tmpl);
        $data['table'] = $this->table->generate($table);
        $data['title'] = 'Compare releases';

        $data['baseurl'] = base_url();
        $data['action']  = base_url('nrlist/compare');
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('nrlist_release_compare_view', $data);
        $this->load->view('footer');
    }

	public function compare($rel1 = NULL, $rel2 = NULL)
	{
        if ($rel1 == NULL and $rel2 == NULL) {
            $rel1 = $this->input->post('release1');
            $rel2 = $this->input->post('release2');
        }

        $this->load->model('Nrlist_model', '' , TRUE);
        $data = $this->Nrlist_model->get_release_diff($rel1,$rel2);

        $data['title'] = "{$rel1} | {$rel2}";
        $data['rel1']  = $rel1;
        $data['rel2']  = $rel2;

        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('nrlist_release_compare_results_view', $data);
        $this->load->view('footer');
	}

    public function release_history()
    {
        $this->load->model('Nrlist_model', '' , TRUE);
        $tables = $this->Nrlist_model->get_complete_release_history();
        $resolutions = array('1.5','2.0','2.5','3.0','3.5','4.0','20.0','all');
        $labels      = array('1_5A','2_0A','2_5A','3_0A','3_5A','4_0A','20_0A','all');

        $i = 0;
        foreach ($resolutions as $res) {
            $this->table->set_heading('Release','Date','Added groups','Removed groups','Updated groups','Added pdbs','Removed pdbs');
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped' id='{$labels[$i]}table'>" );
            $this->table->set_template($tmpl);
            $data['tables'][$labels[$i]] = $this->table->generate($tables[$res]);
            $i++;
        }

        $data['title'] = 'Release History';
        $data['baseurl'] = base_url();
        $this->load->view('header_view', $data);
        $this->load->view('menu_view', $data);
        $this->load->view('nrlist_release_history_view', $data);
        $this->load->view('footer');
    }


}

/* End of file nrlist.php */
/* Location: ./application/controllers/nrlist.php */