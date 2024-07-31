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
                 ->like('entity_macromolecule_type', 'polyribonucleotide')
                 ->where("chain_length = (SELECT max(chain_length) FROM chain_info WHERE pdb_id ='$pdb_id' AND (entity_macromolecule_type LIKE '%polyribonucleotide%' OR entity_macromolecule_type LIKE '%RNA%'))");
        $query = $this->db->get()->result();

        return $query[0]->source;
    }

    function get_pdb_info($inp,$cla="")
    {
        $pdb_url = "https://www.rcsb.org/structure/";

        //  Is the input $pdb a pdb_id or an ife_id?
        //  Assess and set the variables accordingly.
        $pdb = substr($inp,0,4);
        $ife = ( strlen($inp) > 4) ? $inp : "foo";
        $ife = str_replace('+ ','+',$ife);
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
                    // $this->db->select('cl.name')
                    //          ->from('nr_releases AS nr')
                    //          ->join('nr_chains AS ch','nr.nr_release_id = ch.nr_release_id')
                    //          ->join('nr_classes AS cl','ch.nr_class_id = cl.nr_class_id')
                    //          ->where('ch.ife_id', $ife)
                    //          ->where('cl.resolution', "all")
                    //          ->order_by('nr.index DESC')
                    //          ->limit(1);
                    $this->db->select('cl.name')
                                ->from('nr_classes AS cl')
                                ->join('nr_releases AS nr','nr.nr_release_id = cl.nr_release_id')
                                ->join('nr_class_rank AS ch','ch.nr_class_name = cl.name')
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
                        //  ->where('nc.nr_name', $rsc)
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
                         anchor_popup("https://www.nakb.org/atlas=$pdb", 'NAKB') .
                         ', or ' .
                         anchor_popup("pdb/$pdb", 'RNA 3D Hub');
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

    function get_chain_sequence($pdb, $chain)
    {
        $this->db->select('sequence')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb)
                 ->where('chain_name', $chain);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return 'No sequence was found for the given id'; }

        $row = $query->row_array(1);

        return $row['sequence'];
    }

    function get_sequence_basepairs($pdb, $chain, $nested)
    {

        $query_str = "
        select A.index as seq_id1, concat(A.number, coalesce(A.ins_code, '')) as 3d_id1, A.nucleotide as nt1, f_lwbp as bp, C.index as seq_id2, C.nucleotide as nt2, concat(C.number, coalesce(C.ins_code, '')) as 3d_id2, B.f_crossing as crossing
        from
        (
            select t3.index + 1 as `index`, t3.`normalized_unit` as `nucleotide`, t2.unit_id, t1.number, t1.ins_code
            from unit_info t1, exp_seq_unit_mapping t2, exp_seq_position t3
            where t1.pdb_id = " . $this->db->escape($pdb) . "
            and t1.chain = " . $this->db->escape($chain) . "
            and t1.model = 1
            and t1.unit_id = t2.unit_id
            and t2.exp_seq_position_id = t3.exp_seq_position_id
            and (t1.alt_id = 'A' OR t1.alt_id is null)
        ) as A
        JOIN
        (
            select unit_id_1, unit_id_2, f_lwbp, t10.pdb_id, f_crossing
            from unit_pairs_interactions t10, unit_info t11, unit_info t12
            where
            t10.pdb_id =" . $this->db->escape($pdb) . "
            and f_lwbp is not null
            and t10.unit_id_1 = t11.unit_id
            and t10.unit_id_2 = t12.unit_id
            and t11.number < t12.number
            and t11.chain = " . $this->db->escape($chain) . "
            and t12.chain = " . $this->db->escape($chain) . "
        ) as B
        JOIN
        (
            select t3.index + 1 as `index`, t3.`normalized_unit` as `nucleotide`, t2.unit_id, t1.number, t1.ins_code
            from unit_info t1, exp_seq_unit_mapping t2, exp_seq_position t3
            where
            t1.pdb_id = " . $this->db->escape($pdb) . "
            and t1.chain = " . $this->db->escape($chain) . "
            and t1.model = 1
            and t1.unit_id = t2.unit_id
            and t2.exp_seq_position_id = t3.exp_seq_position_id
            and (t1.alt_id = 'A' OR t1.alt_id is null)
        ) as C
        on A.unit_id = B.unit_id_1
        and B.unit_id_2 = C.unit_id
        order by B.pdb_id, A.index";

        $query = $this->db->query($query_str);
        $nested_bps = array();

        foreach ($query->result_array() as $row)
        {
            if ($nested == 'False' or $row['crossing'] == 0) {
                $nested_bps[] = $row;
            }
        }

        $sequence = $this->get_chain_sequence($pdb, $chain);

        $data = array(
            "pdb_id" => $pdb,
            "chain_id" => $chain,
            "sequence" => $sequence,
            "annotations" => $nested_bps
        );

        $myJSON = json_encode($data);

        return $myJSON;

    }

    function get_chain_sequence_basepairs($pdb, $chain, $nested)
    {

        // A refers to a query of the unit_info table.
        // unit_info.index is seq_id1
        // unit_info.number and ins_code is combined to become 3d_id1
        // unit_info.nucleotide no longer exists ... probably should be unit ... will be nt1
        // Within query A,
        //   t1 refers to unit_info, t2 refers to exp_seq_unit_mapping, t3 refers to exp_seq_position
        //
        // B refers to a query of unit_pairs_interactions
        // Within query B,
        //   t10 refers to unit_pairs_interactions, t11 refers to unit_info, t12 refers to unit_info
        //
        // unit_pairs_interactions.f_lwbp is bp
        // C refers to a query of unit_info table, another view of it to get seq_id2, 3d_id2, nt2

        $query_str = "
        select A.index as seq_id1, concat(A.number, coalesce(A.ins_code, '')) as 3d_id1, A.nucleotide as nt1, A.unit1, B.annotation as bp, C.index as seq_id2, C.nucleotide as nt2, C.unit2, concat(C.number, coalesce(C.ins_code, '')) as 3d_id2, B.crossing
        from
        (
            select t3.index + 1 as `index`, t3.`normalized_unit` as `nucleotide`, t2.unit_id, t1.number, t1.ins_code, t1.unit as `unit1`
            from unit_info t1, exp_seq_unit_mapping t2, exp_seq_position t3
            where t1.pdb_id = " . $this->db->escape($pdb) . "
            and t1.chain = " . $this->db->escape($chain) . "
            and t1.model = 1
            and t1.unit_id = t2.unit_id
            and t2.exp_seq_position_id = t3.exp_seq_position_id
            and (t1.alt_id = 'A' OR t1.alt_id is null)
        ) as A
        JOIN
        (
            select unit_id_1, unit_id_2, annotation, t10.pdb_id, crossing
            from pair_annotations t10, unit_info t11, unit_info t12
            where
            t10.pdb_id =" . $this->db->escape($pdb) . "
            and annotation is not null
            and category = 'basepair'
            and t10.unit_id_1 = t11.unit_id
            and t10.unit_id_2 = t12.unit_id
            and t11.number < t12.number
            and t11.chain = " . $this->db->escape($chain) . "
            and t12.chain = " . $this->db->escape($chain) . "
        ) as B
        JOIN
        (
            select t3.index + 1 as `index`, t3.`normalized_unit` as `nucleotide`, t2.unit_id, t1.number, t1.ins_code, t1.unit as `unit2`
            from unit_info t1, exp_seq_unit_mapping t2, exp_seq_position t3
            where
            t1.pdb_id = " . $this->db->escape($pdb) . "
            and t1.chain = " . $this->db->escape($chain) . "
            and t1.model = 1
            and t1.unit_id = t2.unit_id
            and t2.exp_seq_position_id = t3.exp_seq_position_id
            and (t1.alt_id = 'A' OR t1.alt_id is null)
        ) as C
        on A.unit_id = B.unit_id_1
        and B.unit_id_2 = C.unit_id
        order by B.pdb_id, A.index";

        $query = $this->db->query($query_str);

        $LW = array('cWW','tWW','cWH','cHW','tWH','tHW','cWS','cSW','tWS','tSW','cHH','tHH','cHS','cSH','tHS','tSH','cSS','tSS');

        $nested_bps = array();
        foreach ($query->result_array() as $row)
        {
            if ($nested == 'False' or $row['crossing'] == 0) {
                if (in_array($row['bp'], $LW)) {
                    $nested_bps[] = $row;
                }
            }
        }

        $sequence = $this->get_chain_sequence($pdb, $chain);

        $data = array(
            "pdb_id" => $pdb,
            "chain_id" => $chain,
            "sequence" => $sequence,
            "annotations" => $nested_bps
        );

        $myJSON = json_encode($data);

        return $myJSON;

    }

    function get_bulge_RSRZ($loop_id)
    {
        // This code is tied to a specific loop release in November 2022.
        // That looks like a problem.

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
        // Tests:
        // http://rna.bgsu.edu/rna3dhub/rest/SeqtoUnitMapping?ife=1S72|1|0  NULL values at start, middle, end
        // http://rna.bgsu.edu/rna3dhub/rest/SeqtoUnitMapping?ife=2N1Q|5|A  Many models
        // http://rna.bgsu.edu/rna3dhub/rest/SeqtoUnitMapping?ife=6E7L|1|A  Symmetry operators
        // http://rna.bgsu.edu/rna3dhub/rest/SeqtoUnitMapping?ife=1FJG|1|A  Alternate ids

        $fields = explode('|', $ife);

        if (count($fields) < 3) {
            return "Please specify PDB, model, and chain like 1S72|1|0.  One chain at a time.";
        }

        $pdb = $fields[0];
        $chain = $fields[2];

        $this->db->select('e2.exp_seq_chain_mapping_id')
                 ->select('e2.exp_seq_id')
                 ->from('exp_seq_chain_mapping as e2')
                 ->join('chain_info as c1','e2.chain_id = c1.chain_id')
                 ->where('c1.pdb_id',$pdb)
                 ->where('c1.chain_name',$chain)
                 ->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 0) { return "No matching PDB id and chain for " . $ife; }

        $escmi = $query->row()->exp_seq_chain_mapping_id;
        $esi = $query->row()->exp_seq_id;

        $data = "";

        // query to get all of the sequence positions
        $this->db->select('e3.index')
                 ->select('e3.unit')
                 ->from('exp_seq_position as e3')
                 ->where('e3.exp_seq_id',$esi)
                 ->order_by('e3.index');
        $query = $this->db->get();

        if ($query->num_rows() == 0) { return "No matching PDB id and chain for " . $ife; }

        // map indices to units to be able to fill in the unresolved ones when needed
        $max_unresolved_index = $query->num_rows();
        $index_to_unit = array();

        foreach ($query->result_array() as $row) {
            $index_to_unit[$row['index']] = $row['unit'];
        }

        // query to map all sequence positions that are resolved
        // I tried and tried to get this query to also get the unresolved nucleotides, but it doesn't work.
        // The where clauses just negate the effect of the left join.
        // It was easier when the exp_seq_unit_mapping table had NULL values in it, but those were error prone
        // and were removed in February 2023.
        $this->db->select('e1.unit_id')
                 ->select('e3.index')
                 ->select('e3.unit')
                 ->from('exp_seq_position as e3')
                 ->join('exp_seq_unit_mapping as e1','e3.exp_seq_position_id = e1.exp_seq_position_id')
                 ->join('unit_info as ui','ui.unit_id = e1.unit_id')
                 ->where('e3.exp_seq_id',$esi)
                 ->where('e1.exp_seq_chain_mapping_id',$escmi)
                 ->order_by('e3.index, ui.model, ui.alt_id, ui.sym_op');
        $query = $this->db->get();

        $unresolved_index = 0;

        foreach ($query->result_array() as $row) {
            $index=$row['index'];

            // fill in unresolved nucleotides
            while ($unresolved_index < $index) {
                $relation = $pdb . "|sequence|" . $chain . "|" . $index_to_unit[$unresolved_index] . "|" . ($unresolved_index+1) . " observed_as NULL";
                $data .= $relation . "</br>";
                $unresolved_index = $unresolved_index + 1;
            }

            $unresolved_index = $unresolved_index + 1;

            // Add 1 to $index because sequence index begins from 0 in the database
            //$data .= $index . " " . $unit_id . " testing</br>";

            $unit_id=$row['unit_id'];
            $unit=$row['unit'];

            $relation = $pdb . "|sequence|" . $chain . "|" . $unit . "|" . ($index+1) . " observed_as " . $unit_id;
            $data .= $relation . "</br>";
        }

        // fill in unresolved nucleotides at the end of the chain
        while ($unresolved_index < $max_unresolved_index) {
            $relation = $pdb . "|sequence|" . $chain . "|" . $index_to_unit[$unresolved_index] . "|" . ($unresolved_index+1) . " observed_as NULL";
            $data .= $relation . "</br>";
            $unresolved_index = $unresolved_index + 1;
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
        // This function uses old unit ids with _ symbols
        // It really should not be needed anymore, as of November 2022

        // 1S72_AU_1_0_30_U_
        // $is_nt_list = preg_match('/([a-z]|[A-Z]|[0-9]){4}_[a-zA-Z0-9]{2,3}_\d+_\d+_\d+_\[a-zA-Z]/',$s);

        echo 'Starting get_coordinates';
        echo $s;

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

    function get_nt_coordinates_approximate($nt_ids,$distance=10)
    {
        // Used in loops_benchmark_view.php, which is old
        // Seems to simply get nearby units.
        // Replacing with new neighborhood function, hope for the best.

        return $this->get_unit_and_neighbor_coordinates($nt_ids,$distance);
    }


    function get_unit_coordinates($nt_ids)
    {
        // get the coordinates of the listed units

        $this->db->select('coordinates')->from('unit_coordinates');
        $this->db->where_in('unit_coordinates.unit_id', $nt_ids);

        // make SQL return the results in the same order as $nt_ids
        // example of query: SELECT coordinates FROM unit_coordinates WHERE unit_id IN ('2ZM5|1|C|A|31', '2ZM5|1|C|U|32')
        //                   ORDER BY FIELD (unit_id, '2ZM5|1|C|A|31', '2ZM5|1|C|U|32');

        $this->db->_protect_identifiers = FALSE; // stop CI adding backticks
        $order = sprintf('FIELD(unit_coordinates.unit_id, %s)', "'" . implode("','", $nt_ids) . "'");
        $this->db->order_by($order);
        $this->db->_protect_identifiers = TRUE; // switch on again for security reasons

        $query = $this->db->get();

        if ($query->num_rows() == 0) { return False; }

        return $query;
    }


    function change_model_num($query, $model_num)
    {
        // loop over query results to set the model number as desired
        // Return an array of lines in cif format

        $lines_arr = array();

        foreach ($query->result() as $row) {
            foreach ($row as $line) {
                $line = explode("\n", $line);
                foreach ($line as $line2) {
                    // $model_1_pattern = '/ 1\s*$/';
                    $model_1_pattern = '/ ' . $model_num . '\s*$/';
                    // If model number is not 1, change to 1
                    if (!preg_match($model_1_pattern, $line2)) {
                        $search_pattern = '/([+-]?[0-9]+)\s*$/';
                        $line2 = preg_replace($search_pattern, $model_num, $line2);
                    }
                    $lines_arr[] = ($line2);
                }
            }
        }

        return $lines_arr;
    }


    function get_xyz_coordinates($unit_ids, $pdb_id)
    {
        // retrieve the x,y,z coordinates of all centers of all units in $unit_ids
        // for nucleotides, that will include the base center, glycosidic atom, sugar center, phosphate center
        // for amino acids, that will include the functional group center and backbone center

        $this->db->select('x, y, z')
                 ->distinct()
                 ->from('unit_centers')
                 ->where('pdb_id', $pdb_id)         // this probably speeds up the query
                 ->where_in('unit_id', $unit_ids);
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return False; }

        $given_x = array();
        $given_y = array();
        $given_z = array();

        foreach ($query->result_array() as $row) {
            $given_x[] = floatval($row['x']);
            $given_y[] = floatval($row['y']);
            $given_z[] = floatval($row['z']);
        }

        $centers_coord = array($given_x, $given_y, $given_z);
        return $centers_coord;
    }

    function get_xyz_coordinates_between_limits($pdb_id, $model_num, $coord_limits)
    {
        // Find all units in the given PDB file whose center is within the given limits.
        // In the future, if we want to, we could
        // only find residues where the base center and the amino acid functional group center
        // is within the limits
        //$center_type = array('base', 'aa_fg');

        $unit_coord_arr = array();
        $this->db->select('unit_id, x, y, z, name')
                 ->from('unit_centers')
                 ->where('pdb_id', $pdb_id)
                 ->where('x >=', $coord_limits[0])
                 ->where('x <=', $coord_limits[1])
                 ->where('y >=', $coord_limits[2])
                 ->where('y <=', $coord_limits[3])
                 ->where('z >=', $coord_limits[4])
                 ->where('z <=', $coord_limits[5]);

//                 ->where_in('name', $center_type);

        $query = $this->db->get();
        //if ($query->num_rows() == 0) { return False; }

        $unit_coord_arr = array();
        foreach ($query->result_array() as $row) {
            $mn = explode('|',$row['unit_id']);   // extract model number
            if ($mn[1] == $model_num) {           // neighbors from same model
                $unit_coord = array(
                    "unit_id" => $row['unit_id'],
                    "x" => floatval($row['x']),
                    "y" => floatval($row['y']),
                    "z" => floatval($row['z']),
                    "name" => $row['name']
                );
                $unit_coord_arr[] = $unit_coord;
            }
        }

        return $unit_coord_arr;
    }


    function filter_neighboring_residues($centers_coord, $potential_neighboring_units, $distance, $nt_ids)
    {
        // use the x,y,z coordinates in $centers_coord to check if x,y,z coordinates in query results
        // $potential_neighboring_units are within $distance, avoiding those in $nt_ids

        $output_nt_ids = array();
        //$output_distance_list = array();
        $distance_squared = $distance * $distance;

        foreach($potential_neighboring_units as $unit_arr) {
            // if unit id of this potential unit is in the query, don't check distances
            if (!in_array($unit_arr['unit_id'], $nt_ids)) {
                // if the unit id of this potential unit is already in the output, don't check distances
                // That misses the possibility of finding an even closer match with a different center.
                if (!in_array($unit_arr['unit_id'], $output_nt_ids)) {

                    // keep track of minimum distance of potential unit $unit_arr to query x,y,z locations
                    $d2min = 10*$distance_squared;

                    // loop over query x,y,z locations
                    for ($i=0; $i < count($centers_coord[0]); $i++) {
                        // calculate squared distance, keep track of minimum squared distance
                        $d2 = pow(($unit_arr['x'] - $centers_coord[0][$i]), 2)  + pow(($unit_arr['y'] - $centers_coord[1][$i]), 2) + pow(($unit_arr['z'] - $centers_coord[2][$i]), 2);
                        if ($d2 < $d2min) {
                            $d2min = $d2;
                        }
                    }

                    if ($d2min < $distance_squared){
                        $output_nt_ids[] = $unit_arr['unit_id'];
                        // currently not returning $output_distance_list, so don't compute it
                        //$output_distance_list[] = sqrt($d2min);
                    }
                }
            }
        }

        // currently not returning $output_distance_list

        //if (count($output_nt_ids) == 0) { return False; }

        return $output_nt_ids;
    }


    function get_neighboring_units($unit_ids,$distance=10)
    {
        // Starting with $unit_ids, find the x,y,z coordinates of their centers,
        // expand by $distance to a rectangular box around them,
        // then find other units within that box,
        // then filter down to ones that are within $distance of one of the centers in $unit_ids

        // get the pdb id and model number from the first unit id
        $fields = explode('|',$unit_ids[0]);
        $pdb_id = $fields[0];
        $model_num = $fields[1];

        // Get all centers of $unit_ids, including base, sugar, phosphate, aa_fg
        $centers_xyz_coord = $this->get_xyz_coordinates($unit_ids, $pdb_id);

        //echo "Centers returned";
        //echo count($centers_xyz_coord);

        // If no centers are really returned, don't look for neighbors
        if (count($centers_xyz_coord) < 3) { return array(); }

        // Find the maxima and minima and expand by $distance
        $x_min = min($centers_xyz_coord[0]) - $distance;
        $x_max = max($centers_xyz_coord[0]) + $distance;
        $y_min = min($centers_xyz_coord[1]) - $distance;
        $y_max = max($centers_xyz_coord[1]) + $distance;
        $z_min = min($centers_xyz_coord[2]) - $distance;
        $z_max = max($centers_xyz_coord[2]) + $distance;

        // store the limits in an array
        $coord_limits = array($x_min, $x_max, $y_min, $y_max, $z_min, $z_max);

        // query to find all units whose x, y, z coordinates are between the limits
        $potential_neighboring_units = $this->get_xyz_coordinates_between_limits($pdb_id, $model_num, $coord_limits);

        // the following line is unlikely to happen
        if (count($potential_neighboring_units) == 0) { return array(); }

        // skipping units already in $unit_ids, check distances of potential to $centers_xyz_coord
        // calculate distances to units in $nt_ids, record the smallest
        $neighboring_residues = $this->filter_neighboring_residues($centers_xyz_coord, $potential_neighboring_units, $distance, $unit_ids);

        return $neighboring_residues;
    }

    function get_complete_units($unit_ids)
    // Get the complete unit ids including the ones with alternative ids

    // Problem:  this is slow and it fails when someone puts in a unit id
    // that does not exist, like 7JIL|1|2|A|1061 where A should be G

    {
        $complete_units = array();
        foreach ($unit_ids as $unit_id) {
            $unit_alternative_id = $unit_id . "|%";

            $this->db->select('unit_id')
                 ->from('unit_info')
                 ->where('unit_id like', $unit_id)
                 ->or_where('unit_id like', $unit_alternative_id)
                 ->order_by('alt_id')
                 ->limit(1);
            $query = $this->db->get();

            if ($query->num_rows() == 0) { return False; }

            foreach ($query->result() as $row) {
                $complete_units[] = $row->unit_id;
            }
        }
        return $complete_units;
    }


    function get_unit_and_neighbor_coordinates($unit_ids, $distance=10)
    {
        // Get the coordinates of the specified units and return in model 1
        // Get the coordinates of neighboring units and return in model 2

        // given list of units, as string or array
        if (is_string($unit_ids)) {
            $nts = explode(',', $unit_ids);
        } else {
            $nts = $unit_ids;
        }

        // The following line crashes on missing unit ids
        // It's slow, and maybe it does not add much, it's commented out on 2023-05-22
        //$nts = $this->get_complete_units($nts);

        // get coordinates of the given units
        $core_coord_query = $this->get_unit_coordinates($nts);
        if ($core_coord_query == False) { return "No coordinate data available for the selection {$unit_ids} made"; }
        // core nts will have model num 1
        $core_coord = $this->change_model_num($core_coord_query, 1);

        // get unit ids of neighboring units
        $neighboring_residues = $this->get_neighboring_units($nts,$distance);

        // these variables are defined in /var/www/rna3dhub/application/config/constants.php
        global $headers_cif, $footer_cif;

        if (count($neighboring_residues) > 0) {
            // get coordinates of neighboring units
            $neighbor_coordinates = $this->get_unit_coordinates($neighboring_residues);
            //neighboring nts will have model num 2
            $neighboor_coord = $this->change_model_num($neighbor_coordinates, 2);

            $coord_array = array_merge($headers_cif, $core_coord, $footer_cif, $headers_cif, $neighboor_coord, $footer_cif);
        } else {
            $coord_array = array_merge($headers_cif, $core_coord, $footer_cif);
        }

        $final_result = '';
        foreach ($coord_array as $output) {
            $final_result .= $output . "\n";
        }

        return $final_result;
    }


    function get_loop_units($loop_id)
    {
        // query the database for loop_id
        // return the units that make up that loop_id

        $this->db->select('unit_id')
                 ->from('loop_positions')
                 ->where('loop_id', $loop_id)
                 ->order_by('position');
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return False; }

        $complete_units = array();
        foreach ($query->result() as $row) {
            $complete_units[] = $row->unit_id;
        }

        return $complete_units;
    }


    function get_chain_units($chain_id)
    {
        // query the database for chain_id
        // return the units that make up that chain_id
        // keep only one alt_id per nucleotide number, prefer NULL over A over B

        $fields = explode("|",$chain_id);
        $pdb_id = $fields[0];
        $model  = $fields[1];
        $chain  = $fields[2];

        // if $fields has more than 3 fields, the 9th field is the symmetry operator
        // return only unit ids in the designated symmetry operator, or the default 1_555
        // http://rnatest.bgsu.edu/rna3dhub/display3D/chain/1WVL|1|C
        // http://rnatest.bgsu.edu/rna3dhub/display3D/chain/1WVL|1|C||||||1_555
        // http://rnatest.bgsu.edu/rna3dhub/display3D/chain/1WVL|1|C||||||7_465
        // http://rnatest.bgsu.edu/rna3dhub/display3D/chain/1WVL|1|C||||||. request all symmetries

        if (count($fields) == 9) {
            $symmetry = $fields[8];
        } else {
            $symmetry = "1_555";
        }

        if ($symmetry == '.') {
            $this->db->select('unit_id')
                    ->from('unit_info')
                    ->where('pdb_id', $pdb_id)
                    ->where('model',  $model)
                    ->where('chain',  $chain)
                    ->order_by('number')
                    ->order_by('alt_id');
            $query = $this->db->get();
        } else {
            $this->db->select('unit_id')
                    ->from('unit_info')
                    ->where('pdb_id', $pdb_id)
                    ->where('model',  $model)
                    ->where('chain',  $chain)
                    ->where('sym_op', $symmetry)
                    ->order_by('number')
                    ->order_by('alt_id');
            $query = $this->db->get();
        }

        if ($query->num_rows() == 0) { return False; }

        $seen_ids = array();
        $complete_units = array();
        foreach ($query->result() as $row) {
            // store unit id as 9 fields but with alt_id blank
            // also blank out nucleotide sequence for chains like 2VY2|1|W
            $u = explode('|',$row->unit_id);       // get fields
            while (count($u) < 9) { $u[] = ''; }   // pad out to 9 fields
            $u[3] = '';                            // make sequence field blank
            $u[6] = '';                            // make alt_id field blank
            $clean_u = implode('|',$u);            // put fields into string
            if (array_key_exists($clean_u,$seen_ids)) {
                continue;
            } else {
                $seen_ids[$clean_u] = 1;
                $complete_units[] = $row->unit_id;
            }
        }

        return $complete_units;
    }



    function get_chain_coordinates($chain_id,$distance=10)
    {
        // Convert chain id to unit ids then get coordinates
        $units = $this->get_chain_units($chain_id);
        if ($units == False) { return "Chain is not found"; }

        return $this->get_unit_and_neighbor_coordinates($units,$distance);
    }


    function get_core_motif_units($loop_id, $release_id, $motif_id)
    {
        // look up the list of unit ids in core positions
        // that excludes "non-core" nucleotides

        $this->db->select('unit_id')
                 ->from('ml_loop_positions')
                 ->where('loop_id',$loop_id)
                 ->where('ml_release_id', $release_id)
                 ->where('motif_id', $motif_id)
                 ->order_by('position');
        $query = $this->db->get();
        if ($query->num_rows() == 0) { return False; }

        $core_units = array();
        foreach ($query->result() as $row) {
            $core_units[] = $row->unit_id;
        }
        return $core_units;
    }


    function get_loop_coordinates($loop_id,$distance=10)
    {
        // Convert loop_id to unit ids then get coordinates
        $nts = $this->get_loop_units($loop_id);
        if ($nts == False) { return "Loop id is not found"; }

        return $this->get_unit_and_neighbor_coordinates($nts,$distance);
    }


    function get_motif_coordinates($loop_data, $distance=10)
    {
        // retrieve coordinates, neighbors, and bulged bases and
        // put them in models 1, 2, 3, respectively

        // apparently these fields are separated by | but I don't know where that happens
        list($loop_id, $motif_id, $release_id) = explode('|', $loop_data);

        // get a list of core unit ids
        $core_motif_units = $this->get_core_motif_units($loop_id, $release_id, $motif_id);
        if ($core_motif_units == False) { return "The core units for {$loop_data} and {$loop_id} is not available"; }

        $fields = explode('|', $core_motif_units[0]);
        $pdb_id = $fields[0];
        $model_num = $fields[1];

        // get coordinates of the core nucleotides
        $core_coord_query = $this->get_unit_coordinates($core_motif_units);
        if ($core_coord_query == False) { return "No coordinate data available for for {$loop_data} and {$loop_id}"; }
        // core nts will have model num 1
        $core_coord = $this->change_model_num($core_coord_query, 1);

        // get a list of all unit ids in the loop
        $complete_motif_units = $this->get_loop_units($loop_id);
        if ($complete_motif_units == False) { return "The complete units for {$loop_data} and {$loop_id} is not available"; }

        // get neighboring unit ids
        $neighboring_residues = $this->get_neighboring_units($complete_motif_units,$distance=10);
        // get neighboring coordinates
        $neighbor_coordinates = $this->get_unit_coordinates($neighboring_residues);
        // neighboring nts will have model num 2
        $neighboor_coord = $this->change_model_num($neighbor_coordinates, 2);

        // The difference between the complete_motif_units and core_motif_units will give the bulged units
        $bulged_units = array_diff($complete_motif_units, $core_motif_units);
        $bulged_units = array_values($bulged_units);

        global $headers_cif, $footer_cif;

        if (empty($bulged_units)) {
            $coord_array = array_merge($headers_cif, $core_coord, $footer_cif, $headers_cif, $neighboor_coord, $footer_cif);
        } else {
            // get bulged unit coordinates
            $bulged_units_coordinates = $this->get_unit_coordinates($bulged_units);
            // bulged_units will have model num 3
            $bulged_coord = $this->change_model_num($bulged_units_coordinates, 3);
            // merge the set of coordinates into the output
            $coord_array = array_merge($headers_cif, $core_coord, $footer_cif, $headers_cif, $neighboor_coord, $footer_cif, $headers_cif, $bulged_coord, $footer_cif);
        }

        $final_result = '';

        foreach ($coord_array as $output) {
            $final_result .= $output . "\n";
        }

        return $final_result;
    }


    function get_coord_relative($unit_id)
    {
        // input is a comma-separated string of unit ids
        // first 10 are "core" and go in model 1
        // other nucleotides are "loop" and go in model 4
        // neighborhood goes in model 2

        $final_result = '';

        return $final_result;

        $unit_id = explode(',', $unit_id);
        $core_units = array_slice($unit_id, 0, 10);
        $loop_units = array_slice($unit_id, 10);

        $fields = explode('|', $core_units[0]);
        $pdb_id = $fields[0];
        $model_num = $fields[1];

        // get coordinates of the core nucleotides
        $core_coordinates = $this->get_unit_coordinates($core_units);
        if ($core_coordinates == False) { return "No coordinate data available for for {$unit_id}"; }
        // core nts will have model num 1
        $core_coord = $this->change_model_num($core_coordinates, 1);

        // get neighboring unit ids based on all unit ids
        $neighboring_residues = $this->get_neighboring_units($unit_id,$distance=10);
        // get neighboring coordinates
        $neighbor_coordinates = $this->get_unit_coordinates($neighboring_residues);
        // neighboring nts will have model num 2
        $neighboor_coord = $this->change_model_num($neighbor_coordinates, 2);

        // get coordinates of the other nucleotides
        $loop_coordinates = $this->get_unit_coordinates($loop_units);
        if ($loop_coordinates == False) { return "No coordinate data available for for {$unit_id}"; }
        // loop nts will have model num 4 so they can be displayed separately if desired
        $loop_coord = $this->change_model_num($loop_coordinates, 4);

        global $headers_cif, $footer_cif;

        $coord_array = array_merge($headers_cif, $core_coord, $footer_cif, $headers_cif, $neighboor_coord, $footer_cif, $headers_cif, $loop_coord, $footer_cif);

        $final_result = '';

        foreach ($coord_array as $output) {
            $final_result .= $output . "\n";
        }

        return $final_result;

    }


    function get_loop_pair_coordinates($loop_pair)
    {
        // This function is called by the compare motif group page
        // http://rnatest.bgsu.edu/rna3dhub/motif/compare/IL_28750.1/IL_69191.1
        // The input may be of the form:
        // IL_1J5E_001:@IL_1J5E_002
        $loop_ids = explode(':', $loop_pair);

        if ($loop_ids[0][0] == '@') {
            $loop_to_return = 1;
            $loop_ids[0] = substr($loop_ids[0], 1);
            $loop_id = $loop_ids[0];
        } elseif ($loop_ids[1][0] == '@') {
            $loop_to_return = 2;
            $loop_ids[1] = substr($loop_ids[1], 1);
            $loop_id = $loop_ids[1];
        } else {
            return 'Invalid loop pair';
        }

        return $this->get_loop_coordinates($loop_id);



        // the old code below returns old unit ids on rnatest as of November 2022
        // in order to do this properly, one would want to return just the
        // aligned unit ids from whichever loop has the @


        // The input may be of the form:
        // IL_1J5E_001:@IL_1J5E_002
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

        #return $this->get_loop_coordinates($loop_ids[0]);

        // see the list, using developer tools and looking
        // at the response from getCoordinates
        return $row[$nt_list];

        return $this->get_unit_and_neighbor_coordinates($nt_ids);
    }

    function get_exemplar_coordinates($motif_id)
    {
        // given a motif id find the representative loop
        // and return its coordinates

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
        // This looks like an old function that relies on
        // converting from old to new unit ids

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

        return $this->get_unit_and_neighbor_coordinates($comma_separated_nt_ids);
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

    function get_chain_RSR($chain_id)
    {
        $this->db->select('unit_id, real_space_r')
                 ->from('unit_quality')
                 ->like('unit_id', $chain_id, 'after');
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return 'No RSR correspondence is found';
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

    function get_chain_RSRZ($chain_id)
    {
        $this->db->select('unit_id, real_space_r_z_score')
                 ->from('unit_quality')
                 ->like('unit_id', $chain_id, 'after');
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

    // function get_bulge_RSRZ($loop_id)
    // {

    //     $this->db->select('unit_id')
    //              ->from('ml_loop_positions')
    //              ->where('loop_id',$loop_id)
    //              ->where('ml_release_id',4.24)
    //              ->order_by('position');
    //     $query = $this->db->get();
    //     if ($query->num_rows() == 0) { return 'Loop id not found'; }

    //     $core_units = array();
    //     foreach ($query->result() as $row) {
    //         $core_units[] = $row->unit_id;
    //     }

    //     $this->db->select('unit_id')
    //              ->from('loop_positions')
    //              ->where('loop_id',$loop_id)
    //              ->order_by('position');
    //     $query = $this->db->get();

    //     $complete_units = array();
    //     foreach ($query->result() as $row) {
    //         $complete_units[] = $row->unit_id;
    //     }

    //     $bulged_units = array_diff($complete_units, $core_units);
    //     $bulged_units = array_values($bulged_units);

    //     if(!empty($bulged_units)) {
    //         $this->db->select('unit_id, real_space_r_z_score')
    //              ->from('unit_quality')
    //              ->where_in('unit_id',$bulged_units);
    //         $query = $this->db->get();

    //         if ($query->num_rows() == 0) {
    //             return json_encode(json_decode ("{}"));
    //         } else {
    //             $RSRZ = $query->result();
    //             return json_encode($RSRZ);
    //         }
    //     } else {
    //         return json_encode(json_decode ("{}"));
    //     }


    // }

}

/* End of file ajax_model.php */
/* Location: ./application/model/ajax_model.php */
