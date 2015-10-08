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
        return $this->db->select('id')
                        ->from('pdb_coordinates')
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
        $this->db->select()
                 ->from('pdb_info')
                 ->where('structureId', $pdb_id)
                 ->like('entityMacromoleculeType', 'RNA')
                 ->where("chainLength = (SELECT max(chainLength) FROM pdb_info WHERE structureId ='$pdb_id' AND entityMacromoleculeType LIKE '%RNA%')");
        $query = $this->db->get()->result();
        return $query[0]->source;
    }

    function get_pdb_info($pdb)
    {
        $this->db->select()
                 ->from('pdb_info')
                 ->where('structureId', $pdb)
                 ->limit(1);
        $query = $this->db->get();
        if ( $query->num_rows() > 0 ) {
            $row = $query->row();

            // don't report resolution for nmr structures
            if (preg_match('/NMR/', $row->experimentalTechnique)) {
                $resolution = '';
            } else {
                $resolution = "<u>Resolution:</u> {$row->resolution} &Aring<br>";
            }

            $source = $this->get_source_organism($pdb);

            $basepairs = $this->count_basepairs($pdb);
            $nucleotides = $this->count_nucleotides($pdb);
            $bpnt = number_format($basepairs/$nucleotides, 4);

            $pdb_info = "<u>Title</u>: {$row->structureTitle}<br>" .
                        $resolution .
                        "<u>Method</u>: {$row->experimentalTechnique}<br>" .
                        "<u>Organism</u>: {$source}<br>" .
                        "<i>$nucleotides nucleotides, $basepairs basepairs, $bpnt basepairs/nucleotide</i><br><br>" .
                        'Explore in ' .
                        anchor_popup("http://www.pdb.org/pdb/explore/explore.do?structureId=$pdb", 'PDB') .
                        ',  ' .
                        anchor_popup("http://ndbserver.rutgers.edu/service/ndb/atlas/summary?searchTarget=$pdb", 'NDB') .
                        ' or ' .
                        anchor_popup("pdb/$pdb", 'RNA 3D Hub');
        } else {
            // check obsolete files
            $this->db->select()
                     ->from('pdb_obsolete')
                     ->where('pdb_obsolete_id', $pdb);
            $query = $this->db->get();
            if ( $query->num_rows() > 0 ) {
                $row = $query->row();

                if ($row->replaced_by == '') {
                    // pdb file is not replaced
                    $pdb_info = 'Structure ' . anchor_popup("http://www.pdb.org/pdb/explore/explore.do?structureId=$pdb", $pdb) . " was obsoleted.";
                } else {
                    // pdb file is replaced by one or more new pdbs
                    $replaced_by = explode(',', $row->replaced_by);
                    $new_urls = '';
                    foreach ($replaced_by as $new_file) {
                        $new_urls .= anchor_popup("http://www.pdb.org/pdb/explore/explore.do?structureId=$new_file", $new_file) . ' ';
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
                $this->db->where('id', $contents[$i]);
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

        $sql_command = '* FROM dcc_residues where dcc_residues_id IN ('. $list_ids .') order by(FIELD(id,'.$list_ids.'));';
        $this->db->select($sql_command, FALSE);
        $query = $this->db->get();
        //         $this->db->select()
        //                  ->from('dcc_residues')
        //                  ->where_in('dcc_residues_id',$nt_ids)
        //                  ->order_by($list_ids);
        //         $query = $this->db->get();

        $s = array();
        foreach ($query->result() as $row) {
            $parts   = explode('_',$row->id);
            $nt_type = $parts[5];

            $fields = get_object_vars($row);
            unset($fields['id']);

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
            $nt_ids = explode(',',$s);
            return $this->get_nt_coordinates($nt_ids);
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
        $this->db->select('coordinates')->from('pdb_coordinates');

        $nts = explode(',', $nt_ids);
        foreach ($nts as $nt) {
            $this->db->or_like('id', $nt, 'after');
        }
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop coordinates not found'; }

        $final_result = "MODEL     1\n";
        foreach ($query->result() as $row) {
            $final_result .= $row->coordinates . "\n";
        }
        $final_result .= "ENDMDL\n";

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('pdb_coordinates')
                 ->join('unit_pairs_distances','pdb_coordinates.id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$nt_ids)
                 ->where_not_in('unit_id_1',$nt_ids);
        $query = $this->db->get();

         $final_result .= "MODEL     2\n";
         if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $final_result .= $row->coordinates . "\n";
            }
         }
         $final_result .= "ENDMDL";

        return $final_result;
    }

    function get_nt_coordinates($nt_ids)
    {
        $imploded = "'" . implode("','",$nt_ids) . "'";

        $query_string = "* from pdb_coordinates where id IN ($imploded) ORDER BY FIELD(id, $imploded);";
        $this->db->select($query_string, FALSE);
        $query = $this->db->get();

        if ($query->num_rows() == 0) { return 'Loop coordinates not found'; }

        $final_result = "MODEL     1\n";
        foreach ($query->result() as $row) {
            $final_result .= $row->coordinates . "\n";
        }
        $final_result .= "ENDMDL\n";

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('pdb_coordinates')
                 ->join('unit_pairs_distances','pdb_coordinates.id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$nt_ids)
                 ->where_not_in('unit_id_1',$nt_ids);
        $query = $this->db->get();

         $final_result .= "MODEL     2\n";
         if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $final_result .= $row->coordinates . "\n";
            }
         }
         $final_result .= "ENDMDL";

        return $final_result;
    }

    function get_loop_coordinates($loop_id)
    {
        // find all constituent nucleotides
        $this->db->select('nt_ids')
                 ->distinct()
                 ->from('loop_info')
                 ->where('loop_id',$loop_id);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop id not found'; }

        foreach ($query->result() as $row) {
            $nt_ids = explode(',',$row->nt_ids);
        }

        // get their coordinates
        $this->db->select('coordinates')
                 ->from('pdb_coordinates')
                 ->where_in('id',$nt_ids);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'Loop coordinates not found'; }

        $final_result = "MODEL     1\n";
        foreach ($query->result() as $row) {
            $final_result .= $row->coordinates . "\n";
        }
        $final_result .= "ENDMDL\n";

        // get neighborhood
        $this->db->select('coordinates')
                 ->distinct()
                 ->from('pdb_coordinates')
                 ->join('unit_pairs_distances','pdb_coordinates.id = unit_pairs_distances.unit_id_1')
                 ->where_in('unit_id_2',$nt_ids)
                 ->where_not_in('unit_id_1',$nt_ids);
        $query = $this->db->get();

        $final_result .= "MODEL     2\n";
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $final_result .= $row->coordinates . "\n";
            }
        }
        $final_result .= "ENDMDL";

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
        $this->db->select()
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
                $this->db->select()
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
        $imploded = "'" . implode("','",$exploded) . "'";

        $query_string = "distinct(old_id) from pdb_unit_id_correspondence where unit_id IN ($imploded) ORDER BY FIELD(unit_id, $imploded);";
        $this->db->select($query_string, FALSE);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return 'No unit id correspondence found';
        } else {
            $nt_ids = array();
            foreach ($query->result() as $row) {
                $nt_ids[] = $row->old_id;
            }
        }

        return $this->get_nt_coordinates($nt_ids);
    }

}

/* End of file ajax_model.php */
/* Location: ./application/model/ajax_model.php */
