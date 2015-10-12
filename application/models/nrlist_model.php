<?php

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
        // Call the Model constructor
        parent::__construct();

    }

    function count_motifs($rel)
    {
        $this->db->select('*,count(nr_class_id) as ids')
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

        $this->db->select()
                 ->from('nr_release_diff')
                 ->where('nr_release_id_1',$rel1)
                 ->where('nr_release_id_2',$rel2);
        $query = $this->db->get();
        if ($query->num_rows == 0) {
            $this->db->select()
                     ->from('nr_release_diff')
                     ->where('nr_release_id_1',$rel2)
                     ->where('nr_release_id_2',$rel1);
            $query = $this->db->get();
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

        return $data;
    }

    function get_releases_by_class($id)
    {
        $this->db->select()
                 ->from('nr_classes')
                 ->join('nr_releases','nr_classes.nr_release_id = nr_releases.nr_release_id')
                 ->where('nr_classes.nr_class_id',$id)
                 ->order_by('nr_releases.date');
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
        $this->db->select()
                 ->from('nr_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $current_release = $row->nr_release_id;
        }
        $this->current_release = $current_release;
        $this->db->select()
                 ->from('nr_classes')
                 ->where('nr_class_id',$id)
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

    function get_source_organism($pdb_id)
    {
        $this->db->select('source')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb_id)
                 ->like('entity_macromolecule_type', 'RNA')
                 ->where("chain_length = (SELECT max(chain_length) FROM chain_info WHERE pdb_id ='$pdb_id' AND entity_macromolecule_type LIKE '%RNA%')");
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $result = $query->result();
            return $result[0]->source;
        } else {
            return '';
        }
    }

    function get_members($id)
    {
        $this->db->select()
                 ->from('nr_pdbs')
                 ->join('pdb_info','pdb_info.pdb_id = nr_pdbs.nr_pdb_id')
                 ->where('nr_pdbs.nr_class_id',$id)
                 ->where('nr_pdbs.nr_release_id',$this->last_seen_in)
                 ->group_by('pdb_id')
                 ->order_by('nr_pdbs.rep','desc');
        $query = $this->db->get();
        $i = 0;
        foreach ($query->result() as $row) {
            $link = $this->make_pdb_widget_link($row->structureId);
            if ( $i==0 ) {
                $link = $link . ' <strong>(rep)</strong>';
            }
            $i++;
            $table[] = array($i,
                             $link,
                             $row->structureTitle,
                             $this->get_source_organism($row->structureId),
                             $this->get_compound_list($row->structureId),
                             $row->experimentalTechnique,
                             $row->resolution,
                             $row->releaseDate);
        }
        return $table;
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
            $s[$i] = "<a class='pdb'>$s[$i]</a>";
        }
        return implode(', ', $s);
    }

    function get_history($id,$mode)
    {
        $this->db->select()->from('nr_set_diff')->where('nr_class_id_1',$id);
        if ($mode == 'parents') {
            $this->db->where('nr_release_id',$this->first_seen_in);
        } elseif ($mode=='children') {
            $this->db->where('nr_release_id !=',$this->first_seen_in);
        }
        $query = $this->db->get();
        $table = array();
        foreach ($query->result() as $row) {
            $table[] = array($row->nr_class_id_1,
                             anchor(base_url("nrlist/view/".$row->nr_class_id_2),$row->nr_class_id_2),
                             anchor(base_url("nrlist/release/".$row->nr_release_id), $row->nr_release_id),
                             $this->add_pdb_class($row->intersection),
                             $this->add_pdb_class($row->one_minus_two),
                             $this->add_pdb_class($row->two_minus_one));
        }
        return $table;
    }

    function beautify_description_date($s)
    {
        return substr($s,0,4) .'-'. substr($s,4,2) .'-'. substr($s,6,2);
    }

    function get_change_counts_by_release()
    {
        $this->db->select('nr_release_id_1')
                 ->select_sum('num_added_groups','nag')
                 ->select_sum('num_removed_groups','nrg')
                 ->select_sum('num_updated_groups','nug')
                 ->from('nr_release_diff')
                 ->where('direct_parent',1)
                 ->group_by('nr_release_id_1');
        $query = $this->db->get();
        $changes = array();
        foreach ($query->result() as $row) {
            $changes[$row->nr_release_id_1] = $row->nag + $row->nug + $row->nrg;
        }
        return $changes;
    }

    function get_label_type($changes)
    {
        if ($changes == 0) {
            $label = 'success';
        }
        elseif ($changes <= 20) {
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
        $this->db->select('nr_release_id,count(nr_pdb_id) as num')
                 ->from('nr_pdbs')
                 ->like('nr_class_id','NR_all','after')
                 ->group_by('nr_release_id');
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
            $new_files[] = $row->structureId;
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
        $this->db->select()
                 ->from('nr_releases')
                 ->order_by('date', 'desc')
                 ->limit(2);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $releases[] = $row->nr_release_id;
        }

        // get their release difference
        $this->db->select()
                 ->from('nr_release_diff')
                 ->where('nr_release_id_1', $releases[0])
                 ->where('nr_release_id_2', $releases[1])
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

        $this->db->select()
                 ->from('nr_releases')
                 ->order_by('date','desc');
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
        $this->db->select()
                 ->from('nr_releases')
                 ->order_by('date','desc')
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
                 ->order_by('date','desc');
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

        $this->db->select()
                 ->from('nr_releases')
                 ->join('nr_release_diff','nr_releases.nr_release_id = nr_release_diff.nr_release_id_1')
                 ->where('direct_parent',1)
                 ->order_by('date','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->nr_release_id_2 == $releases[$row->nr_release_id]) {
                $tables[$row->resolution][] = array(
                    anchor(base_url(array('nrlist','compare',$row->nr_release_id,$releases[$row->nr_release_id])), $row->nr_release_id),
                    $this->beautify_description_date($row->description),
                    $this->make_release_label($row->num_added_groups),
                    $this->make_release_label($row->num_removed_groups),
                    $this->make_release_label($row->num_updated_groups),
                    $this->make_release_label($row->num_added_pdbs),
                    $this->make_release_label($row->num_removed_pdbs)
                );
            }
        }
        return $tables;
    }

    function get_release_description($id)
    {
        $this->db->select()
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

    function get_release($id, $resolution)
    {
        $resolution = str_replace('A', '', $resolution);
        // get raw release data
        $this->db->select()
                 ->from('nr_pdbs')
                 ->where('nr_release_id', $id)
                 ->like('nr_class_id', "NR_{$resolution}", 'after');
        $query = $this->db->get();

        // reorganize by class and rep and pdb
        $class = array();
        foreach ($query->result() as $row) {
            $pdbs[] = $row->id;
            if ($row->rep == 1) {
                $reps[$row->class_id] = $row->id;
            }
            if (!array_key_exists($row->class_id, $class) ) {
                $class[$row->class_id] = array();
            }
            $class[$row->class_id][]     = $row->id;
        }
        $pdbs = array_unique($pdbs);

        // get general pdb info
        $this->db->select('pdb_id, title, resolution, experimentalTechnique')
                 ->from('pdb_info')
                 ->where_in('pdb_id', $pdbs )
                 ->group_by('pdb_id');
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdb[$row->structureId]['title']      = $row->structureTitle;
            $pdb[$row->structureId]['resolution'] = (is_null($row->resolution)) ? '' : number_format($row->resolution, 1) . ' &Aring';
            $pdb[$row->structureId]['experimentalTechnique'] = $row->experimentalTechnique;
        }

        // get best chains and models
        $this->db->select()
                 ->from('pdb_best_chains_and_models')
                 ->where_in('pdb_id', $pdbs);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $best_chains[$row->pdb_id] = $row->best_chains;
            $best_models[$row->pdb_id] = $row->best_models;
        }

        // check if any of the files became obsolete
        $this->db->select()
                 ->from('pdb_obsolete')
                 ->where_in('pdb_obsolete_id', $pdbs);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdb[$row->obsolete_id]['title'] = "OBSOLETE: replaced by <a class='pdb'>{$row->replaced_by}</a>";
            $pdb[$row->obsolete_id]['resolution'] = '';
        }

        // get annotations: updated/>2 parents etc
        $this->db->select()
                 ->from('nr_classes')
                 ->where('nr_release_id',$id)
                 ->where('resolution', $resolution);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $reason[$row->id]  = $row->comment;
            $reason_flat[]     = $row->comment;
        }

        // count all annotation types
        $counts = array_count_values($reason_flat);
        $counts_text = '';
        foreach ($counts as $comment => $count) {
            $label = $this->get_annotation_label_type($comment);
            $counts_text .= "<span class='label $label'>$comment</span> <strong>$count</strong>;    ";
        }
        $counts_text .= '<br><br>';

        // get order
        $this->db->select('*,count(nr_pdb_id) as num')
                 ->from('nr_pdbs')
                 ->where('nr_release_id', $id)
                 ->like('nr_class_id', "NR_{$resolution}", 'after')
                 ->group_by('nr_class_id')
                 ->order_by('num','desc')
                 ->order_by('nr_pdb_id');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $order[] = $row->class_id;
        }

        // make the table
        $table = array();
        $i = 1;
        foreach ($order as $class_id) {
            $pdb_id = $reps[$class_id];
            $table[] = array($i,
                             anchor(base_url("nrlist/view/".$class_id),$class_id)
                             . '<br>' . $this->add_annotation_label($class_id, $reason)
                             . '<br>' . $this->get_source_organism_for_class($class[$class_id]),
                             '<strong class="pdb">' . $pdb_id . '</strong>' .
                             '<ul>' .
                             '<li>' . $pdb[$pdb_id]['title'] . '</li>' .
                             '<li>' . $pdb[$pdb_id]['experimentalTechnique'] . '</li>' .
                             '<li>Chain(s): ' . $best_chains[$pdb_id] .
                             '; model(s): ' . $best_models[$pdb_id] . '</li>' .
                             '</ul>',
                             $pdb[$pdb_id]['resolution'],
                             $this->count_all_nucleotides($pdb_id),
                             $this->add_pdb_class($class[$class_id]));
            $i++;
        }
        return array('table' => $table, 'counts' => $counts_text);
    }

    function count_all_nucleotides($pdb_id)
    {
        $this->db->select('count(*) as length')
                 ->from('pdb_coordinates')
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
        $this->db->select('nr_pdbs.nr_pdb_id as id, nr_pdbs.nr_class_id as class_id, nr_pdbs.rep as rep')
                 ->from('nr_pdbs')
                 ->join('nr_classes', 'nr_pdbs.nr_class_id = nr_classes.nr_class_id')
                 ->where('nr_pdbs.nr_release_id', $release)
                 ->where('nr_classes.nr_release_id', $release)
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

        $this->db->select()
                  ->from('nr_releases')
                  ->order_by('date','desc');
        $query = $this->db->get();

        $table = array();
        foreach ($query->result() as $row) {
            if (array_key_exists($row->nr_release_id,$changes)) {
                $label_type = $this->get_label_type($changes[$row->nr_release_id]);
                $label = " <span class='label {$label_type}'>{$changes[$row->id]} changes</span>";
            } else {
                $label = '';
            }
            $table[] = form_radio(array('name'=>'release1','value'=>$row->nr_release_id)) . $row->nr_release_id . $label;
            $table[] = form_radio(array('name'=>'release2','value'=>$row->nr_release_id)) . $row->nr_release_id;
            $table[] = $this->beautify_description_date($row->description);
        }
        return $table;
    }

    function is_valid_release($id)
    {
        $this->db->select()
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
        $this->db->select()
                 ->from('nr_classes')
                 ->where('nr_class_id', $id)
                 ->limit(1);
        if ( $this->db->get()->num_rows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

}

/* End of file nrlist_model.php */
/* Location: ./application/model/nrlist_model.php */
