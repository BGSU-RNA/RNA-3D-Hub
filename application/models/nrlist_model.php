<?php

ini_set("memory_limit","300M");

function add_url($n)
{
    return anchor(base_url(array('nrlist','view',$n)), $n);
}

class Nrlist_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->helper('html');
        $CI->load->helper('form');
        $this->last_seen_in    = '';
        $this->first_seen_in   = '';
        $this->current_release = '';
        $this->tax_url = 'http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=';
        // Call the Model constructor
        parent::__construct();
        //$this->load->database();

    }

    function count_motifs($rel)
    {
        $this->db->select('resolution, count(nr_class_id) as ids')
                 ->from('nr_classes')
                 ->where('nr_release_id', $rel)
                 ->group_by('resolution');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $counts[$row->resolution] = $row->ids;
        }
        return $counts;
    }

    function get_release_diff($rel1, $rel2)
    {
        $labels = array('1.5'=>'1_5A','2.0'=>'2_0A','2.5'=>'2_5A','3.0'=>'3_0A','3.5'=>'3_5A','4.0'=>'4_0A','20.0'=>'20_0A','all'=>'all');
        $attributes = array('class' => 'unstyled');

        $counts1 = $this->count_motifs($rel1);
        $counts2 = $this->count_motifs($rel2);

        $sql = "CALL nr_release_diff(?,?)";
        $par = array($rel1, $rel2);

        $query = $this->db->query($sql, $par);

        if ($query->num_rows == 0) {
            $par = array($rel2,$rel1);
            $query = $this->db->query($sql, $par);
        }

        foreach ($query->result() as $row) {
            $data['uls'][$labels[$row->resolution]]['num_motifs1'] = $counts1[$row->resolution];
            $data['uls'][$labels[$row->resolution]]['num_motifs2'] = $counts2[$row->resolution];

            if ($row->num_same_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_intersection'] = ul(array_map("add_url", explode(', ',$row->same_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_intersection'] = '';
            }

            if ($row->num_updated_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_updated'] = ul(array_map("add_url", explode(', ',$row->updated_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_updated'] = '';
            }

            if ($row->num_added_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_1'] = ul(array_map("add_url", explode(', ',$row->added_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_1'] = '';
            }

            if ($row->num_removed_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_2'] = ul(array_map("add_url", explode(', ',$row->removed_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_2'] = '';
            }

            $data['uls'][$labels[$row->resolution]]['num_intersection'] = $row->num_same_groups;
            $data['uls'][$labels[$row->resolution]]['num_updated']      = $row->num_updated_groups;
            $data['uls'][$labels[$row->resolution]]['num_only_in_1']    = $row->num_added_groups;
            $data['uls'][$labels[$row->resolution]]['num_only_in_2']    = $row->num_removed_groups;
        }

        $query->free_result();
        unset($query);

        return $data;
    }

    function get_releases_by_class($id)
    {
        /* TODO:  Can this safely reduce to just nr_class_id and description? */
        $this->db
                 #->select('nrc.nr_class_id')
                 #->select('nrc.name')
                 ->select('nrc.nr_release_id')
                 #->select('nrc.resolution')
                 #->select('nrc.handle')
                 #->select('nrc.version')
                 #->select('nrc.comment')
                 /*->select('nrr.nr_release_id')*/
                 #->select('nrr.date')
                 ->select('nrr.description')
                 ->from('nr_classes AS nrc')
                 ->join('nr_releases AS nrr','nrc.nr_release_id = nrr.nr_release_id')
                 ->where('nrc.name',$id)
                 ->order_by('nrr.date');
        $query = $this->db->get();
        $releases[0][0] = 'Release';
        $releases[1][0] = 'Date';
        $i = 0;
        foreach ($query->result() as $row) {
            if ($i==0) {
                $this->first_seen_in = $row->nr_release_id;
                $i++;
            }
            $releases[0][] = anchor(base_url("nrlist/release/".$row->nr_release_id), $row->nr_release_id);
            $releases[1][] = $this->beautify_description_date($row->description);
        }
        $this->last_seen_in = $row->nr_release_id;
        return $releases;
    }

    function get_status($id)
    {
        /* TODO:  Reduce to nr_release_id? */
        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index','desc')
                 ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $current_release = $row->nr_release_id;
        }

        $this->current_release = $current_release;

        /* TODO:  Reduce to one column? */
        $this->db->select('nr_class_id')
                 ->select('name')
                 ->select('nr_release_id')
                 ->select('resolution')
                 ->select('handle')
                 ->select('version')
                 ->select('comment')
                 ->from('nr_classes')
                 ->where('name',$id)
                 ->where('nr_release_id',$current_release);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return 'Current';
        } else {
            return 'Obsolete';
        }
    }

    function make_pdb_widget_link($pdb)
    {
        return "<span class='rcsb_image' title='{$pdb}|asr|xsmall|'></span><a class='pdb'>$pdb</a>";
    }

    function get_source_organism($ife_id)
    {
        if ( 1 == substr_count($ife_id, '|') ) {
            list($pdb_id, $chain) = explode('|', $ife_id);
        } else {
            list($pdb_id, $model_num, $chain) = explode('|', $ife_id);
        }

        $this->db->select('source, taxonomy_id')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb_id)
                 ->where('chain_name', $chain)
                 ->like('entity_macromolecule_type', 'RNA');
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $result = $query->result();
            $tid = $result[0]->taxonomy_id;
            $sid = $result[0]->source;

            if ( $tid != '' ) {
                return anchor_popup("$this->tax_url$tid", "$sid");
            } else {
                return $sid;
            }
        } else {
            return '';
        }
    }

    function get_members($id)
    {

        $this->db->select('pi.pdb_id')
                 ->select('ch.ife_id')
                 ->select('pi.title')
                 ->select('pi.experimental_technique')
                 ->select('pi.release_date')
                 ->select('pi.resolution')
                 ->from('pdb_info AS pi')
                 ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                 ->join('nr_chains AS ch', 'ii.ife_id = ch.ife_id')
                 ->join('nr_classes AS cl', 'ch.nr_class_id = cl.nr_class_id AND ch.nr_release_id = cl.nr_release_id')
                 ->where('cl.name',$id)
                 #->where('nch.nr_release_id',$this->last_seen_in) # what was this doing? still necessary?
                 ->group_by('pi.pdb_id')
                 ->group_by('ii.ife_id')
                 ->order_by('ch.rep','desc');
        $query = $this->db->get();

        $i = 0;
        $table = array();

        foreach ($query->result() as $row) {
            $link = $this->make_pdb_widget_link($row->ife_id);

            if ( $i==0 ) {
                $link = $link . ' <strong>(rep)</strong>';
            }

            $i++;

            $table[] = array($i,
                             $link,
                             $this->get_compound_single($row->ife_id),
                             #  may add get_compound_list as popover
                             #  to get_compound_single field
                             #$this->get_compound_list($row->pdb_id),
                             $this->get_source_organism($row->ife_id),
                             $row->title,
                             $row->experimental_technique,
                             $row->resolution,
                             $row->release_date);
        }

        return $table;
    }

    function get_statistics($id)
    {
        $this->db->select('NR.nr_release_id')
                 ->from('nr_classes AS NC')
                 ->join('nr_releases AS NR', 'NC.nr_release_id = NR.nr_release_id')
                 ->where('NC.name', $id)
                 ->order_by('NR.index', 'DESC')
                 ->limit(1);
        $result = $this->db->get()->result_array();

        $release_id = $result[0]['nr_release_id'];

        $this->db->select('pi.pdb_id')
                 ->select('ch.ife_id')
                 ->select('pi.title')
                 ->select('pi.experimental_technique')
                 ->select('pi.release_date')
                 ->select('pi.resolution')
                 ->select('ii.length')
                 ->select('ii.bp_count')
                 ->select('nl.index')
                 ->from('pdb_info AS pi')
                 ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                 ->join('nr_chains AS ch', 'ii.ife_id = ch.ife_id')
                 ->join('nr_ordering AS nl', 'ch.nr_chain_id = nl.nr_chain_id')
                 ->join('nr_classes AS cl', 'nl.nr_class_id = cl.nr_class_id AND ch.nr_release_id = cl.nr_release_id')
                 ->where('cl.name',$id)
                 ->where('cl.nr_release_id', $release_id)
                 #->where('nch.nr_release_id',$this->last_seen_in) # what was this doing? still necessary?
                 //->group_by('pi.pdb_id')
                 //->group_by('ii.ife_id')
                 ->order_by('nl.index','asc');

        $query = $this->db->get();

        $i = 0;
        $table = array();

        foreach ($query -> result() as $row) {
            $link = $this->make_pdb_widget_link($row->ife_id);
            //if ( $i==0 ) {
                //$link = $link . ' <strong>(rep)</strong>';
            //}
            $i++;
            $table[] = array($i,
                             $link,
                             $row->title,
                             //$this->get_source_organism($row->ife_id),
                             //$this->get_compound_list($row->pdb_id),
                             $row->experimental_technique,
                             $row->resolution,
                             $row->length);
                             //$row->bp_count);

        }

        return $table;

	}

	function get_heatmap_data($id)
    {
        $this->db->select('NR.nr_release_id')
                 ->from('nr_classes AS NC')
                 ->join('nr_releases AS NR', 'NC.nr_release_id = NR.nr_release_id')
                 ->where('NC.name', $id)
                 ->order_by('NR.index', 'DESC')
                 ->limit(1);
        $result = $this->db->get()->result_array();

        $release_id = $result[0]['nr_release_id'];

        $this->db->select('NC1.ife_id AS ife1')
                 ->select('NO1.index AS ife1_index')
                 ->select('NC2.ife_id AS ife2')
                 ->select('NO2.index AS ife2_index')
                 ->select('CSS.discrepancy')
                 ->from('nr_classes AS NCL')
                 ->join('nr_chains as NC1', 'NC1.nr_class_id = NCL.nr_class_id and NC1.nr_release_id = NCL.nr_release_id', 'inner')
                 ->join('nr_ordering as NO1', 'NO1.nr_chain_id = NC1.nr_chain_id and NO1.nr_class_id = NC1.nr_class_id', 'inner')
                 ->join('nr_chains as NC2', 'NC2.nr_class_id = NCL.nr_class_id and NC2.nr_release_id = NCL.nr_release_id', 'inner')
                 ->join('nr_ordering as NO2', 'NO2.nr_chain_id = NC2.nr_chain_id and NO2.nr_class_id = NC2.nr_class_id', 'inner')
                 ->join('ife_chains as IC1', 'IC1.ife_id = NC1.ife_id and IC1.index = 0', 'inner')
                 ->join('ife_chains as IC2', 'IC2.ife_id = NC2.ife_id and IC2.index = 0', 'inner')
                 ->join('chain_chain_similarity as CSS', 'CSS.chain_id_1 = IC1.chain_id and CSS.chain_id_2 = IC2.chain_id', 'left outer')
                 ->where('NC1.nr_chain_id !=', 'NC2.nr_chain_id')
                 ->where('NCL.name', $id)
                 ->where('NCL.nr_release_id', $release_id);

        $query = $this->db->get();

        //  why do this processing if the results are not used?!?


        foreach($query->result() as $row) {
            $ife1[] = $row->ife1;
            $ife1_index[] = $row->ife1_index;
            $ife2[] = $row->ife2;
            $ife2_index[] = $row->ife2_index;
            $discrepancy[] = $row->discrepancy;
        }

        $heatmap_data = json_encode($query->result());

        return $heatmap_data;
    }

    function get_compound_single($ife)
    {
        $this->db->select('group_concat(DISTINCT ci.compound separator ", ") as compound', FALSE)
                 ->from('ife_info AS ii')
                 ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id AND ii.model = ic.model')
                 ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id AND ci.pdb_id = ii.pdb_id')
                 ->where('ii.ife_id', $ife)
                 ->order_by('ci.chain_name');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $result = $row->compound;
        }

        return $result;
    }

    function get_compound_list($id)
    {
        $this->db->select('group_concat(compound separator ", ") as compounds', FALSE)
                 ->from('chain_info')
                 ->where('pdb_id', $id)
                 ->group_by('pdb_id');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $result = $row->compounds;
        }

        return $result;
    }

    function add_pdb_class($list)
    {
        if (!is_array($list)) {
            $s = explode(',', $list);
        } else {
            $s = $list;
        }

        for ($i = 0; $i < count($s); $i++) {
            $s[$i] = $this->add_space_to_long_IFE($s[$i]);
            $s[$i] = "<a class='pdb'>$s[$i]</a>";
        }

        return implode(', ', $s);
    }

    function count_pdb_class($list)
    {
        if (!is_array($list)) {
            $s = explode(',', $list);
        } else {
            $s = $list;
        }

        return count($s);

    }

    function get_history($id,$mode)
    {
        if ($mode == 'parents') {
            $sql = "CALL nr_set_diffs_parents(?,?)";
            $par = array($id,$this->first_seen_in);
        } elseif ($mode == 'children') {
            $sql = "CALL nr_set_diffs_children(?,?)";
            $par = array($id,$this->last_seen_in);
        }

        $query = $this->db->query($sql, $par);

        $table = array();

        foreach ($query->result() as $row) {
            $nr_class_name_out = ( $mode == 'parents' ) ? $row->nr_class_name_parent : $row->nr_class_name_child;
            $one_minus_two = ( $mode == 'parents' ) ? $row->added : $row->only;
            $two_minus_one = ( $mode == 'parents' ) ? $row->removed : $row->added;
            $one_minus_two_count = ( $mode == 'parents' ) ? $row->add_count : $row->only_count;
            $two_minus_one_count = ( $mode == 'parents' ) ? $row->rem_count : $row->add_count;

            $table[] = array($row->nr_class_name_base,
                             anchor(base_url("nrlist/view/".$nr_class_name_out),$nr_class_name_out),
                             anchor(base_url("nrlist/release/".$row->nr_release_id), $row->nr_release_id),
                             "(" . $row->int_count . ") " . $this->add_pdb_class($row->intersection),
                             "(" . $one_minus_two_count . ") " . $this->add_pdb_class($one_minus_two),
                             "(" . $two_minus_one_count . ") " . $this->add_pdb_class($two_minus_one)
                            );
        }

        $query->next_result(); ### clears the extra empty MySQL result set

        return $table;
    }

    function beautify_description_date($s)
    {
        return substr($s,0,4) .'-'. substr($s,4,2) .'-'. substr($s,6,2);
    }

    function get_change_counts_by_release()
    {
        $this->db->select('nr_release_id')
                 ->select('new_class_count AS nag')
                 ->select('removed_class_count AS nrg')
                 ->select('updated_class_count AS nug')
                 ->from('nr_parent_counts');
        $query = $this->db->get();

        $changes = array();
        foreach ($query->result() as $row) {
            $changes[$row->nr_release_id] = $row->nag + $row->nug + $row->nrg;
        }

        return $changes;
    }

    function get_label_type($changes)
    {
        if ($changes == 0) {
            $label = 'success';
        } elseif ($changes <= 20) {
            $label = 'notice';
        } elseif ($changes <= 100) {
            $label = 'warning';
        } else {
            $label = 'important';
        }

        return $label;
    }

    function get_pdb_files_counts()
    {
        $this->db->select('nch.nr_release_id, count(ii.pdb_id) as num')
                 ->from('ife_info AS ii')
                 ->join('nr_chains AS nch', 'ii.ife_id = nch.ife_id')
                 ->join('nr_classes AS ncl', 'nch.nr_class_id = ncl.nr_class_id AND nch.nr_release_id = ncl.nr_release_id')
                 ->where('ncl.resolution', 'all')
                 ->group_by('nch.nr_release_id');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $counts[$row->nr_release_id] = $row->num;
        }

        return $counts;
    }

    function get_newest_pdb_images()
    {
        $sql = "SELECT DISTINCT(`pdb_id`) FROM `pdb_info`" .
               "WHERE `release_date` >= DATE_ADD('" .
               date("Y-m-d H:i:s") . "', INTERVAL -1 WEEK);";
        $query = $this->db->query($sql);

        $new_files = array();
        foreach ($query->result() as $row) {
            $new_files[] = $row->pdb_id;
        }

        if ( count($new_files) > 0 ) {
            $html = '<h4>New RNA-containing PDB files released this week:</h4>';
            foreach ($new_files as $new_file) {
                $new_file = trim($new_file);
                $html .= $this->make_pdb_widget_link($new_file);
            }
        } else {
            $html = '<strong>No new RNA-containing PDB files this week.</strong>';
        }

        return $html;
    }

    function get_newest_nr_class_members()
    {
        // get two latest releases
        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index', 'desc')
                 ->limit(2);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $releases[] = $row->nr_release_id;
        }

        // get their release difference
        $this->db->select('added_pdbs')
                 ->from('__trash_nr_release_diff')
                 ->where('nr_release_id1', $releases[0])
                 ->where('nr_release_id2', $releases[1])
                 ->where('resolution', 'all');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $new_files = $row->added_pdbs;
        }

        if ($new_files != '' ) {
            $html = '<h4>New RNA-containing PDB files released this week:</h4>';
            $new_files = explode(',', $new_files);
            foreach ($new_files as $new_file) {
                $new_file = trim($new_file);
                $html .= $this->make_pdb_widget_link($new_file);
            }
        } else {
            $html = '<strong>No new RNA-containing PDB files this week.</strong>';
        }

        return $html;
    }

    function get_total_pdb_count()
    {
        $this->db->select('pdb_id')
                 ->from('pdb_info')
                 ->distinct();
        $query = $this->db->get();

        return $query->num_rows();
    }

    function get_all_releases()
    {
        $changes   = $this->get_change_counts_by_release();
        $pdb_count = $this->get_pdb_files_counts();
        $releases  = $this->get_release_precedence();

        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index','desc');
        $query = $this->db->get();

        $i = 0;
        foreach ($query->result() as $row) {
            if ($i == 0) {
                $id = anchor(base_url("nrlist/release/".$row->nr_release_id), $row->nr_release_id.' (current)');
                $i++;
            } else {
                $id = anchor(base_url("nrlist/release/".$row->nr_release_id), $row->nr_release_id);
            }

            if (array_key_exists($row->nr_release_id,$changes)) {
                $label = $this->get_label_type($changes[$row->nr_release_id]);
                $compare_url = base_url(array('nrlist','compare',$row->nr_release_id,$releases[$row->nr_release_id]));
                $status = "<a href='$compare_url' class='nodec'><span class='label {$label}'>{$changes[$row->nr_release_id]} changes</span></a>";
            } else {
                $status = '';
            }

            $description = $this->beautify_description_date($row->description);
            $table[] = array($id, $status, $description, $pdb_count[$row->nr_release_id] );
        }

        return $table;
    }

    function get_latest_release()
    {
        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();

        return $result[0]['nr_release_id'];
    }

    function make_release_label($num)
    {
        if ($num == 0) {
            return "<span class='label default'>$num</span>";
        } elseif ($num <= 10) {
            return "<span class='label notice'>$num</span>";
        } elseif ($num <= 100) {
            return "<span class='label warning'>$num</span>";
        } else {
            return "<span class='label important'>$num</span>";
        }
    }

    function get_release_precedence()
    {
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->order_by('index','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $ids[] = $row->nr_release_id;
        }

        for ($i=0; $i<count($ids)-1; $i++) {
            $releases[$ids[$i]] = $ids[$i+1];
        }

        return $releases;
    }

    function get_complete_release_history()
    {
        $releases = $this->get_release_precedence();

        $this->db->select('nr_release_id')
                 ->select('description')
                 ->select('parent_nr_release_id')
                 ->select('resolution')
                 ->select('new_class_count')
                 ->select('removed_class_count')
                 ->select('updated_class_count')
                 ->select('pdb_added_count')
                 ->select('pdb_removed_count')
                 ->from('nr_release_compare_counts')
                 ->order_by('index', 'desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->parent_nr_release_id == $releases[$row->nr_release_id]) {
                $tables[$row->resolution][] = array(
                    anchor(base_url(array('nrlist','release',$row->nr_release_id)),$row->nr_release_id),
                    $this->beautify_description_date($row->description),
                    anchor(base_url(array('nrlist','compare',$row->nr_release_id,$row->parent_nr_release_id)), $row->parent_nr_release_id),
                    $this->make_release_label($row->new_class_count),
                    $this->make_release_label($row->removed_class_count),
                    $this->make_release_label($row->updated_class_count),
                    $this->make_release_label($row->pdb_added_count),
                    $this->make_release_label($row->pdb_removed_count)
                );
            }
        }

        return $tables;
    }

    function get_release_description($id)
    {
        $this->db->select('description')
                 ->from('nr_releases')
                 ->where('nr_release_id',$id);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $s = $row->description;
        }

        return $this->beautify_description_date($s);
    }

    function get_annotation_label_type($comment)
    {
        if ($comment == 'Exact match') {
            return 'success';
        } else {
            return 'important';
        }
    }

    function add_annotation_label($class_id,$reason)
    {
        if (array_key_exists($class_id,$reason)) {
            $label = $this->get_annotation_label_type($reason[$class_id]);
            return " <span class='label $label'>{$reason[$class_id]}</span>";
        } else {
            return '';
        }
    }

    function add_space_to_long_IFE($ifename)
    {
        if (strlen($ifename) > 36) {
            $ife_set = explode('+', $ifename);
            for ($i=4; $i < count($ife_set); $i = $i + 4) {
                $ife_set[$i] = " $ife_set[$i]";
            }
            $ifename = implode("+",$ife_set);
        }
        return $ifename;
    }

/*
    ### DEFUNCT FUNCTION?
    function get_source_organism_for_class($pdb_list)
    {
        $source = '';
        foreach($pdb_list as $pdb_id) {
            $source = $this->get_source_organism($pdb_id);
            if ( $source != '' ) {
                break;
            }
        }
        return $source;
    }
*/

    function get_release($id, $resolution)
    {
        $resolution = str_replace('A', '', $resolution);

        // get raw release data
        $this->db->select('ii.ife_id, ii.pdb_id, nl.name, nc.rep')
                 ->from('ife_info AS ii')
                 ->join('nr_chains AS nc', 'ii.ife_id = nc.ife_id')
                 ->join('nr_classes AS nl', 'nc.nr_class_id = nl.nr_class_id AND nc.nr_release_id = nl.nr_release_id')
                 ->where('nc.nr_release_id', $id)
                 ->like('nl.name', "NR_{$resolution}", 'after');
        $query = $this->db->get();

        // reorganize by class and rep and pdb
        $class = array();
        foreach ($query->result() as $row) {
            $ifes[] = $row->ife_id;
            $pdbs[] = $row->pdb_id;

            if ($row->rep == 1) {
                $reps[$row->name] = $row->ife_id;
            }

            if (!array_key_exists($row->name, $class) ) {
                $class[$row->name] = array();
            }

            $class[$row->name][] = $row->ife_id;
        }

        $ifes = array_unique($ifes);
        $pdbs = array_unique($pdbs);

        // get general pdb info
        $this->db->select('pdb_id, title, resolution, experimental_technique')
                 ->from('pdb_info')
                 ->where_in('pdb_id', $pdbs )
                 ->group_by('pdb_id');
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $pdb[$row->pdb_id]['title']      = $row->title;
            $pdb[$row->pdb_id]['resolution'] = (is_null($row->resolution)) ? '' : number_format($row->resolution, 1) . ' &Aring';
            $pdb[$row->pdb_id]['experimental_technique'] = $row->experimental_technique;
        }

        // check if any of the files became obsolete
        $this->db->select('pdb_obsolete_id, replaced_by')
                 ->from('pdb_obsolete')
                 ->where_in('pdb_obsolete_id', $pdbs);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $pdb[$row->pdb_obsolete_id]['title'] = "OBSOLETE: replaced by <a class='pdb'>{$row->replaced_by}</a>";
            $pdb[$row->pdb_obsolete_id]['resolution'] = '';
        }

        // get annotations: "updated/>2 parents" etc.
        $this->db->select('nr_class_id, comment')
                 ->from('nr_classes')
                 ->where('nr_release_id',$id)
                 ->where('resolution', $resolution);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $reason[$row->nr_class_id] = $row->comment;
            $reason_flat[]             = $row->comment;
        }

        // count all annotation types
        $counts = array_count_values($reason_flat);
        $counts_text = '';

        foreach ($counts as $comment => $count) {
            $label = $this->get_annotation_label_type($comment);
            $counts_text .= "<span class='label $label'>$comment</span> <strong>$count</strong>;    ";
        }
        $counts_text .= '<br><br>';

        // make the table
        $table = array();
        $i = 1;

        // get order
        /*
        $this->db->select('nl.name, cr.pdb_id, cr.analyzed_length, cr.experimental_length, cr.compound, cr.species_name, cr.species_id, nl.nr_class_id, count(DISTINCT ii.ife_id) as num')
                 ->from('nr_chains AS nc')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->join('nr_classes AS nl', 'nc.nr_class_id = nl.nr_class_id AND nc.nr_release_id = nl.nr_release_id')
                 ->join('nr_class_reps AS cr', 'nl.name = cr.name AND nl.nr_class_id = cr.nr_class_id')
                 ->where('nc.nr_release_id', $id)
                 ->like('nl.name', "NR_{$resolution}", 'after')
                 ->group_by('nl.name')
                 ->order_by('num','desc')
                 ->order_by('nc.rep','desc')
                 ->order_by('ii.ife_id');
        */

        /*
        $this->db->select('cr.name, cr.pdb_id, cr.analyzed_length, cr.experimental_length, cr.compound, cr.species_name, cr.species_id, cr.nr_class_id, rc.num')
                 ->from('nr_class_reps_bar AS cr')
                 ->join('nr_class_reps_count AS rc', 'cr.nr_release_id = rc.nr_release_id AND cr.name = rc.name AND cr.ife_id = rc.ife_id')
                 ->where('cr.nr_release_id', $id)
                 ->like('cr.name', "NR_{$resolution}", 'after')
                 ->order_by('cr.analyzed_length','desc')
                 #->order_by('rc.num','desc')
                 ->order_by('cr.ife_id');
        */

/*
    SELECT nl.name,
            ii.ife_id, 
            ii.pdb_id, 
            ii.length AS analyzed_length, 
            GROUP_CONCAT(DISTINCT ci.compound SEPARATOR ', ') AS compound, 
            sm.species_name, 
            sm.species_mapping_id, 
            nl.nr_class_id, 
            COUNT(DISTINCT ii.ife_id) AS num 
        FROM nr_chains AS nc 
        INNER JOIN ife_info AS ii 
            ON nc.ife_id = ii.ife_id 
        INNER JOIN nr_classes AS nl 
            ON nc.nr_class_id = nl.nr_class_id 
            AND nc.nr_release_id = nl.nr_release_id 
        INNER JOIN ife_chains AS ic 
            ON ii.ife_id = ic.ife_id 
        INNER JOIN chain_info AS ci 
            ON ic.chain_id = ci.chain_id 
        LEFT JOIN species_mapping AS sm 
            ON ci.taxonomy_id = sm.species_mapping_id 
        WHERE nl.nr_release_id = '9.13' 
            AND nl.resolution = '4.0' 
        GROUP BY nl.name, 
                    nl.nr_release_id, 
                    nl.resolution
    ;
*/

        $this->db->select('nl.name')
                 ->select('ii.ife_id')
                 ->select('ii.pdb_id')
                 ->select('ii.length AS analyzed_length')
                 ->select('group_concat(DISTINCT ci.compound separator ", ") as compound', FALSE)
                 ->select('sm.species_name')
                 ->select('sm.species_mapping_id')
                 ->select('nl.nr_class_id')
                 ->select('COUNT(DISTINCT ii.ife_id) AS num')
                 ->from('nr_chains AS nc')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->join('nr_classes AS nl', 'nc.nr_class_id = nl.nr_class_id AND nc.nr_release_id = nl.nr_release_id')
                 ->join('ife_chains AS ic', 'ii.ife_id')
                 ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id')
                 ->join('species_mapping AS sm', 'ci.taxonomy_id = sm.species_mapping_id', 'left')
                 ->where('nl.nr_release_id', $id)
                 ->where('nl.resolution', $resolution)
                 ->group_by('nl.name')
                 ->group_by('nl.nr_release_id')
                 ->group_by('nl.resolution');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $class_id = $row->name;
            $nums     = $row->num;
            $ife_id   = $reps[$class_id];
            $pdb_id   = $row->pdb_id;

            $source   = ( is_null($row->species_name) ) ? "" :
                            anchor_popup("$this->tax_url$row->species_id", "$row->species_name");
            $compound = (strlen($row->compound) > 40 ) ? substr($row->compound, 0, 40) . "[...]" : $row->compound;

            if (preg_match('/\+/',$ife_id)){
                $best_chains = "";
                $best_models = "";
                $ife_set     = explode('+', $ife_id);
                $idx         = 0;

                foreach ($ife_set as $each_ife){
                    $ife_split        = explode('|', $each_ife);
                    $get_chains[$idx] = $ife_split[2];
                    $get_models[$idx] = $ife_split[1];
                    $idx++;
                }

                $sort_chains = array_unique($get_chains);
                $sort_models = array_unique($get_models);
                sort($sort_chains);
                sort($sort_models);
                $best_chains = implode(', ', $sort_chains);
                $best_models = implode(', ', $sort_models);
            } else {
                $ife_split   = explode('|', $ife_id);
                $best_chains = $ife_split[2];
                $best_models = $ife_split[1];
            }

            // $id refers to the release_id
            $table[] = array($i,
                             anchor(base_url("nrlist/view/".$class_id),$class_id)
                             #anchor(base_url("nrlist/view/".$class_id."/".$id),$class_id,$id)
                             . '<br>' . $this->add_annotation_label($row->nr_class_id, $reason)
                             . '<br>' . $source,
                             $this->add_space_to_long_IFE($ife_id) . ' (<strong class="pdb">' . $pdb_id . '</strong>)' .
                             '<ul>' .
                             '<li>' . $compound . '</li>' .
                             '<li>' . $pdb[$pdb_id]['experimental_technique'] . '</li>' .
                             '<li>Chain(s): ' . $best_chains . '; model(s): ' . $best_models . '</li>' .
                             '</ul>',
                             $pdb[$pdb_id]['resolution'],
                             $row->analyzed_length,
                             #$row->analyzed_length . '&nbsp;(analyzed)<br>' .
                             #$row->experimental_length . '&nbsp;(experimental)',
                             "(" . $nums . "," . $this->count_pdb_class($class[$class_id]) . ") " . $this->add_pdb_class($class[$class_id])
                            );
            $i++;
        }

        return array('table' => $table, 'counts' => $counts_text);
    }

    function count_all_nucleotides($pdb_id)
    {
        $this->db->select('count(unit_id) as length')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb_id)
                 ->where_in('unit', array('A','C','G','U'))
                 ->order_by('count(*)', 'DESC')
                 ->limit(1);
        $query = $this->db->get();

        $result = $query->result();
        return $result[0]->length;
    }

    function get_csv($release, $resolution)
    {
        $resolution = str_replace('A', '', $resolution);
        $this->db->select('ii.ife_id as id, nl.name as class_id, nc.rep')
                 ->from('nr_chains AS nc')
                 ->join('nr_classes AS nl', 'nc.nr_class_id = nl.nr_class_id AND nc.nr_release_id = nl.nr_release_id')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->where('nc.nr_release_id', $release)
                 ->where('resolution', $resolution);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            if ( $row->rep == 1 ) {
                $reps[$row->class_id] = $row->id;
            }
            $members[$row->class_id][] = $row->id;
        }

        $csv = '';
        foreach($reps as $class_id => $rep) {
            $csv .= '"' . implode('","', array($class_id, $rep, implode(',', $members[$class_id]))) . '"' . "\n";
        }

        return $csv;
    }

    function get_compare_radio_table()
    {
        $changes = $this->get_change_counts_by_release();

        $this->db->select('nr_release_id AS id, description')
                 ->from('nr_releases')
                 ->order_by('index','desc');
        $query = $this->db->get();

        $table = array();
        foreach ($query->result() as $row) {
            if (array_key_exists($row->id,$changes)) {
                $label_type = $this->get_label_type($changes[$row->id]);
                $label = " <span class='label {$label_type}'>{$changes[$row->id]} changes</span>";
            } else {
                $label = '';
            }

            $table[] = form_radio(array('name'=>'release1','value'=>$row->id)) . $row->id . $label;
            $table[] = form_radio(array('name'=>'release2','value'=>$row->id)) . $row->id;
            $table[] = $this->beautify_description_date($row->description);
        }

        return $table;
    }

    function is_valid_release($id)
    {
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->where('nr_release_id', $id)
                 ->limit(1);

        if ( $this->db->get()->num_rows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

    function is_valid_class($id)
    {
        $this->db->select('name')
                 ->from('nr_classes')
                 ->where('name', $id)
                 ->limit(1);

        if ( $this->db->get()->num_rows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

    function get_two_newest_releases()
    {
        $this->db->select('nr_release_id')
                 ->select('parent_nr_release_id')
                 ->from('nr_releases')
                 ->order_by('index', 'desc')
                 ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row){
            $rel1 = $row->nr_release_id;
            $rel2 = $row->parent_nr_release_id;
        }

        return array($rel1, $rel2);
    }

}

/* End of file nrlist_model.php */
/* Location: ./application/model/nrlist_model.php */