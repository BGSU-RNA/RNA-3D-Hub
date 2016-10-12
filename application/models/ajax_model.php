<?php
class Ajax_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        // Call the Model constructor
        parent::__construct();
    }

    function count_nucleotides($pdb_id)
    {
        // count nucleotides in all chains
        return $this->db->select('unit')
                        ->from('unit_info')
                        ->where('pdb_id', $pdb_id)
                        ->where_in('unit', array('A','C','G','U'))
                        ->count_all_results();
    }

    function count_basepairs($pdb_id)
    {
        $f_lwbp = array('cHH','cHS','cHW','cSH','cSS','cSW','cWH','cWS','cWW','tHH',
                        'tHS','tHW','tSH','tSS','tSW','tWH','tWS','tWW');

        return $this->db->select('f_lwbp')
                        ->from('unit_pairs_interactions')
                        ->where('pdb_id', $pdb_id)
                        ->where_in('f_lwbp', $f_lwbp)
                        ->count_all_results() / 2;
    }

    function get_source_organism($pdb_id)
    {
        $this->db->select('source')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb_id)
                 ->like('entity_macromolecule_type', 'RNA')
                 ->where("chain_length = (SELECT max(chain_length) FROM chain_info WHERE pdb_id ='$pdb_id' AND entity_macromolecule_type LIKE '%RNA%')");
        $query = $this->db->get()->result();

        return $query[0]->source;
    }

    function get_pdb_info($pdb)
    {
        $pdb_url = "http://www.rcsb.org/pdb/explore/explore.do?structureId=";

        $this->db->select('pi.title')
                 ->select('pi.experimental_technique')
                 ->select('pi.resolution')
                 ->from('pdb_info AS pi')
                 ->where('pi.pdb_id', $pdb)
                 ->limit(1);
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $row = $query->row();

            // don't report resolution for nmr structures
            if (preg_match('/NMR/', $row->experimental_technique)) {
                $resolution = '';
            } else {
                $resolution = "<u>Resolution:</u> {$row->resolution} &Aring<br>";
            }

            $source = $this->get_source_organism($pdb);

            $basepairs = $this->count_basepairs($pdb);
            $nucleotides = $this->count_nucleotides($pdb);
            $bpnt = ( $nucleotides == 0 ) ? 0 : number_format($basepairs/$nucleotides, 4);

            $pdb_info = "<u>Title</u>: {$row->title}<br>" .
                        $resolution .
                        "<u>Method</u>: {$row->experimental_technique}<br>" .
                        "<u>Organism</u>: {$source}<br>" .
                        "<i>$nucleotides nucleotides, $basepairs basepairs, $bpnt basepairs/nucleotide</i><br><br>" .
                        'Explore in ' .
                        anchor_popup("$pdb_url$pdb", 'PDB') .
                        ',  ' .
                        anchor_popup("http://ndbserver.rutgers.edu/service/ndb/atlas/summary?searchTarget=$pdb", 'NDB') .
                        ' or ' .
                        anchor_popup("pdb/$pdb", 'BGSU RNA Site');
        } else {
            // check obsolete files
            $this->db->select('replaced_by')
                     ->from('pdb_obsolete')
                     ->where('pdb_obsolete_id', $pdb);
            $query = $this->db->get();

            if ( $query->num_rows() > 0 ) {
                $row = $query->row();

                if ($row->replaced_by == '') {
                    // pdb file is not replaced
                    $pdb_info = 'Structure ' . anchor_popup("$pdb_url$pdb", $pdb) . " was obsoleted.";
                } else {
                    // pdb file is replaced by one or more new pdbs
                    $replaced_by = explode(',', $row->replaced_by);
                    $new_urls = '';

                    foreach ($replaced_by as $new_file) {
                        $new_urls .= anchor_popup("$pdb_url$new_file", $new_file) . ' ';
                    }
                    
                    $pdb_info = "PDB file {$pdb} was replaced by {$new_urls}";
                }
            } else {
                $pdb_info = 'PDB file not found';
            }
        }

        return $pdb_info;
    }

    function save_loop_extraction_benchmark_annotation($contents)
    {
        try {
            for ($i=0; $i<count($contents)-1; $i+=2) {
                $data = array('manual_annotation' => $contents[$i+1]);
                $this->db->where('loop_benchmark_id', $contents[$i]);
                $this->db->update('loop_benchmark', $data);
            }
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }

    function get_dcc_data($s)
    {
        // detect what $s is in the future

        // assume nt_list for now
        $nt_ids = explode(',',$s);
        return $this->get_nt_json_dcc($nt_ids);
    }

    function get_nt_json_dcc($nt_ids)
    {
        $lengths = array('C' => 24, 'U' => 23, 'A' => 26, 'G' => 27);

        $list_ids = "'" . implode("','",$nt_ids) . "'";

        $sql_command = 'dcc_residues_id, sfcheck_correlation, sfcheck_correlation_side_chain, ' .
                            'sfcheck_real_space_R, sfcheck_real_space_R_side_chain, sfcheck_connect, ' .
                            'sfcheck_shift, sfcheck_shift_side_chain, sfcheck_density_index_main_chain, ' .
                            'sfcheck_density_index_side_chain, sfcheck_B_iso_main_chain, ' .
                            'sfcheck_B_iso_side_chain, mapman_correlation, mapman_real_space_R, ' .
                            'mapman_Biso_mean, mapman_occupancy_mean FROM __dcc_residues where dcc_residues_id IN (' . 
                            $list_ids . ') order by(FIELD(dcc_residues_id,' . $list_ids . '));';
        $this->db->select($sql_command, FALSE);
        $query = $this->db->get();
        //         $this->db->select()
        //                  ->from('dcc_residues')
        //                  ->where_in('dcc_residues_id',$nt_ids)
        //                  ->order_by($list_ids);
        //         $query = $this->db->get();

        $s = array();
        foreach ($query->result() as $row) {
            $parts   = explode('_',$row->dcc_residues_id);
            $nt_type = $parts[5];

            $fields = get_object_vars($row);
            unset($fields['dcc_residues_id']);

            foreach ($fields as $key => $value) {
                if (!array_key_exists($key,$s)) {
                    $s[$key] = '';
                }
            }

            foreach ($fields as $key => $value) {
                $s[$key] .= str_repeat($value . ' ', $lengths[$nt_type]);
            }
        }

        return json_encode($s);
    }

    function get_coordinates($s)
    {
        // 1S72_AU_1_0_30_U_
        // $is_nt_list = preg_match('/([a-z]|[A-Z]|[0-9]){4}_[a-zA-Z0-9]{2,3}_\d+_\d+_\d+_\[a-zA-Z]/',$s);
        $is_nt_list = substr_count($s,'_');
        if ($is_nt_list > 3) {
            echo $s;
            $nt_ids = explode(', ',$s);
            return $this->get_unit_id_coordinates($s);
        }

        $is_motif_id = preg_match('/(IL|HL|J3)_\d{5}\.\d+/',$s);
        if ($is_motif_id != 0) {
            return $this->get_exemplar_coordinates($s);
        }

        $is_loop_id  = preg_match('/(IL|HL|J3)_\w{4}_\d{3}/',$s);
        if ($is_loop_id != 0) {
            return $this->get_loop_coordinates($s);
        } else {
            return 'Input was not recognized';
        }
    }

    function get_nt_coordinates_approximate($nt_ids)
    {
        
        // get coordinates
        $this->db->select('coordinates')->from('unit_coordinates');

        $nts = explode(',', $nt_ids);

        foreach ($nts as $nt) {
            $this->db->or_like('unit_id', $nt, 'after');
        }
        
        $query = $this->db->get();
        
        if ($query->num_rows() == 0) { return 'Nucleotide coordinates not found'; }

        foreach ($query->result() as $row) {
            foreach ($row as $line) {
                $line= explode("\n", $line);
                foreach ($line as $line2) {
                    $model_1_pattern = '/ 1\s*$/';
                    // If model number is not 1, change to 1
                    if (!preg_match($model_1_pattern, $line2)) {
                        $search_pattern = '/([+-]?[0-9]+)\s*$/';
                        $line2 = preg_replace($search_pattern, '1', $line2);
                    }      
                    $lines_arr[] = ($line2);  
                }   
            }
        }

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('unit_coordinates')
                 ->join('unit_pairs_distances','unit_coordinates.unit_id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$nt_ids)
                 ->where_not_in('unit_id_1',$nt_ids);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            foreach ($row as $line) {
                $line= explode("\n", $line);
                foreach ($line as $line2) {
                    $model_2_pattern = '/ 2\s*$/';
                    // If model number is not 2, change to 2
                    if (!preg_match($model_2_pattern, $line2)) {
                        $search_pattern = '/([+-]?[0-9]+)\s*$/';
                        $line2 = preg_replace($search_pattern, '2', $line2);
                    }      
                    $lines_arr2[] = ($line2);  
                }   
            }
        }

        $headers_cif_fr3d = array (

            'data_view',
            '#', 
            'loop_',
            '_atom_site.group_PDB', 
            '_atom_site.id', 
            '_atom_site.type_symbol', 
            '_atom_site.label_atom_id', 
            '_atom_site.label_alt_id', 
            '_atom_site.label_comp_id', 
            '_atom_site.label_asym_id', 
            '_atom_site.label_entity_id', 
            '_atom_site.label_seq_id', 
            '_atom_site.pdbx_PDB_ins_code', 
            '_atom_site.Cartn_x', 
            '_atom_site.Cartn_y', 
            '_atom_site.Cartn_z', 
            '_atom_site.occupancy', 
            '_atom_site.B_iso_or_equiv', 
            '_atom_site.Cartn_x_esd', 
            '_atom_site.Cartn_y_esd', 
            '_atom_site.Cartn_z_esd', 
            '_atom_site.occupancy_esd', 
            '_atom_site.B_iso_or_equiv_esd', 
            '_atom_site.pdbx_formal_charge', 
            '_atom_site.auth_seq_id', 
            '_atom_site.auth_comp_id', 
            '_atom_site.auth_asym_id', 
            '_atom_site.auth_atom_id', 
            '_atom_site.pdbx_PDB_model_num' 

        );

        $footer = array('#');
     
        $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer, $headers_cif_fr3d, $lines_arr2, $footer);
        
        $final_result = '';

        foreach ($combine_result as $output) {
            $final_result .= $output . "\n";
        }

        return $final_result;

    }

    function get_nt_coordinates($nt_ids)
    {
          
        $headers_cif_fr3d = array (

            'data_view',
            '#', 
            'loop_',
            '_atom_site.group_PDB', 
            '_atom_site.id', 
            '_atom_site.type_symbol', 
            '_atom_site.label_atom_id', 
            '_atom_site.label_alt_id', 
            '_atom_site.label_comp_id', 
            '_atom_site.label_asym_id', 
            '_atom_site.label_entity_id', 
            '_atom_site.label_seq_id', 
            '_atom_site.pdbx_PDB_ins_code', 
            '_atom_site.Cartn_x', 
            '_atom_site.Cartn_y', 
            '_atom_site.Cartn_z', 
            '_atom_site.occupancy', 
            '_atom_site.B_iso_or_equiv', 
            '_atom_site.Cartn_x_esd', 
            '_atom_site.Cartn_y_esd', 
            '_atom_site.Cartn_z_esd', 
            '_atom_site.occupancy_esd', 
            '_atom_site.B_iso_or_equiv_esd', 
            '_atom_site.pdbx_formal_charge', 
            '_atom_site.auth_seq_id', 
            '_atom_site.auth_comp_id', 
            '_atom_site.auth_asym_id', 
            '_atom_site.auth_atom_id', 
            '_atom_site.pdbx_PDB_model_num' 

        );

        $footer = array('#');

        $nt_ids = explode(',', $nt_ids);

        // get their coordinates
        $this->db->select('coordinates')->from('unit_coordinates');
        $this->db->where_in('unit_id', $nt_ids);
        $this->db->_protect_identifiers = FALSE; // stop CI adding backticks

        // make SQL to return the correct order of results based on the where_in clause
        // example of query: SELECT coordinates FROM unit_coordinates WHERE unit_id IN ('2ZM5|1|C|A|31', '2ZM5|1|C|U|32')
        //                   ORDER BY FIELD (unit_id, '2ZM5|1|C|A|31', '2ZM5|1|C|U|32');
        $order = sprintf('FIELD(unit_id, %s)', "'" . implode("','", $nt_ids) . "'");
        $this->db->order_by($order);
        $this->db->_protect_identifiers = TRUE; // switch on again for security reasons
        $query = $this->db->get();

        if ($query->num_rows() == 0) { return 'Nucleotide coordinates not found'; }

        // get coordinates
        foreach ($query->result() as $row) {
            foreach ($row as $line) {
                $line= explode("\n", $line);
                foreach ($line as $line2) {
                    $model_1_pattern = '/ 1\s*$/';
                    // If model number is not 1, change to 1
                    if (!preg_match($model_1_pattern, $line2)) {
                        $search_pattern = '/([+-]?[0-9]+)\s*$/';
                        $line2 = preg_replace($search_pattern, '1', $line2);
                    }      
                    $lines_arr[] = ($line2);  
                }   
            }
        }

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('unit_coordinates')
                 ->join('unit_pairs_distances','unit_coordinates.unit_id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$nt_ids)
                 ->where_not_in('unit_id_1',$nt_ids);
        $query = $this->db->get();
      
        if ($query->num_rows() != 0) {

            foreach ($query->result() as $row) {
                foreach ($row as $line) {
                    $line= explode("\n", $line);
                    foreach ($line as $line2) {
                        $model_2_pattern = '/ 2\s*$/';
                        // If model number is not 2, change to 2
                        if (!preg_match($model_2_pattern, $line2)) {
                            $search_pattern = '/([+-]?[0-9]+)\s*$/';
                            $line2 = preg_replace($search_pattern, '2', $line2);
                        }      
                        $lines_arr2[] = ($line2);  
                    }   
                }
            }

            $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer, $headers_cif_fr3d, $lines_arr2, $footer);
        
            $final_result = '';

            foreach ($combine_result as $output) {
                $final_result .= $output . "\n";
            }

            return $final_result;
        }

        
        else {

            $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer);
        
            $final_result = '';

            foreach ($combine_result as $output) {
                $final_result .= $output . "\n";
            }

            return $final_result;
        }    
        
    }

    function get_loop_coordinates($loop_id)
    {
        
        $headers_cif_fr3d = array (

            'data_view',
            '#', 
            'loop_',
            '_atom_site.group_PDB', 
            '_atom_site.id', 
            '_atom_site.type_symbol', 
            '_atom_site.label_atom_id', 
            '_atom_site.label_alt_id', 
            '_atom_site.label_comp_id', 
            '_atom_site.label_asym_id', 
            '_atom_site.label_entity_id', 
            '_atom_site.label_seq_id', 
            '_atom_site.pdbx_PDB_ins_code', 
            '_atom_site.Cartn_x', 
            '_atom_site.Cartn_y', 
            '_atom_site.Cartn_z', 
            '_atom_site.occupancy', 
            '_atom_site.B_iso_or_equiv', 
            '_atom_site.Cartn_x_esd', 
            '_atom_site.Cartn_y_esd', 
            '_atom_site.Cartn_z_esd', 
            '_atom_site.occupancy_esd', 
            '_atom_site.B_iso_or_equiv_esd', 
            '_atom_site.pdbx_formal_charge', 
            '_atom_site.auth_seq_id', 
            '_atom_site.auth_comp_id', 
            '_atom_site.auth_asym_id', 
            '_atom_site.auth_atom_id', 
            '_atom_site.pdbx_PDB_model_num' 

        );

        $footer = array('#');

        // find all constituent nucleotides
        $this->db->select('unit_ids')
                 ->distinct()
                 ->from('loop_info')
                 ->where('loop_id',$loop_id);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id not found'; }

        foreach ($query->result() as $row) {
            $nt_ids = explode(',',$row->unit_ids);
        }

        // get their coordinates
        $this->db->select('coordinates')
                ->from('unit_coordinates')
                ->where_in('unit_id', $nt_ids);
        $query = $this->db->get();

        if ($query->num_rows() == 0) { return 'Loop coordinates not found'; }

        foreach ($query->result() as $row) {
            foreach ($row as $line) {
                $line= explode("\n", $line);
                foreach ($line as $line2) {
                    $model_1_pattern = '/ 1\s*$/';
                    // If model number is not 1, change to 1
                    if (!preg_match($model_1_pattern, $line2)) {
                        $search_pattern = '/([+-]?[0-9]+)\s*$/';
                        $line2 = preg_replace($search_pattern, '1', $line2);
                    }      
                    $lines_arr[] = ($line2);  
                }   
            }
        }

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('unit_coordinates')
                 ->join('unit_pairs_distances','unit_coordinates.unit_id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$nt_ids)
                 ->where_not_in('unit_id_1',$nt_ids);
        $query = $this->db->get();

        // test if atomic coordinates for neighboring atoms are available
        if ($query->num_rows() != 0) {

            foreach ($query->result() as $row) {
                foreach ($row as $line) {
                    $line= explode("\n", $line);
                    foreach ($line as $line2) {
                        $model_2_pattern = '/ 2\s*$/';
                        // If model number is not 2, change to 2
                        if (!preg_match($model_2_pattern, $line2)) {
                            $search_pattern = '/([+-]?[0-9]+)\s*$/';
                            $line2 = preg_replace($search_pattern, '2', $line2);
                        }      
                        $lines_arr2[] = ($line2);  
                    }   
                }
            }

            $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer, $headers_cif_fr3d, $lines_arr2, $footer);
        
            $final_result = '';

            foreach ($combine_result as $output) {
                $final_result .= $output . "\n";
            }

            return $final_result;

        }

        // else the atomic coordinates for neighboring atoms are not available
        else {

            $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer);
        
            $final_result = '';

            foreach ($combine_result as $output) {
                $final_result .= $output . "\n";
            }

            return $final_result;

        }

    }

    function get_loop_pair_coordinates($loop_pair)
    {
        // IL_1J5E_001:IL_1J5E_002
        $loop_ids = explode(':', $loop_pair);

        if ($loop_ids[0][0] == '@') {
            $loop_to_return = 1;
            $loop_ids[0] = substr($loop_ids[0], 1);
        } elseif ($loop_ids[1][0] == '@') {
            $loop_to_return = 2;
            $loop_ids[1] = substr($loop_ids[1], 1);
        } else {
            return 'Invalid loop pair';
        }

        // get coordinates from the alignment of loop1 and loop2
        if ( $loop_to_return == 1 ) {
            $nt_list = 'nt_list1';
        } else {
            $nt_list = 'nt_list2';
        }

        $this->db->select('loop_searches_id, loop_id_1, loop_id_2, disc, nt_list1, nt_list2')
                 ->from('loop_searches')
                 ->where('loop_id_1',$loop_ids[0])
                 ->where('loop_id_2',$loop_ids[1]);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            // result found, but the loops don't match
            if ($row['disc'] == -1) {
                // try the reverse case
                if ( $loop_to_return == 1 ) {
                    $nt_list = 'nt_list2';
                } else {
                    $nt_list = 'nt_list1';
                }

                $this->db->select('loop_searches_id, loop_id_1, loop_id_2, disc, nt_list1, nt_list2')
                         ->from('loop_searches')
                         ->where('loop_id_1',$loop_ids[1])
                         ->where('loop_id_2',$loop_ids[0]);
                $query = $this->db->get();

                if ($query->num_rows() > 0) {
                    $row = $query->row_array();
                } else {
                    return 'Loop pair not found';
                }
            }
        } else {
            return 'Loop pair not found';
        }

        $nt_ids = explode(',', $row[$nt_list]);

        return $this->get_nt_coordinates($nt_ids);
    }

    function get_exemplar_coordinates($motif_id)
    {
        // given a motif_id find the representative loop
        $this->db->select('loop_id')
                 ->from('ml_loop_order')
                 ->where('motif_id',$motif_id)
                 ->where('original_order',1);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            return 'No motif found';
        }
        $row = $query->row();

        return $this->get_loop_coordinates($row->loop_id);
    }

    function get_unit_id_coordinates($unit_ids)
    {

        $exploded = explode(',', $unit_ids);

        $this->db->select('unit_id')
                 ->distinct()
                 ->from('__pdb_unit_id_correspondence')
                 ->where_in('old_id', $exploded);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return 'No unit id correspondence found';
        } else {
            $nt_ids = array();
            foreach ($query->result() as $row) {
                $nt_ids[] = $row->unit_id;
                $comma_separated_nt_ids = implode(",", $nt_ids);
 
            }

        }

        return $this->get_nt_coordinates($comma_separated_nt_ids);
    }

}

/* End of file ajax_model.php */
/* Location: ./application/model/ajax_model.php */