<?php
class Loops_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

        // Call the Model constructor
        parent::__construct();
    }

    function is_valid_loop_id($id)
    {
        $this->db->select()
                 ->from('loop_info')
                 ->where('loop_id', $id);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function get_loop_list($pdb_id)
    {
        $this->db->select('group_concat(unit_id) AS unit_ids, loop_info.loop_id', FALSE)
                 ->from('loop_info')
                 ->join('loop_positions', 'loop_info.loop_id=loop_positions.loop_id')
                 ->join('pdb_unit_id_correspondence', 'loop_positions.nt_id=pdb_unit_id_correspondence.old_id')
                 ->where('loop_info.pdb_id', $pdb_id)
                 ->group_by('loop_info.loop_id');
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $data = array();
            foreach($query->result() as $row) {
                $data[] = '"' . implode('","', array($row->id, $row->unit_ids)) . '"';
            }
            $table = implode("\n", $data);
        } else {
            $table = 'No loops found';
        }

        return $table;
    }

    function get_loop_info($id)
    {
        $result = array();
        // info from loops_all
        $this->db->select()
                 ->from('loop_info')
                 ->where('loop_id',$id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $loop_info = $query->row();
            $result['length'] = $loop_info->length;
            $result['sequence'] = $loop_info->seq;
        } else {
            $result['length'] = '';
            $result['sequence'] = '';
        }

        // qa info
        $this->db->select()
                 ->from('loop_qa')
                 ->where('loop_qa_id',$id)
                 ->order_by('release_id', 'desc')
                 ->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $loop_qa = $query->row();
            switch ($loop_qa->status) {
                case 1:
                    $result['qa'] = 'Valid loop';
                    break;
                case 2:
                    $result['qa'] = 'Missing nucleotides: ' . $loop_qa->nt_signature;
                    break;
                case 3:
                    $result['qa'] = 'Modified nucleotides: ' . $loop_qa->modifications;
                    break;
                case 4:
                    $result['qa'] = 'Abnormal chains';
                    break;
                case 5:
                    $result['qa'] = 'Incomplete nucleotides: ' . $loop_qa->nt_signature;
                    break;
                case 6:
                    $result['qa'] = 'Self-complementary: ' . $loop_qa->complementary;
                    break;
            }
        } else {
            $result['qa'] = 'QA data not found';
        }

        // get list of bulges
        $this->db->select()
                 ->from('loop_positions')
                 ->where('loop_id',$id)
                 ->where('bulge',1)
                 ->order_by('position');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $bulges = array();
            foreach ($query->result() as $row) {
                $parts = explode('_', $row->nt_id);
                $bulges[] = $parts[5] . $parts[4];
            }
            $result['bulges'] = implode(', ', $bulges);
        } else {
            $result['bulges'] = "None detected";
        }

        return $result;
    }

    function get_pdb_info($id)
    {
        $result = array();
        //general pdb info
        $result['pdb'] = substr($id,3,4);
        $result['rna3dhub_link'] = anchor_popup('pdb/' . $result['pdb'] . '/motifs', 'RNA 3D Hub');
        $result['pdb_link'] = anchor_popup('http://www.rcsb.org/pdb/explore.do?structureId=' . $result['pdb'], 'PDB');
        $this->db->select()
                 ->from('pdb_info')
                 ->where('structureId',$result['pdb'])
                 ->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $pdb_info = $query->row();
            $result['pdb_desc'] = $pdb_info->structureTitle;
            $result['pdb_exptechnique'] = $pdb_info->experimentalTechnique;
            if ($pdb_info->resolution == Null) {
                $result['pdb_resolution'] = '';
            } else {
                $result['pdb_resolution'] = $pdb_info->resolution . ' &Aring;';
            }
        } else {
            $result['pdb_desc'] = '';
            $result['pdb_exptechnique'] = '';
            $result['pdb_resolution'] = '';
        }

        // non-redundant equivalence class info
        // get latest NR release id
        $this->db->select()
                 ->from('nr_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $query = $this->db->get();
        $release = $query->row();
        // get equivalence classes
        $this->db->select()
                 ->from('nr_pdbs')
                 ->where('id',$result['pdb'])
                 ->where('release_id', $release->id);
        $query = $this->db->get();

        $nr_classes = array();
        foreach ($query->result() as $row) {
            $nr_classes[] = anchor_popup('nrlist/view/' . $row->class_id, $row->class_id);
        }
        $result['nr_classes'] = implode(', ', $nr_classes);

        return $result;
    }

    function get_current_motif_id_from_loop_id($id)
    {
        // get current motif release id
        $this->db->select()
                 ->from('ml_releases')
                 ->order_by('date','desc')
                 ->where('type',substr($id, 0, 2))
                 ->limit(1);
        $query = $this->db->get();
        $release = $query->row();

        // get motif id
        $this->db->select()
                 ->from('ml_loops')
                 ->where('release_id',$release->id)
                 ->where('ml_loops_id',$id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $motif = $query->row();
            return array('motif_id' => $motif->motif_id,
                         'release_id' => $release->id);
        } else {
            return NULL;
        }

    }

    function get_most_recent_motif_assignment($loop_id)
    {
        $loop_type = substr($loop_id, 0, 2);
        $this->db->select('ml_loops.motif_id as motif_id, ml_releases.ml_releases_id as release_id')
                 ->from('ml_loops')
                 ->join('ml_releases', 'ml_releases.ml_releases_id=ml_loops.release_id')
                 ->where('ml_loops.ml_loops_id', $loop_id)
                 ->where('ml_releases.type', $loop_type)
                 ->order_by('ml_releases.date', 'desc');
        $query = $this->db->get();
        if ( $query->num_rows() == 0 ) {
            return NULL;
        } else {
            $result = $query->result();
            return array('motif_id'   => $result[0]->motif_id,
                         'release_id' => $result[0]->release_id);
        }
    }

    function get_motif_info($id)
    {
        $result = array();
        $motif = $this->get_current_motif_id_from_loop_id($id);

        $old_release = FALSE;
        // try to get motif assignment from previous releases
        if ($motif == NULL) {
            $motif = $this->get_most_recent_motif_assignment($id);
            $old_release = TRUE;
        }

        if ($motif != NULL) {
            $result['motif_id'] = $motif['motif_id'];
            $result['motif_url'] = anchor_popup('motif/view/' . $motif['motif_id'], $motif['motif_id']);
            // get motif annotations
            $this->db->select()
                     ->from('ml_motif_annotations')
                     ->where('motif_id', $result['motif_id']);
            $query = $this->db->get();
            $annotation = $query->row();
            if ($annotation->common_name != Null) {
                $result['motif_common_name'] = $annotation->common_name;
            } else {
                $result['motif_common_name'] = 'Not assigned yet';
            }
            $result['bp_signature'] = $annotation->bp_signature;
            // get number of motif instances
            $this->db->select()
                     ->from('ml_loops')
                     ->where('release_id',$motif['release_id'])
                     ->where('motif_id', $motif['motif_id']);
            $query = $this->db->get();
            $result['motif_instances'] = $query->num_rows();
            if ( $old_release ) {
                $result['motif_url'] = $result['motif_url'] . " (release {$motif['release_id']})";
            }
        } else {
            $result['motif_id'] = "This loop hasn't been annotated with motifs yet";
            $result['motif_url'] = "This loop hasn't been annotated with motifs yet";
            $result['bp_signature'] = 'Not available';
            $result['motif_instances'] = 0;
            $result['motif_common_name'] = 'Not available';
        }

        return $result;
    }

    function get_protein_info($id)
    {
        $result = array();
        // get nearby protein chains
        $this->db->select('t3.chain')
                 ->distinct()
                 ->from('loop_positions as t1')
                 ->join('pdb_distances as t2', 't1.nt_id = t2.id1')
                 ->join('pdb_coordinates as t3', 't2.id2 = t3.id')
                 ->where('loop_id', $id)
                 ->where('char_length(t3.unit) = 3')
                 ->not_like('t3.coordinates','HETATM','after');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $chains = array();
            foreach ($query->result() as $row) {
                $chains[] = $row->chain;
            }
            $this->db->select()
                     ->from('pdb_info')
                     ->where('structureId', substr($id,3,4))
                     ->where_in('chainId', $chains);
            $query = $this->db->get();
            // db_name = 'PDB, Uniprot'
            // db_ids  = 'PDB_id, Uniprot_id'
            foreach ($query->result() as $row) {
                $result['proteins'][$row->chainId]['description'] = $row->compound;
                $databases = explode(',', $row->db_name);
                $ids = explode(',', $row->db_id);
                for ($i = 0; $i < count($databases); $i++) {
                    if (preg_match('/Uniprot/i', $databases[$i])) {
                        $result['proteins'][$row->chainId]['uniprot'] = anchor_popup('http://www.uniprot.org/uniprot/' . trim($ids[$i]), $ids[$i]);
                    }
                }
            }
        } else {
            $result['proteins'] = array();
        }

        return $result;
    }

    function get_current_motif_release($motif_type)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type', $motif_type)
                 ->order_by('date', 'desc')
                 ->limit(1);
        $query = $this->db->get();
        $row = array_shift($query->result());
        return $row->id;
    }

    function get_similar_loops($id)
    {
        $where = "loop_searches.disc > 0 AND (loop_searches.loop_id_1='$id' OR loop_searches.loop_id_2='$id')";
        $this->db->select('loop_searches.loop_id_1, loop_searches.loop_id_2, loop_searches.disc, loop_search_qa.status, loop_search_qa.message')
                 ->from('loop_searches')
                 ->join('loop_search_qa','loop_searches.loop_id_1=loop_search_qa.loop_id_1 AND loop_searches.loop_id_2=loop_search_qa.loop_id_2','left')
                 ->where($where, NULL, FALSE)
                 ->order_by('loop_searches.disc', 'asc');
        $query = $this->db->get();

        $matches = array();
        $table = array();
        $count = 0;
        $motif = $this->get_current_motif_id_from_loop_id($id);
        $ml_release_id = $this->get_current_motif_release(substr($id, 0, 2));

        foreach ($query->result() as $row) {
            // establish what loop is the match of $id
            if ($row->loop_id1 == $id) {
                $match = $row->loop_id2;
            } else {
                $match = $row->loop_id1;
            }
            // exclude rows with reversed orientation of loop_id1 and loop_id2
            if ( array_key_exists($match, $matches) ) {
                continue;
            } else {
                $matches[$match]='';
            }
            $count++;
            $this->db->select()
                     ->from('ml_loops')
                     ->where('ml_loops_id',$match)
                     ->where('release_id',$ml_release_id);
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                $result = $q->row();
            } else {
                $result = '';
            }

            // compose the message
            if ($row->status == 4) {
                $message = 'Unmatched basepair: ' . $row->message;
            } elseif ($row->status == 5) {
                $message = 'Unmatched near pair: ' . $row->message;
            } elseif ($row->status == 6) {
                $message = 'Unmatched stacking: ' . $row->message;
            } elseif ($row->status == 7) {
                $message = 'Basepair mismatch: ' . $row->message;
            } elseif ($row->status == 7) {
                $message = 'Basepair-basestacking mismatch: ' . $row->message;
            } else {
                $message = '';
            }

            $radiobutton = "<input type='radio'
                                   name='g'
                                   id='s{$count}'
                                   class='jmolTools-loop-pairs'
                                   data-coord='{$id}:{$match}'>
                            <label for='s{$count}'>$match</label>
                            <span class='loop_link'>" . anchor_popup("loops/view/$match", '&#10140;') . "</span>";

            if ( $result == '' ) {
                $motif_link = 'not annotated yet';
            } elseif ($result->motif_id == $motif['motif_id'] ) {
                $motif_link = '<span class="label success">' .
                              anchor_popup('motif/view/' . $result->motif_id, $result->motif_id)
                              . '</span>';
            } else {
                $motif_link = anchor_popup('motif/view/' . $result->motif_id, $result->motif_id);
            }

            $table[] = array($count,
                             array('data' => $radiobutton, 'class' => 'loop'),
                             number_format($row->disc, 4),
                             $motif_link,
                             $message);
        }

        return $table;
    }

    function get_loop_stats()
    {
        // get release order
        $this->db->select()
                 ->from('loop_releases')
                 ->order_by('date', 'desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $releases[] = $row->id;
            $dates[$row->id] = substr($row->date,0,10);
        }

        // get loop counts group by loop type and release
        $this->db->select('release_id, status, count(status) as counts, substr(loop_qa_id, 1, 2) as loop_type', FALSE)
                 ->from('loop_qa')
                 ->group_by(array('status', 'release_id', 'loop_type'));
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $results[$row->loop_type][$row->release_id][$row->status] = $row->counts;
        }

        foreach (array_keys($results) as $loop_type) {
            foreach ($releases as $release) {
                $tables[$loop_type][] = array($release,
                                            $dates[$release],
                                            array_sum(array_values($results[$loop_type][$release])),
                                            $this->make_view_loops_link($results,$loop_type,$release,1),
                                            $this->make_view_loops_link($results,$loop_type,$release,2),
                                            $this->make_view_loops_link($results,$loop_type,$release,3),
                                            $this->make_view_loops_link($results,$loop_type,$release,4),
                                            $this->make_view_loops_link($results,$loop_type,$release,5),
                                            $this->make_view_loops_link($results,$loop_type,$release,6));
            }
        }
        return $tables;
    }

    function make_view_loops_link($counts,$motif_type,$release_id,$status)
    {
        if (!array_key_exists($status,$counts[$motif_type][$release_id])) {
            return '0';
        }

        $type = $this->qa_status[$status];
        if ($type == 'complementary' and $motif_type != 'IL') {
            return 'N/A';
        }
        else {
            return anchor(
                          base_url(array('loops','view_all',$type,$motif_type,$release_id)),
                          $counts[$motif_type][$release_id][$status]
                          );
        }
    }

    function make_ligand_link($s)
    {
        // http://www.pdb.org/pdb/images/UR3_300.png
        $parts = explode(',',$s);
        $links = '';
        foreach ($parts as $part) {
            $part = trim($part);
            $links .= "<a href='http://www.rcsb.org/pdb/ligand/ligandsummary.do?hetId={$part}' target='_blank'>$part</a> ";
        }
        return $links;
    }

    function get_loops($type,$motif_type,$release_id,$num,$offset)
    {
        $verbose = $type;
        $type = array_search($type, $this->qa_status);
        $this->db->select()
                 ->from('loop_qa')
                 ->join('loop_info','loop_qa.loop_qa_id=loop_info.loop_id')
                 ->where('status',$type)
                 ->where('type',$motif_type)
                 ->where('release_id',$release_id)
                 ->order_by('loop_info.loop_id')
                 ->limit($num,$offset);
        $query = $this->db->get();

        $i = 1;
        foreach ($query->result() as $row) {
            if ($verbose == 'modified') {
                $this->table->set_heading('#','id','PDB','Modifications');
                $info = $this->make_ligand_link($row->modifications);
            } elseif ($verbose == 'complementary') {
                $this->table->set_heading('#','id','PDB','Info');
                $info = $row->complementary;
            } elseif ($verbose != 'valid') {
                $this->table->set_heading('#','id','PDB','Info');
                $info = $row->nt_signature;
            } elseif ($verbose == 'valid') {
                $this->table->set_heading('#','id','PDB','Info');
                $info = $row->seq;
            } else {
                $this->table->set_heading('#','id','PDB','Info');
                $info = '';
            }
            $this->table->add_row($offset + $i,
                                          $this->make_radio_button($row->id),
                                          '<a class="pdb">' . substr($row->id,3,4) . '</a>',
                                          $info);
            $i++;
        }

        if ($query->num_rows() == 0) {
            $data['table'] = "No $type $motif_type loops were found";
        } else {
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
            $this->table->set_template($tmpl);
        }
        return $this->table->generate();
    }

    function make_radio_button($id) {
        $loop_link = anchor_popup("loops/view/$id", '&#10140;');
        return array('data' => "<label><input type='radio' class='jmolInline' data-type='loop_id'
                id={$id} data-nt='{$id}' name='l'><span>{$id}</span>
                <span class='loop_link'>{$loop_link}</span></label>", 'class' => 'loop');
    }

    function get_loops_count($type,$motif_type,$release_id)
    {
        $type = array_search($type, $this->qa_status);
        $this->db->from('loop_qa')
                 ->where('status',$type)
                 ->like('loop_qa_id',$motif_type,'after')
                 ->where('release_id',$release_id);
        return $this->db->count_all_results();
    }

    function initialize_sfdata()
    {
        $this->q   = $this->query_dcc();
        $this->avg = $this->get_averages();
        $this->low_is_good = array('mapman_Biso_mean','mapman_real_space_R','sfcheck_B_iso_main_chain',
        'sfcheck_B_iso_side_chain','sfcheck_connect','sfcheck_density_index_side_chain','sfcheck_density_index_main_chain',
        'sfcheck_real_space_R','sfcheck_real_space_R_side_chain','sfcheck_shift','sfcheck_shift_side_chain');

        $this->high_is_good = array('mapman_correlation','mapman_occupancy_mean','sfcheck_correlation',
        'sfcheck_correlation_side_chain');
    }

    function get_graphs()
    {
        $url = 'http://rna.bgsu.edu/img/MotifAtlas/dcc_loops';
        if ($handle = opendir('/Servers/rna.bgsu.edu/img/MotifAtlas/dcc_loops')) {
            /* This is the correct way to loop over the directory. */
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != ".DS_Store") {
                    $graphs[] = $entry;
                }
            }
            closedir($handle);
        }
        $text = '';
        foreach ($graphs as $graph) {
            $text .= <<<EOT
              <li>
                <a href="{$url}/{$graph}" rel='g'>
                  <img class="thumbnail span4" src="$url/{$graph}" alt="">
                  $graph
                </a>
              </li>
EOT;
        }
        return $text;
    }

    function query_dcc()
    {
        // SELECT LOOP_id,`mltest`.`dcc_residues`.* FROM `ml_loop_positions`
        // JOIN `mltest`.`dcc_residues`
        // ON nt_id = `mltest`.`dcc_residues`.`dcc_residues_id`
        // WHERE `ml_loop_positions`.release_id = '0.5'
        // ORDER BY loop_id ASC

        // SELECT LOOP_id,`mltest`.`dcc_residues`.*,loops_all.nt_ids,ml_loops.`motif_id` FROM `ml_loop_positions`
        // JOIN `mltest`.`dcc_residues`
        // JOIN loops_all
        // LEFT JOIN ml_loops
        // ON nt_id = `mltest`.`dcc_residues`.`dcc_residues_id` AND loop_info.loop_id=loop_id AND ml_loops.ml_loopsid=LOOP_id
        // WHERE `ml_loop_positions`.release_id = '0.5' AND ml_loops.release_id='0.5'
        // ORDER BY loop_id ASC;

        $this->db->select()
                 ->from('ml_loop_positions')
                 ->join('dcc_residues','nt_id = dcc_residues.dcc_residues_id')
                 ->join('loop_info','loop_id=loop_info.loop_id')
                 ->join('ml_loops','loop_id=ml_loops.ml_loops_id','left')
                 ->where('ml_loop_positions.release_id','0.5')
                 ->where('ml_loops.release_id','0.5')
                 ->group_by('loop_id') // NB! comment out or leave in?
                 ->order_by('ml_loops.motif_id','asc')
                 ->order_by('loop_id','asc');
//                  ->limit(10);
        $query = $this->db->get();

        return $query;
    }

    function get_heading()
    {
        $heading = array('#','loop id','nt id','motif');
        $i = 1;
        foreach ($this->avg as $key => $value) {
            if (preg_match('/mapman|sfcheck/',$key)) {
                $heading[] = "<a href='#' class='twipsy' title='{$key}. Avg {$value}'>$i</a>";
                $i++;
            }
        }
        return $heading;
    }


    function get_averages()
    {
        $cum = array();
        $i = 0;
        foreach ($this->q->result() as $row) {

            if ($i == 0) {
                $fields = get_object_vars($row);
                foreach ($fields as $key => $value) {
                    if (!array_key_exists($key,$cum)) {
                        $cum[$key] = 0;
                    }
                }
                $i = 1;
            }

            foreach ($fields as $key => $value) {
                $cum[$key] += $value;
            }
        }

        $total = $this->q->num_rows();
        foreach ($cum as $key => $value) {
            $avg[$key] = number_format($value / $total, 3);
        }
        return $avg;
    }

    // get a row from the query object, check if any of the fields are below
    // the average, if so, then return a formatted row for the table, otherwise
    // return an empty string.
    function analyze_nucleotide($row, $i)
    {
        $props = get_object_vars($row);

        $extreme_case = false;
        foreach ($props as $key => $value) {

            if (array_key_exists($key,$this->low_is_good)) {
                if ($value > $this->avg[$key]) {
                    $extreme_case = true;
                    break;
                }
            } else {
                if ($value < $this->avg[$key]) {
                    $extreme_case = true;
                    break;
                }

            }


            // high b values are bad, so we highlight them
//             $pos = strpos($key,'iso');
//             if ( $pos != false and $value > $this->avg[$key] ) {
//                 $extreme_case = true;
//                 break;
//             }
//             if ( array_key_exists($key,$this->avg) and $value < $this->avg[$key] ) {
//                 $extreme_case = true;
//                 break;
//             }
        }

        if ($extreme_case == true) {
            return array(
                $i,
                $this->make_checkbox($row->loop_id,$row->nt_ids),
                '<a class="pdb">' . substr($row->nt_id,0,4) . '</a>    ' . substr($row->nt_id,10),
                anchor_popup(site_url(array('motif/view/0.5',$row->motif_id)),$row->motif_id,array('width'=>'1000')),
                $this->make_label($row->sfcheck_correlation,'sfcheck_correlation'),
                $this->make_label($row->sfcheck_correlation_side_chain,'sfcheck_correlation_side_chain'),
                $this->make_label($row->sfcheck_real_space_R,'sfcheck_real_space_R'),
                $this->make_label($row->sfcheck_real_space_R_side_chain,'sfcheck_real_space_R_side_chain'),
                $this->make_label($row->sfcheck_connect,'sfcheck_connect'),
                $this->make_label($row->sfcheck_shift,'sfcheck_shift'),
                $this->make_label($row->sfcheck_shift_side_chain,'sfcheck_shift_side_chain'),
                $this->make_label($row->sfcheck_density_index_main_chain,'sfcheck_density_index_main_chain'),
                $this->make_label($row->sfcheck_density_index_side_chain,'sfcheck_density_index_side_chain'),
                $this->make_label($row->sfcheck_B_iso_main_chain,'sfcheck_B_iso_main_chain'),
                $this->make_label($row->sfcheck_B_iso_side_chain,'sfcheck_B_iso_side_chain'),
                $this->make_label($row->mapman_correlation,'mapman_correlation'),
                $this->make_label($row->mapman_real_space_R,'mapman_real_space_R'),
                $this->make_label($row->mapman_Biso_mean,'mapman_Biso_mean'),
                $this->make_label($row->mapman_occupancy_mean,'mapman_occupancy_mean')
            );
        } else {
            return array();
        }

    }

    function make_label($value, $key)
    {
        if (in_array($key,$this->low_is_good)) {
            if ($value > $this->avg[$key]) {
                return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            } else {
                return "<span class='label success twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            }
        } else {
            if ($value < $this->avg[$key]) {
                return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            } else {
                return "<span class='label success twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            }
        }
    }

    function make_checkbox($loop,$nts)
    {
        return "<label class='narrow'><input type='radio' name='loops' id='{$loop}' class='jmolInline' data-nt='$nts'><span>$loop</span></label>";
    }

    function get_sfdata_table()
    {
        $i = 1;
        foreach ($this->q->result() as $row) {
            $row_array = $this->analyze_nucleotide($row,$i);
            if (count($row_array) > 0) {
                $table[] = $row_array;
                $i++;
            }
        }
        return $table;
    }

    function get_fields_array()
    {
        return array('sfcheck_correlation','sfcheck_correlation_side_chain',
                     'sfcheck_real_space_R','sfcheck_real_space_R_side_chain',
                     'sfcheck_connect',
                     'sfcheck_shift','sfcheck_shift_side_chain',
                     'sfcheck_density_index_main_chain',
                     'sfcheck_density_index_side_chain',
                     'sfcheck_B_iso_main_chain','sfcheck_B_iso_side_chain',
                     'mapman_correlation','mapman_real_space_R',
                     'mapman_Biso_mean','mapman_occupancy_mean');
    }

    function get_min($pdb)
    {
        $this->db->select_min('sfcheck_correlation')
                 ->select_min('sfcheck_correlation_side_chain')
                 ->select_min('sfcheck_real_space_R')
                 ->select_min('sfcheck_real_space_R_side_chain')
                 ->select_min('sfcheck_connect')
                 ->select_min('sfcheck_shift')
                 ->select_min('sfcheck_shift_side_chain')
                 ->select_min('sfcheck_density_index_main_chain')
                 ->select_min('sfcheck_density_index_side_chain')
                 ->select_min('sfcheck_B_iso_main_chain')
                 ->select_min('sfcheck_B_iso_side_chain')
                 ->select_min('mapman_correlation')
                 ->select_min('mapman_real_space_R')
                 ->select_min('mapman_Biso_mean')
                 ->select_min('mapman_occupancy_mean')
                 ->from('dcc_residues')
                 ->like('dcc_residues_id',strtoupper($pdb),'after');
        $result = $this->db->get()->result_array();
        return $result[0];
    }

    function get_max($pdb)
    {
        $this->db->select_max('sfcheck_correlation')
                 ->select_max('sfcheck_correlation_side_chain')
                 ->select_max('sfcheck_real_space_R')
                 ->select_max('sfcheck_real_space_R_side_chain')
                 ->select_max('sfcheck_connect')
                 ->select_max('sfcheck_shift')
                 ->select_max('sfcheck_shift_side_chain')
                 ->select_max('sfcheck_density_index_main_chain')
                 ->select_max('sfcheck_density_index_side_chain')
                 ->select_max('sfcheck_B_iso_main_chain')
                 ->select_max('sfcheck_B_iso_side_chain')
                 ->select_max('mapman_correlation')
                 ->select_max('mapman_real_space_R')
                 ->select_max('mapman_Biso_mean')
                 ->select_max('mapman_occupancy_mean')
                 ->from('dcc_residues')
                 ->like('dcc_residues_id',strtoupper($pdb),'after');
        $result = $this->db->get()->result_array();
        return $result[0];
     }

    function get_dcc_pdbs()
    {
        $this->db->select('DISTINCT(substr(dcc_residues_id,1,4)) as pdb FROM dcc_residues;',false);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $result[] = anchor(base_url(array('loops','sfjmol',$row->pdb)),$row->pdb);
        }
        return $result;
    }

}

/* End of file loops_model.php */
/* Location: ./application/model/loops_model.php */