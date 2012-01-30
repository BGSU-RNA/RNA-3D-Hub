<?php
class Loops_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

        // Call the Model constructor
        parent::__construct();
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
        $this->db->select('release_id, status, count(status) as counts, substr(id, 1, 2) as loop_type', FALSE)
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
                 ->where('status',$type)
                 ->like('id',$motif_type,'after')
                 ->where('release_id',$release_id)
                 ->order_by('id')
                 ->limit($num,$offset);
        $query = $this->db->get();

        $table = array();
        $i = 1;
        foreach ($query->result() as $row) {
            $loop = array($offset + $i,
                          '<label><input type="radio" class="loop" name="l"><span>' . $row->id . '</span></label>',
                          '<a class="pdb">' . substr($row->id,3,4) . '</a>');
            if ($verbose == 'modified') {
                $loop[] = $this->make_ligand_link($row->modifications);
            } elseif ($verbose == 'complementary') {
                $loop[] = $row->complementary;
            } elseif ($verbose != 'valid') {
                $loop[] = $row->nt_signature;
            }
            $table[] = $loop;
            $i++;
        }
        return $table;
    }

    function get_loops_count($type,$motif_type,$release_id)
    {
        $type = array_search($type, $this->qa_status);
        $this->db->from('loop_qa')
                 ->where('status',$type)
                 ->like('id',$motif_type,'after')
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
                <a href="{$url}/{$graph}" class='fancybox' rel='g'>
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