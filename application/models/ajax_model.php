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

    function get_latest_ml_release_id()
    {
        $this->db->select('ml_release_id')
                 ->from('ml_releases')
                 ->order_by("date", "desc")
                 ->limit(1);

        $query = $this->db->get();
        $row = $query->row();
        $ml_release_id = $row->ml_release_id;

        return $ml_release_id;
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

    function get_pdb_info($inp,$cla="")
    {
        $pdb_url = "http://www.rcsb.org/pdb/explore/explore.do?structureId=";

        //  Is the input $pdb a pdb_id or an ife_id?
        //  Assess and set the variables accordingly.
        $pdb = substr($inp,0,4);
        $ife = ( strlen($inp) > 4) ? $inp : "foo";

        $this->db->select('pi.title')
                 ->select('pi.experimental_technique')
                 ->select('pi.resolution')
                 ->from('pdb_info AS pi')
                 ->where('pi.pdb_id', $pdb)
                 ->limit(1);
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $row = $query->row();

            if ( $ife == "foo" ) {
                $rsc = "foo";
            } else {
                if ( $cla ) {
                    $tmp = preg_replace('/^NR_[1-4]\.[05]_/','NR_all_',$cla);
                    $rsc = preg_replace('/^NR_20.0_/','NR_all_',$tmp);
                } else {
                    $this->db->select('cl.name')
                             ->from('nr_releases AS nr')
                             ->join('nr_chains AS ch','nr.nr_release_id = ch.nr_release_id')
                             ->join('nr_classes AS cl','ch.nr_class_id = cl.nr_class_id')
                             ->where('ch.ife_id', $ife)
                             ->where('cl.resolution', "all")
                             ->order_by('nr.index DESC')
                             ->limit(1);

                     $clquery = $this->db->get();
                     $clrow = $clquery->row();
                     $rsc = $clrow->name;
                }
            }

            // don't report resolution for nmr structures
            if (preg_match('/NMR/', $row->experimental_technique)) {
                $resolution = '';
            } else {
                $resolution = "<u>Resolution:</u> {$row->resolution} &Aring;<br>";
            }

            $source = $this->get_source_organism($pdb);

            $basepairs = $this->count_basepairs($pdb);
            $nucleotides = $this->count_nucleotides($pdb);
            $bpnt = ( $nucleotides == 0 ) ? 0 : number_format($basepairs/$nucleotides, 4);

            $pdb_info = "<u>Title</u>: {$row->title}<br/>" .
                        "<u>Method</u>: {$row->experimental_technique}<br/>" .
                        "<u>Organism</u>: <i>{$source}</i><br/>";

            //  Debugging info
            //$pdb_info .= "<hr/>" . 
            //             "<u>PDB</u>: [ $pdb ]<br/>" .
            //             "<u>IFE</u>: [ $ife ]<br/>";
            //$pdb_info .= "<hr/>" .
            //             "<u>class</u>: [ $rsc ]<br/>";

            //  Isolate nt/bp in preparation for removal.
            $pdb_info .= "<hr/>" . 
                         "<i>$nucleotides nucleotides, $basepairs basepairs, $bpnt basepairs/nucleotide</i><br/>";

            //  Separate the CQS logic, and conditionally display these values
            if ( $ife != "foo" ){
                $this->db->select('ic.ife_id')
                         ->select('nc.composite_quality_score')
                         ->select('ic.clashscore')
                         ->select('ic.average_rsr')
                         ->select('ic.average_rscc')
                         ->select('ic.percent_clash')
                         ->select('ic.rfree')
                         #->select('nc.fraction_unobserved')
                         ->select('nc.percent_observed')
                         ->from('ife_cqs AS ic')
                         ->join('nr_cqs AS nc','ic.ife_id = nc.ife_id')
                         ->where('ic.ife_id', $ife)
                         ->where('nc.nr_name', $rsc)
                         ->limit(1);
                $ifequery = $this->db->get();

                if ( $ifequery->num_rows() > 0 ) {
                    $row = $ifequery->row();

                    $cqs    = $row->composite_quality_score;
                    $arsr   = ( $row->average_rsr == 40 ) ? "not applicable; using 40 for CQS" : $row->average_rsr;
                    $pclash = $row->percent_clash;
                    $arscc  = ( $row->average_rscc == -1) ? "not applicable; using -1 for CQS" : $row->average_rscc;
                    $rfree  = ( $row->rfree == 1) ? "not applicable; using 1 for CQS" : $row->rfree;
                    #$fruno  = $row->fraction_unobserved;
                    $frobs   = $row->percent_observed;
                } else {
                    $cqs    = "not available";
                    $arsr   = "not available";
                    $pclash = "not available";
                    $arscc  = "not available";
                    $rfree  = "not available";
                    #$fruno  = "not available";
                    $frobs  = "not available";
                }

                $pdb_info .= "<hr/>" . 
                             "<u>Composite Quality Score (CQS)</u>: $cqs<br/>" .
                             $resolution .
                             "<u>Percent Clash</u>: $pclash %<br/>" .  
                             "<u>Fraction Observed</u>: $frobs<br/>" . 
                             "<u>Average RSR</u>: $arsr<br/>" .  
                             "<u>Average RSCC</u>: $arscc<br/>" .  
                             "<u>Rfree</u>: $rfree<br/>";
            }

            //  Add the structure website links.
            $pdb_info .= "<hr/>" . 
                         'Explore in ' .
                         anchor_popup("$pdb_url$pdb", 'PDB') .
                         ',  ' .
                         anchor_popup("http://ndbserver.rutgers.edu/service/ndb/atlas/summary?searchTarget=$pdb", 'NDB') .
                         ', or ' .
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

    function get_bulge_RSRZ($loop_id)
    {
        
        $this->db->select('unit_id')
                 ->from('ml_loop_positions')
                 ->where('loop_id',$loop_id)
                 ->where('ml_release_id',4.24)
                 ->order_by('position');
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id not found'; }

        $core_units = array();
        foreach ($query->result() as $row) {
            $core_units[] = $row->unit_id;
        }

        $this->db->select('unit_id')
                 ->from('loop_positions')
                 ->where('loop_id',$loop_id)
                 ->order_by('position');
        $query = $this->db->get();

        $complete_units = array();
        foreach ($query->result() as $row) {
            $complete_units[] = $row->unit_id;
        }
        
        $bulged_units = array_diff($complete_units, $core_units);
        $bulged_units = array_values($bulged_units);

        if(!empty($bulged_units)) {
            $this->db->select('unit_id, real_space_r_z_score')
                 ->from('unit_quality')
                 ->where_in('unit_id',$bulged_units);
            $query = $this->db->get();
        
            if ($query->num_rows() == 0) {
                return json_encode(json_decode ("{}"));
            } else {
                $RSRZ = $query->result();
                return json_encode($RSRZ);
            }
        } else {
            return json_encode(json_decode ("{}"));
        }

        
    }

    function get_seq_unit_mapping($ife) 
    {
        list($pdb, $model, $chain) = explode('|', $ife);
        
        
        $this->db->select('e1.unit_id')
                 ->select('e3.index')
                 ->select('e3.unit')
                 ->from('exp_seq_unit_mapping as e1')
                 ->join('exp_seq_chain_mapping as e2','e1.exp_seq_chain_mapping_id = e2.exp_seq_chain_mapping_id')
                 ->join('chain_info as c1','e2.chain_id = c1.chain_id')
                 ->join('exp_seq_position as e3','e1.exp_seq_position_id = e3.exp_seq_position_id')
                 ->where('c1.pdb_id ',$pdb)
                 ->where('c1.chain_name',$chain);
        $query = $this->db->get();
        
        $data = " ";
        foreach ($query->result_array() as $row) {
            $unit_id=$row['unit_id']; 
            # Add 1 because sequence index begins from 0 in the db
            $index=$row['index'] + 1;
            $unit=$row['unit'];
            if (is_null($unit_id)) {
                $relation = $pdb . "|Sequence|" . $chain . "|" . $unit . "|" . $index . " observed_as NULL";
                $data .= $relation . "</br>";
            } else {
                $relation = $pdb . "|Sequence|" . $chain . "|" . $unit . "|" . $index . " observed_as " . $unit_id;
                $data .= $relation . "</br>";
            }
        }
    
        return $data;

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

    function get_loop_coordinates_MotifAtlas($loop_id)
    {

        $lines_arr = array();
        $lines_arr2 = array();
        $lines_arr3 = array();

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

        $ml_release_id = $this->get_latest_ml_release_id();

        $this->db->select('unit_id')
                 ->from('ml_loop_positions')
                 ->where('loop_id',$loop_id)
                 ->where('ml_release_id', $ml_release_id)
                 ->order_by('position');
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id is not found'; }

        $core_units = array();
        foreach ($query->result() as $row) {
            $core_units[] = $row->unit_id;
        }

        $this->db->select('unit_id')
                 ->from('loop_positions')
                 ->where('loop_id',$loop_id)
                 ->order_by('position');
        $query = $this->db->get();

        $complete_units = array();
        foreach ($query->result() as $row) {
            $complete_units[] = $row->unit_id;
        }

        $bulged_units = array_diff($complete_units, $core_units);
        $bulged_units = array_values($bulged_units);


        if (empty($bulged_units)) {
           $has_bulges = False;
        } else {
           $has_bulges = True;

        }


        $this->db->select('coordinates')->from('unit_coordinates');
        $this->db->where_in('unit_id', $core_units);
        $this->db->_protect_identifiers = FALSE; // stop CI adding backticks

        // make SQL to return the correct order of results based on the where_in clause
        // example of query: SELECT coordinates FROM unit_coordinates WHERE unit_id IN ('2ZM5|1|C|A|31', '2ZM5|1|C|U|32')
        //                   ORDER BY FIELD (unit_id, '2ZM5|1|C|A|31', '2ZM5|1|C|U|32');
        $order = sprintf('FIELD(unit_id, %s)', "'" . implode("','", $core_units) . "'");
        $this->db->order_by($order);
        $this->db->_protect_identifiers = TRUE; // switch on again for security reasons
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


        if ($has_bulges == True) {
            
            // get the coordinates of bulged nucleotides
            $this->db->select('coordinates')
                ->from('unit_coordinates')
                ->where_in('unit_id', $bulged_units);
            $query = $this->db->get();

            foreach ($query->result() as $row) {
                foreach ($row as $line) {
                    $line= explode("\n", $line);
                    foreach ($line as $line2) {
                        $model_3_pattern = '/ 3\s*$/';
                        // If model number is not 2, change to 2
                        if (!preg_match($model_3_pattern, $line2)) {
                            $search_pattern = '/([+-]?[0-9]+)\s*$/';
                            $line2 = preg_replace($search_pattern, '3', $line2);
                        }      
                        $lines_arr3[] = ($line2);  
                    }   
                }
            }

        }

        // get the coordinates of neighboring residues
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('unit_coordinates')
                 ->join('unit_pairs_distances','unit_coordinates.unit_id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$complete_units)
                 ->where_not_in('unit_id_1',$complete_units);
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

        $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer, $headers_cif_fr3d, $lines_arr2, $footer, $headers_cif_fr3d, $lines_arr3, $footer);
        
        $final_result = '';

        foreach ($combine_result as $output) {
            $final_result .= $output . "\n";
        }

        
        return $final_result;        

    }


    function get_loop_coordinates($loop_id)
    {

        $lines_arr = array();
        $lines_arr2 = array();
        
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

        $this->db->select('unit_id')
                 ->from('loop_positions')
                 ->where('loop_id', $loop_id)
                 ->order_by('position');
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id is not found'; }

        $complete_units = array();
        foreach ($query->result() as $row) {
            $complete_units[] = $row->unit_id;
        }


        $this->db->select('coordinates')->from('unit_coordinates');
        $this->db->where_in('unit_id', $complete_units);
        $this->db->_protect_identifiers = FALSE; // stop CI adding backticks

        // make SQL to return the correct order of results based on the where_in clause
        // example of query: SELECT coordinates FROM unit_coordinates WHERE unit_id IN ('2ZM5|1|C|A|31', '2ZM5|1|C|U|32')
        //                   ORDER BY FIELD (unit_id, '2ZM5|1|C|A|31', '2ZM5|1|C|U|32');
        $order = sprintf('FIELD(unit_id, %s)', "'" . implode("','", $complete_units) . "'");
        $this->db->order_by($order);
        $this->db->_protect_identifiers = TRUE; // switch on again for security reasons
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

        // get the coordinates of neighboring residues
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('unit_coordinates')
                 ->join('unit_pairs_distances','unit_coordinates.unit_id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$complete_units)
                 ->where_not_in('unit_id_1',$complete_units);
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

        $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer, $headers_cif_fr3d, $lines_arr2, $footer);
        
        $final_result = '';

        foreach ($combine_result as $output) {
            $final_result .= $output . "\n";
        }

        
        return $final_result;
        

    }

    
    function get_coord_relative($unit_id)
    {
        $unit_id = explode(',', $unit_id);
        $core_units = array_slice($unit_id, 0, 10);
        $loop_units = array_slice($unit_id, 10);

        $lines_arr = array();
        $lines_arr2 = array();
        $lines_arr3 = array();
        
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

        $this->db->select('coordinates')->from('unit_coordinates');
        $this->db->where_in('unit_id', $loop_units);
        $this->db->_protect_identifiers = FALSE; // stop CI adding backticks

        // make SQL to return the correct order of results based on the where_in clause
        // example of query: SELECT coordinates FROM unit_coordinates WHERE unit_id IN ('2ZM5|1|C|A|31', '2ZM5|1|C|U|32')
        //                   ORDER BY FIELD (unit_id, '2ZM5|1|C|A|31', '2ZM5|1|C|U|32');
        $order = sprintf('FIELD(unit_id, %s)', "'" . implode("','", $loop_units) . "'");
        $this->db->order_by($order);
        $this->db->_protect_identifiers = TRUE; // switch on again for security reasons
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

        // get the coordinates of neighboring residues
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('unit_coordinates')
                 ->join('unit_pairs_distances','unit_coordinates.unit_id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$loop_units)
                 ->where_not_in('unit_id_1',$loop_units);
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

        $this->db->select('coordinates')->from('unit_coordinates');
        $this->db->where_in('unit_id', $core_units);
        $this->db->_protect_identifiers = FALSE; // stop CI adding backticks

        // make SQL to return the correct order of results based on the where_in clause
        // example of query: SELECT coordinates FROM unit_coordinates WHERE unit_id IN ('2ZM5|1|C|A|31', '2ZM5|1|C|U|32')
        //                   ORDER BY FIELD (unit_id, '2ZM5|1|C|A|31', '2ZM5|1|C|U|32');
        $order = sprintf('FIELD(unit_id, %s)', "'" . implode("','", $core_units) . "'");
        $this->db->order_by($order);
        $this->db->_protect_identifiers = TRUE; // switch on again for security reasons
        $query = $this->db->get();

        if ($query->num_rows() == 0) { return 'Core coordinates not found'; }

        foreach ($query->result() as $row) {
            foreach ($row as $line) {
                $line= explode("\n", $line);
                foreach ($line as $line2) {
                    $model_3_pattern = '/ 3\s*$/';
                    // If model number is not 3, change to 3
                    if (!preg_match($model_3_pattern, $line2)) {
                        $search_pattern = '/([+-]?[0-9]+)\s*$/';
                        $line2 = preg_replace($search_pattern, '3', $line2);
                    }      
                    $lines_arr3[] = ($line2);  
                }   
            }
        }

        $combine_result = array_merge($headers_cif_fr3d, $lines_arr, $footer, $headers_cif_fr3d, $lines_arr2, $footer, $headers_cif_fr3d, $lines_arr3, $footer);
        
        $final_result = '';

        foreach ($combine_result as $output) {
            $final_result .= $output . "\n";
        }

        return $final_result;
        
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

//// New codes dealing with obtaining RSRZ/RSR values for loops and unit_ids ///

    function get_loop_RSR($loop_id)
    {

        // find all constituent unit IDs of the loop
        $this->db->select('unit_ids')
                 ->from('loop_info')
                 ->where_in('loop_id',$loop_id);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id not found'; }

        foreach ($query->result() as $row) {
            $total_unit_ids = explode(',', $row->unit_ids);
        }

        $this->db->select('unit_id, real_space_r')
                 ->from('unit_quality')
                 ->where_in('unit_id',$total_unit_ids);
        $query = $this->db->get();
        
        if ($query->num_rows() == 0) {
            return 'No RSR correspondence found';
        } else {
            $RSR = $query->result();
        }

        return json_encode($RSR);

    }

    function get_exemplar_RSR($motif_id)
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

        return $this->get_loop_RSR($row->loop_id);
    }

    function get_loop_RSRZ($loop_id)
    {

        // find all constituent unit IDs of the loop
        $this->db->select('unit_ids')
                 ->from('loop_info')
                 ->where_in('loop_id',$loop_id);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id not found'; }

        foreach ($query->result() as $row) {
            $total_unit_ids = explode(',', $row->unit_ids);
        }

        $this->db->select('unit_id, real_space_r_z_score')
                 ->from('unit_quality')
                 ->where_in('unit_id',$total_unit_ids);
        $query = $this->db->get();
        
        if ($query->num_rows() == 0) {
            return 'No RSRZ correspondence is found';
        } else {
            $RSRZ = $query->result();
        }

        return json_encode($RSRZ);

    }

    function get_exemplar_RSRZ($motif_id)
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

        return $this->get_loop_RSRZ($row->loop_id);
    }

    function get_unit_id_RSR($unit_ids)
    {

        $exploded = explode(',', $unit_ids);

        $this->db->select('unit_id, real_space_r')
                 ->from('unit_quality')
                 ->where_in('unit_id',$exploded);
        $query = $this->db->get();
        
        if ($query->num_rows() == 0) {
            return 'No RSR correspondence found';
        } else {
            $RSR = $query->result();
 
        }

        return json_encode($RSR);
    }

    function get_unit_id_RSRZ($unit_ids)
    {

        $exploded = explode(',', $unit_ids);

        $this->db->select('unit_id, real_space_r_z_score')
                 ->from('unit_quality')
                 ->where_in('unit_id',$exploded);
        $query = $this->db->get();
        
        if ($query->num_rows() == 0) {
            return 'No RSRZ correspondence is found';
        } else {
            $RSRZ = $query->result();
        }

        return json_encode($RSRZ);
    }

}

/* End of file ajax_model.php */
/* Location: ./application/model/ajax_model.php */
