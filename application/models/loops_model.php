<?php
class Loops_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        $this->q   = $this->query_dcc();
        $this->avg = $this->get_averages();

        // Call the Model constructor
        parent::__construct();
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
        $query = $this->db->get();

        return $query;
    }

    function get_heading()
    {
        $heading = array('#','loop id','nt id','motif');
        $i = 1;
        foreach ($this->avg as $key => $value) {
            $heading[] = "<a href='#' class='twipsy' title='{$key}. Avg {$value}'>$i</a>";
            $i++;
        }
        return $heading;
    }


    function get_averages()
    {
        $cum['sfcheck_correlation']              = 0;
        $cum['sfcheck_correlation_side_chain']   = 0;
        $cum['sfcheck_real_space_R']             = 0;
        $cum['sfcheck_real_space_R_side_chain']  = 0;
        $cum['sfcheck_connect']                  = 0;
        $cum['sfcheck_shift']                    = 0;
        $cum['sfcheck_shift_side_chain']         = 0;
        $cum['sfcheck_density_index_main_chain'] = 0;
        $cum['sfcheck_density_index_side_chain'] = 0;
        $cum['sfcheck_B_iso_main_chain']         = 0;
        $cum['sfcheck_B_iso_side_chain']         = 0;
        $cum['mapman_correlation']               = 0;
        $cum['mapman_real_space_R']              = 0;
        $cum['mapman_Biso_mean']                 = 0;
        $cum['mapman_occupancy_mean']            = 0;

        foreach ($this->q->result() as $row) {
            $cum['sfcheck_correlation']              += $row->sfcheck_correlation;
            $cum['sfcheck_correlation_side_chain']   += $row->sfcheck_correlation_side_chain;
            $cum['sfcheck_real_space_R']             += $row->sfcheck_real_space_R;
            $cum['sfcheck_real_space_R_side_chain']  += $row->sfcheck_real_space_R_side_chain;
            $cum['sfcheck_connect']                  += $row->sfcheck_connect;
            $cum['sfcheck_shift']                    += $row->sfcheck_shift;
            $cum['sfcheck_shift_side_chain']         += $row->sfcheck_shift_side_chain;
            $cum['sfcheck_density_index_main_chain'] += $row->sfcheck_density_index_main_chain;
            $cum['sfcheck_density_index_side_chain'] += $row->sfcheck_density_index_side_chain;
            $cum['sfcheck_B_iso_main_chain']         += $row->sfcheck_B_iso_main_chain;
            $cum['sfcheck_B_iso_side_chain']         += $row->sfcheck_B_iso_side_chain;
            $cum['mapman_correlation']               += $row->mapman_correlation;
            $cum['mapman_real_space_R']              += $row->mapman_real_space_R;
            $cum['mapman_Biso_mean']                 += $row->mapman_Biso_mean;
            $cum['mapman_occupancy_mean']            += $row->mapman_occupancy_mean;
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
            // high b values are bad, so we highlight them
            $pos = strpos($key,'iso');
            if ( $pos != false and $value > $this->avg[$key] ) {
                $extreme_case = true;
                break;
            }
            if ( array_key_exists($key,$this->avg) and $value < $this->avg[$key] ) {
                $extreme_case = true;
                break;
            }
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
        $pos = strpos($key,'iso');
        if ( $pos != false and $value > $this->avg[$key] ) {
            return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
        } elseif ( $pos != false and $value <= $this->avg[$key] ) {
            return "<span class='label twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
        }

        if ($value < $this->avg[$key]) {
            return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
        } else {
            return "<span class='label twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
        }
    }

    function make_checkbox($loop,$nts)
    {
        return "<label class='narrow'><input type='radio' name='loops' id='{$loop}' class='jmolInline' data-nt='$nts'>$loop</label>";
    }

    function get_sfdata_table()
    {
        $i = 1;
        foreach ($this->q->result() as $row) {

            $row_array = $this->analyze_nucleotide($row,$i);
            if (count($row_array) > 0) {
                $table[] = $row_array;
            }

            $i++;
        }
        return $table;
    }

}

/* End of file loops_model.php */
/* Location: ./application/model/loops_model.php */



//         $fields = array('sfcheck_correlation','sfcheck_real_space_R',
//                         'sfcheck_real_space_R_side_chain','sfcheck_connect',
//                         'sfcheck_shift','sfcheck_shift_side_chain',
//                         'sfcheck_density_index_main_chain',
//                         'sfcheck_density_index_side_chain',
//                         'sfcheck_B_iso_main_chain','sfcheck_B_iso_side_chain',
//                         'mapman_correlation','mapman_real_space_R',
//                         'mapman_Biso_mean','mapman_occupancy_mean');