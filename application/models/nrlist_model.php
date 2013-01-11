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
        $this->db->select('*,count(id) as ids')
                 ->from('nr_classes')
                 ->where('release_id', $rel)
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
                 ->where('nr_release_id1',$rel1)
                 ->where('nr_release_id2',$rel2);
        $query = $this->db->get();
        if ($query->num_rows == 0) {
            $this->db->select()
                     ->from('nr_release_diff')
                     ->where('nr_release_id1',$rel2)
                     ->where('nr_release_id2',$rel1);
            $query = $this->db->get();
        }

        foreach ($query->result() as $row) {

            $data['uls'][$labels[$row->resolution]]['num_motifs1'] = $counts1[$row->resolution];
            $data['uls'][$labels[$row->resolution]]['num_motifs2'] = $counts2[$row->resolution];

            if ($row->num_same_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_intersection'] = ul(array_map("add_url", split(', ',$row->same_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_intersection'] = '';
            }
            if ($row->num_updated_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_updated'] = ul(array_map("add_url", split(', ',$row->updated_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_updated'] = '';
            }
            if ($row->num_added_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_1'] = ul(array_map("add_url", split(', ',$row->added_groups)),$attributes);
            } else {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_1'] = '';
            }
            if ($row->num_removed_groups > 0) {
                $data['uls'][$labels[$row->resolution]]['ul_only_in_2'] = ul(array_map("add_url", split(', ',$row->removed_groups)),$attributes);
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
                 ->join('nr_releases','nr_classes.release_id=nr_releases.id')
                 ->where('nr_classes.id',$id)
                 ->order_by('nr_releases.date');
        $query = $this->db->get();
        $releases[0][0] = 'Release';
        $releases[1][0] = 'Date';
        $i = 0;
        foreach ($query->result() as $row) {
            if ($i==0) {
                $this->first_seen_in = $row->release_id;
                $i++;
            }
            $releases[0][] = anchor(base_url("nrlist/release/".$row->release_id), $row->release_id);
            $releases[1][] = $this->beautify_description_date($row->description);
        }
        $this->last_seen_in = $row->release_id;
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
            $current_release = $row->id;
        }
        $this->current_release = $current_release;
        $this->db->select()
                 ->from('nr_classes')
                 ->where('id',$id)
                 ->where('release_id',$current_release);
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
        $this->db->select()
                 ->from('pdb_info')
                 ->where('structureId', $pdb_id)
                 ->like('entityMacromoleculeType', 'RNA')
                 ->where("chainLength = (SELECT max(chainLength) FROM pdb_info WHERE structureId ='$pdb_id' AND entityMacromoleculeType LIKE '%RNA%')");
        $query = $this->db->get()->result();
        return $query[0]->source;
    }

    function get_members($id)
    {
        $this->db->select()
                 ->from('nr_pdbs')
                 ->join('pdb_info','pdb_info.structureId=nr_pdbs.id')
                 ->where('nr_pdbs.class_id',$id)
                 ->where('nr_pdbs.release_id',$this->last_seen_in)
                 ->group_by('structureId')
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
                 ->from('pdb_info')
                 ->where('structureId', $id)
                 ->group_by('structureId');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $result = $row->compounds;
        }
        return $result;
    }

    function add_pdb_class($list)
    {
        if (!is_array($list)) {
            $s = split(',', $list);
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
        $this->db->select()->from('nr_set_diff')->where('nr_class1',$id);
        if ($mode == 'parents') {
            $this->db->where('release_id',$this->first_seen_in);
        } elseif ($mode=='children') {
            $this->db->where('release_id !=',$this->first_seen_in);
        }
        $query = $this->db->get();
        $table = array();
        foreach ($query->result() as $row) {
            $table[] = array($row->nr_class1,
                             anchor(base_url("nrlist/view/".$row->nr_class2),$row->nr_class2),
                             anchor(base_url("nrlist/release/".$row->release_id), $row->release_id),
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
        $this->db->select('nr_release_id1')
                 ->select_sum('num_added_groups','nag')
                 ->select_sum('num_removed_groups','nrg')
                 ->select_sum('num_updated_groups','nug')
                 ->from('nr_release_diff')
                 ->where('direct_parent',1)
                 ->group_by('nr_release_id1');
        $query = $this->db->get();
        $changes = array();
        foreach ($query->result() as $row) {
            $changes[$row->nr_release_id1] = $row->nag + $row->nug + $row->nrg;
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
        $this->db->select('release_id,count(id) as num')
                 ->from('nr_pdbs')
                 ->like('class_id','NR_all','after')
                 ->group_by('release_id');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $counts[$row->release_id] = $row->num;
        }
        return $counts;
    }

    function get_newest_pdb_images()
    {
        $sql = "SELECT DISTINCT(`structureId`) FROM `pdb_info`" .
               "WHERE `releaseDate` >= DATE_ADD('" .
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
            $releases[] = $row->id;
        }

        // get their release difference
        $this->db->select()
                 ->from('nr_release_diff')
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
        $this->db->select('structureId')
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
                $id = anchor(base_url("nrlist/release/".$row->id), $row->id.' (current)');
                $i++;
            } else {
                $id = anchor(base_url("nrlist/release/".$row->id), $row->id);
            }
            if (array_key_exists($row->id,$changes)) {
                $label = $this->get_label_type($changes[$row->id]);
                $compare_url = base_url(array('nrlist','compare',$row->id,$releases[$row->id]));
                $status = "<a href='$compare_url' class='nodec'><span class='label {$label}'>{$changes[$row->id]} changes</span></a>";
            } else {
                $status = '';
            }
            $description = $this->beautify_description_date($row->description);
            $table[] = array($id, $status, $description, $pdb_count[$row->id] );
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
        return $result[0]['id'];
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
        $this->db->select('id')->from('nr_releases')->order_by('date','desc');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $ids[] = $row->id;
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
                 ->join('nr_release_diff','nr_releases.id=nr_release_diff.nr_release_id1')
                 ->where('direct_parent',1)
                 ->order_by('date','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if ($row->nr_release_id2 == $releases[$row->id]) {
                $tables[$row->resolution][] = array(
                    anchor(base_url(array('nrlist','compare',$row->id,$releases[$row->id])), $row->id),
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
                 ->where('id',$id);
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
                 ->where('release_id', $id)
                 ->like('class_id', "NR_{$resolution}", 'after');
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
        $this->db->select('structureId, structureTitle, resolution')
                 ->from('pdb_info')
                 ->where_in('structureId', $pdbs )
                 ->group_by('structureId');
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdb[$row->structureId]['title']      = $row->structureTitle;
            $pdb[$row->structureId]['resolution'] = (is_null($row->resolution)) ? '' : number_format($row->resolution, 1) . ' &Aring';
        }

        // check if any of the files became obsolete
        $this->db->select()
                 ->from('pdb_obsolete')
                 ->where_in('obsolete_id', $pdbs);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdb[$row->obsolete_id]['title'] = "OBSOLETE: replaced by <a class='pdb'>{$row->replaced_by}</a>";
            $pdb[$row->obsolete_id]['resolution'] = '';
        }

        // get annotations: updated/>2 parents etc
        $this->db->select()
                 ->from('nr_classes')
                 ->where('release_id',$id)
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
        $this->db->select('*,count(id) as num')
                 ->from('nr_pdbs')
                 ->where('release_id', $id)
                 ->like('class_id', "NR_{$resolution}", 'after')
                 ->group_by('class_id')
                 ->order_by('num','desc')
                 ->order_by('id');
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
                             anchor(base_url("nrlist/view/".$class_id),$class_id),
                             $this->add_annotation_label($class_id, $reason),
                             $pdb_id,
                             $pdb[$pdb_id]['title'],
                             $pdb[$pdb_id]['resolution'],
                             $this->get_source_organism_for_class($class[$class_id]),
                             $this->add_pdb_class($class[$class_id]));
            $i++;
        }
        return array('table' => $table, 'counts' => $counts_text);
    }

    function get_csv($release, $resolution)
    {
        $resolution = str_replace('A', '', $resolution);
        $this->db->select('nr_pdbs.id as id, nr_pdbs.class_id as class_id, nr_pdbs.rep as rep')
                 ->from('nr_pdbs')
                 ->join('nr_classes', 'nr_pdbs.class_id = nr_classes.id')
                 ->where('nr_pdbs.release_id', $release)
                 ->where('nr_classes.release_id', $release)
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
        $this->db->select()
                 ->from('nr_releases')
                 ->where('id', $id)
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
                 ->where('id', $id)
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
