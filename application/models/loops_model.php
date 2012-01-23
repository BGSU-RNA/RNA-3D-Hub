<?php
class Loops_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        // Call the Model constructor
        parent::__construct();
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
                <a href="{$url}/{$graph}" class='fancybox' rel='g'>
                  <img class="thumbnail span4" src="$url/{$graph}" alt="">
                  $graph
                </a>
              </li>
EOT;
        }
        return $text;
    }

    function get_loop_modifications()
    {
        $query = $this->db->get('loop_modifications');
        foreach ($query->result() as $row) {
            $mod[$row->id] = $row->modification;
        }
        return $mod;
    }

    function make_ligand_link($s)
    {
        // http://www.pdb.org/pdb/images/UR3_300.png
        $parts = explode(',',$s);
        $links = '';
        foreach ($parts as $part) {
            $links .= "<a href='http://www.rcsb.org/pdb/ligand/ligandsummary.do?hetId={$part}' target='_blank'>$part</a> ";
        }
        return $links;
    }

    function get_loops($type,$motif_type,$release_id,$num,$offset)
    {
        $this->db->select('id')
                 ->from('loop_qa')
                 ->where($type,1)
                 ->like('id',$motif_type,'after')
                 ->where('release_id',$release_id)
                 ->order_by('id')
                 ->limit($num,$offset);
        $query = $this->db->get();

        if ($type == 'modified_nt') {
            $mod = $this->get_loop_modifications();
        }

        $table = array();
        $i = 1;
        foreach ($query->result() as $row) {
            if ($type == 'modified_nt') {
                $table[] = array($offset + $i,
                                 '<label><input type="radio" class="loop" name="l"><span>' . $row->id . '</span></label>',
                                 '<a class="pdb">' . substr($row->id,3,4) . '</a>',
                                 $this->make_ligand_link($mod[$row->id]));
            } else {
                $table[] = array($offset + $i,
                                 '<label><input type="radio" class="loop" name="l"><span>' . $row->id . '</span></label>',
                                 '<a class="pdb">' . substr($row->id,3,4) . '</a>');
            }

            $i++;
        }
        return $table;
    }

    function get_loops_count($type,$motif_type,$release_id)
    {
        $this->db->from('loop_qa')
                 ->where($type,1)
                 ->like('id',$motif_type,'after')
                 ->where('release_id',$release_id);
        return $this->db->count_all_results();
    }

    function get_status_counts_by_release()
    {
        $motif_types = array('IL','HL','J3');
        $types       = array('valid','modified_nt','missing_nt');//,'complementary');

        foreach ($motif_types as $motif_type) {
            $this->db->select_sum('valid')
                     ->select_sum('complementary')
                     ->select_sum('modified_nt')
                     ->select_sum('missing_nt')
                     ->select('release_id')
                     ->from('loop_qa')
                     ->like('id',$motif_type,'after')
                     ->group_by('release_id');
            $query = $this->db->get();

            foreach ($query->result() as $row) {
                $result[$motif_type]['valid'][$row->release_id] = $row->valid;
                $result[$motif_type]['complementary'][$row->release_id] = $row->complementary;
                $result[$motif_type]['modified_nt'][$row->release_id] = $row->modified_nt;
                $result[$motif_type]['missing_nt'][$row->release_id] = $row->missing_nt;
            }
        }

        foreach ($motif_types as $motif_type) {
            foreach ($motif_types as $motif_type) {
                $this->db->select('count(id) as num, release_id',false)
                         ->from('loop_qa')
                         ->like('id',$motif_type,'after')
                         ->group_by('release_id');
                $query = $this->db->get();

                foreach ($query->result() as $row) {
                    $result[$motif_type]['total'][$row->release_id] = $row->num;
                }
            }
        }
        return $result; // $result['IL']['valid']['0.1'] = 20220
    }

    function get_release_order()
    {
        $this->db->select()
                 ->from('loop_releases')
                 ->order_by('date','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $result[] = $row->id;
            $dates[$row->id] = substr($row->date,0,10);
        }
        return array('order' => $result, 'dates' => $dates); // $result[0] = '0.1'
    }

    function make_loop_release_link($counts,$motif_type,$type,$release_id)
    {
        if ($type == 'total') {
            return $counts[$motif_type][$type][$release_id];
        } elseif ($type == 'complementary' and $motif_type != 'IL') {
            return 'N/A';
        }
        else {
            return anchor(
                          base_url(array('loops','view_all',$type,$motif_type,$release_id)),
                          $counts[$motif_type][$type][$release_id]
                          );
        }
    }

    function get_loop_releases()
    {
        $temp = $this->get_release_order();
        $releases = $temp['order'];
        $dates    = $temp['dates'];

        // $counts['valid']['IL']['0.1'] = 20220
        $counts = $this->get_status_counts_by_release();

        $motif_types = array('IL','HL','J3');
        foreach ($motif_types as $motif_type) {
            for ($i = 0; $i<count($releases); $i++) {
                $original_id = $releases[$i];
                if ($i == 0) {
                    $id = $releases[$i] . ' (current)';
                } else {
                    $id = $releases[$i];
                }
                $tables[$motif_type][] = array($id,
                                               $dates[$original_id],
                                               $this->make_loop_release_link($counts,$motif_type,'total',$releases[$i]),
                                               $this->make_loop_release_link($counts,$motif_type,'valid',$releases[$i]),
                                               $this->make_loop_release_link($counts,$motif_type,'modified_nt',$releases[$i]),
                                               $this->make_loop_release_link($counts,$motif_type,'missing_nt',$releases[$i]),
                                               $this->make_loop_release_link($counts,$motif_type,'complementary',$releases[$i])
                                               );
            }
        }
        return $tables;
    }

    function query_dcc()
    {
        // SELECT LOOP_id,`mltest`.`dcc_residues`.* FROM `ml_loop_positions`
        // JOIN `mltest`.`dcc_residues`
        // ON nt_id = `mltest`.`dcc_residues`.`id`
        // WHERE `ml_loop_positions`.release_id = '0.5'
        // ORDER BY loop_id ASC

        // SELECT LOOP_id,`mltest`.`dcc_residues`.*,loops_all.nt_ids,ml_loops.`motif_id` FROM `ml_loop_positions`
        // JOIN `mltest`.`dcc_residues`
        // JOIN loops_all
        // LEFT JOIN ml_loops
        // ON nt_id = `mltest`.`dcc_residues`.`id` AND loops_all.id=loop_id AND ml_loops.id=LOOP_id
        // WHERE `ml_loop_positions`.release_id = '0.5' AND ml_loops.release_id='0.5'
        // ORDER BY loop_id ASC;

        $this->db->select()
                 ->from('ml_loop_positions')
                 ->join('dcc_residues','nt_id = dcc_residues.id')
                 ->join('loops_all','loop_id=loops_all.id')
                 ->join('ml_loops','loop_id=ml_loops.id','left')
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
                 ->like('id',strtoupper($pdb),'after');
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
                 ->like('id',strtoupper($pdb),'after');
        $result = $this->db->get()->result_array();
        return $result[0];
     }

    function get_dcc_pdbs()
    {
        $this->db->select('DISTINCT(substr(id,1,4)) as pdb FROM dcc_residues;',false);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $result[] = anchor(base_url(array('loops','sfjmol',$row->pdb)),$row->pdb);
        }
        return $result;
    }

}

/* End of file loops_model.php */
/* Location: ./application/model/loops_model.php */