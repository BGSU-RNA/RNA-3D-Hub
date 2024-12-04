<?php

namespace App\Models;
use CodeIgniter\Model;
ini_set("memory_limit","900M");
class Nrlist_model extends Model {

    public $last_seen_in;
    public $first_seen_in;
    public $current_release;
    public $tax_url;

    function __construct()
    {
        // $CI = & get_instance();
        // $CI->load->helper('url');
        // $CI->load->helper('html');
        // $CI->load->helper('form');
        $this->last_seen_in    = '';
        $this->first_seen_in   = '';
        $this->current_release = '';
        $this->tax_url = 'http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=';
        // Call the Model constructor
        parent::__construct();
        //$this->load->database();

    }

    function add_url($n)
    {
        return anchor(base_url(array('nrlist','view',$n)), $n);
    }


    function count_motifs($rel)
    {
        $builder = $this->db->table('nr_classes');
        $query = $builder->select('resolution, count(nr_class_id) as ids')
                ->where('nr_release_id', $rel)
                ->groupBy('resolution');
        $query = $query->get()->getResult();

        foreach ($query as $row) {
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

        $sql = "CALL nr_release_diff(?,?)";
        $par = array($rel1, $rel2);

        $query = $this->db->query($sql, $par);

        if ($query->num_rows == 0) {
            $par = array($rel2,$rel1);
            $query = $this->db->query($sql, $par);
        }

        foreach ($query as $row) {
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

        # this is the only place in this file that the following lines are used
        $query->free_result();
        unset($query);

        return $data;
    }

    function get_releases_by_class($id)
    {
        /* TODO:  Can this safely reduce to just nr_class_id and description? */
        $builder = $this->db->table('nr_classes AS nrc');
        $query = $builder->select('nrc.nr_release_id')
                ->select('nrr.description')
                ->join('nr_releases AS nrr','nrc.nr_release_id = nrr.nr_release_id')
                ->where('nrc.name',$id)
                ->orderBy('nrr.date');
        $query = $query->get()->getResult();
        $releases[0][0] = 'Release';
        $releases[1][0] = 'Date';
        $i = 0;
        foreach ($query as $row) {
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

    function get_status($id,$type)
    {
        // find the most recent nr_release_id for this equivalence class type
        $query = $this->db->table('nr_classes')
                ->select('nr_release_id')
                // ->select('date')
                // ->select('description')
                ->like('name',$type . '%')
                ->orderBy('nr_class_id','desc')
                ->limit(1);
        $query = $query->get()->getResult();

        foreach ($query as $row) {
            $current_release = $row->nr_release_id;
        }

        $this->current_release = $current_release;

        $query = $this->db->table('nr_classes')
                ->select('nr_class_id')
                // ->select('name')
                // ->select('nr_release_id')
                // ->select('resolution')
                // ->select('handle')
                // ->select('version')
                // ->select('comment')
                ->where('name',$id)
                ->where('nr_release_id',$current_release)
                ->orderBy('nr_class_id','desc')
                ->limit(1);
        $query = $query->get();

        if ($query->getNumRows() > 0) {
            return 'Current';
        } else {
            return 'Obsolete';
        }
    }

    function make_pdb_widget_link($pdb)
    {
        return "<span class='rcsb_image' title='{$pdb}|asr|xsmall|'></span><a class='pdb'>$pdb</a>";
    }

    function make_dna_pdb_widget_link($pdb,$index)
    {
        $ife_1st = explode('+', $pdb);
        $ife_1st = $ife_1st[0];
        return "<input type='checkbox' id='$index' data-coord=$ife_1st class='jmolInline'><br><a class='pdb'>$pdb</a>";
    }

    function make_checkbox($pdb,$index)
    {
        $ife_1st = explode('+', $pdb);
        $ife_1st = $ife_1st[0];
        return "<input type='checkbox' id='$index' data-coord=$ife_1st class='jmolInline'>";
    }

    function get_source_organism($ife_id)
    {
        if ( substr_count($ife_id, '+') >= 1 ) {
            $chain_list = explode('+', $ife_id);
            $ife_id = $chain_list[0];
        }

        if ( substr_count($ife_id, '|') == 1 ) {
            list($pdb_id, $chain) = explode('|', $ife_id);
        } else {
            list($pdb_id, $model_num, $chain) = explode('|', $ife_id);
        }

        $builder = $this->db->table('chain_info');
        $query = $builder->select('source, taxonomy_id')
                ->where('pdb_id', $pdb_id)
                ->where('chain_name', $chain)
                ->get();

        if ( $query->getNumRows() > 0 ) {
            $result = $query->getResult();
            $tid = $result[0]->taxonomy_id;
            $sid = $result[0]->source;

            if ( $tid != '' ) {
                return anchor_popup("$this->tax_url$tid", "$sid");
            } else {
                return $sid;
            }
        } else {
            return '??';
        }
    }

    function get_members($id)
    {
        $builder = $this->db->table('pdb_info AS pi');
        $query = $builder->select('pi.pdb_id')
                ->select('ch.ife_id')
                ->select('pi.title')
                ->select('pi.experimental_technique')
                ->select('pi.release_date')
                ->select('pi.resolution')
                ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
                ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
                ->where('cl.name',$id)
                ->where('cl.nr_release_id',$this->last_seen_in) # copy from the comment function above
                ->groupBy('pi.pdb_id')
                ->groupBy('ii.ife_id')
                ->orderBy('ch.rank','asc')
                ->get()
                ->getResult();

        $builder = $this->db->table('ife_info AS ii');
        $query_cpv = $builder->select('ii.pdb_id')
                ->select('ch.ife_id')
                ->select('cpv.chain')
                ->select('cpv.property')
                ->select('cpv.value')
                ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
                ->join('chain_property_value AS cpv', 'cpv.pdb_id = ii.pdb_id')
                ->where('ch.nr_class_name',$id)
                ->get();

        // converting experimental_technique without changing pdb_info table.
        $experimental_technique = array();
        $experimental_technique['X-RAY DIFFRACTION'] = 'X-ray diffraction';
        $experimental_technique['ELECTRON MICROSCOPY'] = 'Electron microscopy';
        $experimental_technique['SOLUTION NMR'] = 'Solution NMR';
        $experimental_technique['FIBER DIFFRACTION'] = 'Fiber diffraction';
        $experimental_technique['THEORETICAL MODEL, SOLUTION NMR'] = 'Theoretical model, solution NMR';
        $experimental_technique['SOLUTION NMR, THEORETICAL MODEL'] = 'Solution NMR, theoretical model';
        $experimental_technique['FLUORESCENCE TRANSFER'] = 'Fluorescence transfer';
        $experimental_technique['NEUTRON DIFFRACTION'] = 'Neutron diffraction';
        $experimental_technique['SOLUTION NMR, SOLUTION SCATTERING'] = 'Solution NMR, solution scattering';
        $experimental_technique['SOLUTION SCATTERING, SOLUTION NMR'] = 'Solution scattering, solution NMR';
        $experimental_technique['SOLID-STATE NMR'] = 'Solid-state NMR';
        $experimental_technique['ELECTRON MICROSCOPY, SOLUTION NMR'] = 'Electron microscopy, solution NMR';
        $experimental_technique['X-RAY DIFFRACTION, SOLUTION SCATTERING'] = 'X-ray diffraction, solution scattering';

        $ife_to_cpv = array();

        foreach ($query_cpv->getResult() as $row) {
            // for development, display the result of this query on the screen
            // echo $row->pdb_id,"\n";
            // echo $row->ife_id,"\n";
            // echo $row->chain,"\n";
            // echo $row->property,"\n";
            // echo $row->value,"\n\n";

            $row_pdb = $row->pdb_id;
            $row_ife = $row->ife_id;
            $row_chain = $row->chain;
            $row_property = $row->property;
            $row_value = $row->value;

            // echo "{$row_chain}:{$row_value}\n";

            $ife_chain_list = explode('+', $row_ife);
            foreach ($ife_chain_list as $ife_chain){
                $pieces = explode('|', $ife_chain);
                $chain = end($pieces);
                if ($row_chain == $chain and !empty($row_value)) {
                    $ife_to_cpv["{$row_pdb}_{$row_chain}_{$row_property}"] = $row_value;
                }
            }

            // if chain matches the third field of the ife_id (those are the only ones we actually need)
                // put data from this query into a dictionary
                // key could be pdb_id + "_" + chain
                // key could be pdb_id + "_" + chain + "_" + property  <-- then you only need one dictionary

            // }
            // maybe make one dictionary for name, one for source, one for rfam?
            // depending on the property, you fill in a different dictionary

        }

        // foreach ($ife_to_cpv as $key=>$value){
        //     echo "{$key} : {$value}\n";
        // }

        $i = 0;
        $table = array();

        foreach ($query as $row) {
            $link = $this->make_pdb_widget_link(str_replace('+','+ ',$row->ife_id));

            if ( $i==0 ) {
                $link = $link . ' <strong>(rep)</strong>';
            }

            $i++;

            // explode by +: split by +
            // explode by |: for each chain, extract pdb_id and chain
            // make the key you need
            // plug into a dictionary to map to standardized name, source, Rfam family?
            // Use the long version of the standardized name, I guess
            // join those by + sign

            // echo "Name, source, rfam for ",$row->ife_id," is \n";
            $row_ife = $row->ife_id;
            $ife_chain_list = explode('+', $row_ife);

            $rfam_str = "";
            $source_str = "";
            $standardized_name_str = "";

            $rfam_list = array();
            $standardized_name_list = array();
            $source_list = array();

            $j = 0;
            foreach ($ife_chain_list as $ife_item){
                $ife_pieces = explode('|', $ife_item);
                $pdb_from_ife = $ife_pieces[0];
                $ife_chain = end($ife_pieces);
                $rfam_key = "{$pdb_from_ife}_{$ife_chain}_rfam_family";
                $source_key = "{$pdb_from_ife}_{$ife_chain}_source";
                $standardized_name_key = "{$pdb_from_ife}_{$ife_chain}_standardized_name";


                if (array_key_exists($rfam_key, $ife_to_cpv)){
                    array_push($rfam_list, $ife_to_cpv[$rfam_key]);
                }
                if (empty($source_str) and array_key_exists($source_key, $ife_to_cpv)){
                    array_push($source_list, $ife_to_cpv[$source_key]);
                }
                if (array_key_exists($standardized_name_key, $ife_to_cpv)){
                    $standardized_name = explode(';', $ife_to_cpv[$standardized_name_key]);
                    array_push($standardized_name_list, $standardized_name[0]);
                }
            }

            $source_list = array_unique($source_list);
            if (count($source_list) > 1){
                $j = 0;
                foreach ($source_list as $source_item){
                    $source_str .= $source_item;
                    if ($j < count($source_list) - 1){
                        $source_str .= " + ";
                    }
                    $j++;
                }
            } elseif(!empty($source_list)){
                $source_str .= $source_list[0];

            }

            $j = 0;
            foreach ($rfam_list as $rfam_item){
                $rfam_str .= $rfam_item;
                if ($j < count($rfam_list) - 1){
                    $rfam_str .= " + ";
                }
                $j++;
            }

            $j = 0;
            foreach ($standardized_name_list as $standardized_name_item){
                $standardized_name_str .= $standardized_name_item;
                if ($j < count($standardized_name_list) - 1){
                    $standardized_name_str .= " + ";
                }
                $j++;
            }

            // Create table for members tab
            $table[] = array($i,
                            $link,
                            $standardized_name_str,
                            $this->get_compound_single($row->ife_id),
                            $this->get_source_organism($row->ife_id),
                            $source_str,
                            $rfam_str,
                            $row->title,
                            $experimental_technique[$row->experimental_technique],
                            $row->resolution,
                            $row->release_date);
        }

        return $table;
    }

    function get_statistics($id)
    {   if(substr($id, 0, 3) === "DNA"){
            $builder = $this->db->table('pdb_info AS pi');
            $query = $builder->select('pi.pdb_id')
                    ->select('ii.ife_id')
                    ->select('pi.title')
                    ->select('pi.experimental_technique')
                    ->select('pi.release_date')
                    ->select('pi.resolution')
                    ->select('ii.length')
                    ->select('ii.bp_count')
                    ->select('ot.class_order')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_ordering_test AS ot', 'ii.ife_id = ot.ife_id')
                    ->where('ot.nr_class_name',$id)
                    ->orderBy('ot.class_order','asc');
            $query = $query->get()->getResult();
            $i = 0;
            $table = array();
            foreach ($query as $row) {
                $i++;
                $link = $this->make_pdb_widget_link(str_replace('+','+ ',$row->ife_id));
                $table[] = array($i,
                                $row->ife_id,
                                $this->make_checkbox($row->ife_id,$i-1),
                                $link,
                                $row->title,
                                $row->experimental_technique,
                                $row->resolution,
                                $row->length,
                                'NAKB_NA_annotation',
                                'NAKB_protein_annotation',
                                );
            }

            # add NAKB annotations to the table
            $builder = $this->db->table('pdb_info AS pi');
            $query = $builder->select('pi.pdb_id')
                    ->select('ii.ife_id')
                    ->select('pi.title')
                    ->select('pi.experimental_technique')
                    ->select('pi.release_date')
                    ->select('pi.resolution')
                    ->select('ii.length')
                    ->select('ii.bp_count')
                    ->select('ot.class_order')
                    ->select('ppv.property')
                    ->select('ppv.value')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_ordering_test AS ot', 'ii.ife_id = ot.ife_id')
                    ->join('pdb_property_value AS ppv', 'pi.pdb_id = ppv.pdb_id')
                    ->where('ot.nr_class_name',$id)
                    ->orderBy('ot.class_order','asc');
            $query = $query->get()->getResult();

            $annotations = array();
            foreach ($query as $row) {
                $annotations[] = array(
                                $row->ife_id,
                                $row->property,
                                $row->value
                                );
            }

            // nested for loop is a slow way to do this; better to use a dictionary
            $return_table = array();
            foreach ($table as $r){
                $tem_r = $r;
                foreach($annotations as $l){
                    if ($tem_r[1] == $l[0]){
                        if ($tem_r[8] == $l[1]){
                            $tem_r[8] = ($l[2] ?: "");
                        }
                        if ($tem_r[9] == $l[1]){
                            $tem_r[9] = ($l[2] ?: "");
                        }
                    }
                }
                array_splice($tem_r, 1, 1);
                $return_table[] = $tem_r;

            }
            return $return_table;
    } else {
            // RNA equivalence class
            $builder = $this->db->table('pdb_info AS pi');
            $query = $builder->select('pi.pdb_id')
                    ->select('ii.ife_id')
                    ->select('pi.title')
                    ->select('pi.experimental_technique')
                    ->select('pi.release_date')
                    ->select('pi.resolution')
                    ->select('ii.length')
                    ->select('ii.bp_count')
                    ->select('ot.class_order')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_ordering_test AS ot', 'ii.ife_id = ot.ife_id')
                    ->where('ot.nr_class_name',$id)
                    ->orderBy('ot.class_order','asc');
            $query = $query->get()->getResult();
            $i = 0;
            $table = array();
            foreach ($query as $row) {
                $i++;
                $link = $this->make_pdb_widget_link(str_replace('+','+ ',$row->ife_id));
                $table[] = array($i,
                                $this->make_checkbox($row->ife_id,$i-1),
                                $link,
                                $row->title,
                                $row->experimental_technique,
                                $row->resolution,
                                $row->length);
            }
            return $table;
    }

    }

    // function get_heatmap_data_revised($id)
    // {
    //     // Retrieve IFE names and order index for this equivalence class
    //     $query = $this->db->table('nr_ordering_test AS NO1')
    //             ->select('NO1.ife_id AS ife1')
    //             ->select('NO1.class_order AS ife1_index')
    //             ->where('NO1.nr_class_name', $id);

    //     $query = $query->get()->getResult();

    //     // Assemble IFE names into an array
    //     $ife_list = array();
    //     $index_list = array();
    //     foreach ($query as $row) {
    //         array_push($ife_list,$row->ife1);
    //         array_push($index_list,$row->ife1_index);
    //     }

    //     // Load discrepancies from large classes from flat file, small from database
    //     if (count($ife_list) > 10) {

    //         // store all N^2 discrepancies in an associative array
    //         $discrepancy_array = array();
    //         $file_lines = file('/var/www/html/discrepancy/IFEdiscrepancy.txt');
    //         foreach ($file_lines as $line) {
    //             $line = str_replace("\n","",$line);
    //             $resultArray = explode("\t", $line);
    //             if (in_array($resultArray[0],$ife_list)) {
    //                 $discrepancy_array[$resultArray[0]." ".$resultArray[1]] = $resultArray[2];
    //             }
    //         }

    //         $file_lines = array();

    //         // build one line of $result for each pair of discrepancies
    //         $result = array();

    //         for ($i = 0; $i < count($ife_list); $i++) {
    //             for ($j = 0; $j < count($ife_list); $j++) {
    //                 $ife1 = $ife_list[$i];
    //                 $ife2 = $ife_list[$j];
    //                 $newrow["ife1"] = $ife1;
    //                 $newrow["ife2"] = $ife2;
    //                 $newrow["ife1_index"] = $index_list[$i];
    //                 $newrow["ife2_index"] = $index_list[$j];
    //                 $key  = $ife1." ".$ife2;
    //                 $key2 = $ife2." ".$ife1;
    //                 if (array_key_exists($key, $discrepancy_array)) {
    //                     $newrow["discrepancy"] = $discrepancy_array[$key];
    //                 } elseif (array_key_exists($key2, $discrepancy_array)) {
    //                     $newrow["discrepancy"] = $discrepancy_array[$key2];
    //                 } else {
    //                     $newrow["discrepancy"] = null;
    //                 }
    //                 array_push($result,$newrow);
    //             }
    //         }

    //         $heatmap_data = json_encode($result);

    //     } else {
    //         // this query is slow enough that it bogs down the server
    //         $query = $this->db->table('nr_ordering_test AS NO1')
    //                 ->select('NO1.ife_id AS ife1')
    //                 ->select('NO1.class_order AS ife1_index')
    //                 ->select('NO2.ife_id AS ife2')
    //                 ->select('NO2.class_order AS ife2_index')
    //                 ->select('CCS.discrepancy')
    //                 ->join('nr_ordering_test AS NO2', 'NO1.nr_class_name = NO2.nr_class_name', 'inner')
    //                 ->join('ife_chains AS IC1', 'NO1.ife_id = IC1.ife_id AND IC1.index = 0', 'inner')
    //                 ->join('ife_chains AS IC2', 'NO2.ife_id = IC2.ife_id AND IC2.index = 0', 'inner')
    //                 ->join('chain_chain_similarity AS CCS', 'IC1.chain_id = CCS.chain_id_1 AND IC2.chain_id = CCS.chain_id_2', 'left outer')
    //                 ->where('NO1.nr_class_name', $id);

    //         $query = $query->get()->getResult();

    //         $heatmap_data = json_encode($query);

    //     }
    //     $eff_data = json_decode($heatmap_data,true);

    //     $eff_ifes = array();
    //     $eff_discrepancy = array();
    //     $eff_discrepancy_one_row = array();
    //     foreach ($eff_data as $row) {
    //         foreach ($row as $key => $value) {
    //             if ($key == 'ife1'){
    //                 // store each ife value in a separate array; apparently they occur in the correct order
    //                 if (!in_array($value, $eff_ifes)){
    //                     $eff_ifes[] = $value;
    //                 }
    //             }
    //             if ($key == 'discrepancy'){
    //                 $eff_discrepancy_one_row[] = (number_format($value,4) >0? number_format($value,4) : NULL );
    //             }
    //         }
    //     }

    //     $two_d_array = array();
    //     $size = count($eff_ifes);
    //     // echo $size;
    //     for ($i = 0; $i < count($eff_discrepancy_one_row); $i += $size) {
    //         $two_d_array[] = array_slice($eff_discrepancy_one_row, $i, $size);
    //     }

    //     for ($i = 0; $i < $size; $i++) {
    //         $two_d_array[$i][$i] = 0;
    //     }

    //     $eff_data_cleaned = array();
    //     $eff_data_cleaned[] = '#heatmap';
    //     $eff_data_cleaned[] = $two_d_array;
    //     $eff_data_cleaned[] = $eff_ifes;

    //     return json_encode($eff_data_cleaned);
    // }

    function get_heatmap_data($id)
    {
        // $id is an equivalence class id

        // Retrieve IFE names and order index for this equivalence class
        $query = $this->db->table('nr_ordering_test AS NOT')
                ->select('NOT.ife_id AS ife')
                ->select('NOT.class_order AS ife_index')
                ->where('NOT.nr_class_name', $id)
                ->get()
                ->getResult();

        // map ife names to index, and index to ife names
        $ife_to_index = array();
        $index_to_ife = array();
        foreach ($query as $row) {
            $ife_to_index[$row->ife] = $row->ife_index;
            $index_to_ife[$row->ife_index] = $row->ife;
        }

        // Set up a square 2D array with null values
        $n = count($ife_to_index);
        $ns = $n*$n;
        $discrepancy = array();
        for ($i = 0; $i < $n; $i++) {
            $discrepancy[$i] = array(); // Create a new row
            for ($j = 0; $j < $n; $j++) {
                $discrepancy[$i][$j] = null; // Initialize each cell to null
            }
            $discrepancy[$i][$i] = 0; // Set the diagonal to 0
        }

        // Load discrepancies from large classes from flat file, small from database
        if ($n > 50) {
            // Load discrepancies from large text file of all discrepancies
            // takes about 2.8 seconds for 52 IFEs, 261 MB??
            // takes about 6.5 seconds for 103 IFEs
            // takes about 20 seconds for 488 IFEs, but at least it loads! 270 MB
            // takes about 50 seconds for 739 IFEs, 279 MB https://rna.bgsu.edu/rna3dhub/nrlist/view/NR_all_35542.138
            $file_lines = file('/var/www/html/discrepancy/IFEdiscrepancy.txt');
            $count = $n;
            foreach ($file_lines as $line) {
                $line = str_replace("\n","",$line);
                $resultArray = explode("\t", $line);
                if (in_array($resultArray[0],$index_to_ife) and in_array($resultArray[1],$index_to_ife)) {
                    $i = $ife_to_index[$resultArray[0]];
                    $j = $ife_to_index[$resultArray[1]];
                    $d = (number_format($resultArray[2],4) >0? number_format($resultArray[2],4) : NULL );
                    // convert back to float
                    $d = floatval($d);
                    $discrepancy[$i][$j] = $d;
                    $discrepancy[$j][$i] = $d;
                    $count += 2;
                    if ($count == $ns) {
                        // got all the discrepancies we need!
                        break;
                    }
                }
            }
        } else {
            // this query is slow enough that it bogs down the server
            // takes about 1.6 seconds for 52 IFEs
            // takes about 3 seconds for 103 IFEs
            // takes about 71 seconds for 488 IFEs, 88 MB
            $query = $this->db->table('nr_ordering_test AS NO1')
                    ->select('NO1.class_order AS ife1_index')
                    ->select('NO2.class_order AS ife2_index')
                    ->select('CCS.discrepancy')
                    ->join('nr_ordering_test AS NO2', 'NO1.nr_class_name = NO2.nr_class_name', 'inner')
                    ->join('ife_chains AS IC1', 'NO1.ife_id = IC1.ife_id AND IC1.index = 0', 'inner')
                    ->join('ife_chains AS IC2', 'NO2.ife_id = IC2.ife_id AND IC2.index = 0', 'inner')
                    ->join('chain_chain_similarity AS CCS', 'IC1.chain_id = CCS.chain_id_1 AND IC2.chain_id = CCS.chain_id_2', 'left outer')
                    ->where('NO1.nr_class_name', $id)
                    ->where('NO1.class_order < NO2.class_order')
                    ->get()
                    ->getResult();
            foreach ($query as $row) {
                $i = $row->ife1_index;
                $j = $row->ife2_index;
                // old
//                $d = (number_format($row->discrepancy,4) >0? number_format($row->discrepancy,4) : NULL );
                // new, more robust to $row->discrepancy being null
                $d = (isset($row->discrepancy) && $row->discrepancy > 0) ? number_format($row->discrepancy, 4) : NULL;                $discrepancy[$i][$j] = $d;
                $discrepancy[$j][$i] = $d;
            }
        }

        $eff_data_cleaned = array();
        $eff_data_cleaned[] = '#heatmap';
        $eff_data_cleaned[] = $discrepancy;
        $eff_data_cleaned[] = $index_to_ife;

        $json_data = json_encode($eff_data_cleaned);
        $json_data = str_replace('],', "],\n", $json_data);

        // encode in json and also add line breaks so the output page is easier to read, when desired
        return $json_data;
    }


    function get_ribosome_annotation_new($id)
    {
        $builder = $this->db->table('pdb_info AS pi');
        $query = $builder->select('ch.ife_id')
                    ->select('pi.pdb_id')
                    ->select('ca.assembly_id')
                    ->select('ca.ssu_chain')
                    ->select('ca.lsu_large_chain')
                    ->select('ca.lsu_medium_chain')
                    ->select('ca.lsu_small_chain')
                    ->select('ca.mrna')
                    ->select('ca.aminoacyl_trna')
                    ->select('ca.aminoacyl_trna_state')
                    ->select('ca.peptidyl_trna')
                    ->select('ca.peptidyl_trna_state')
                    ->select('ca.exit_trna')
                    ->select('ca.exit_trna_state')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
                    ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
                    ->join('ribosome_chain_annotation AS ca', 'ch.ife_id = ca.ssu_chain')
                    ->where('cl.name',$id)
                    ->where('cl.nr_release_id', $this->last_seen_in) # copy from the comment function above
                    ->groupBy('pi.pdb_id')
                    ->groupBy('ii.ife_id')
                    ->orderBy('ch.rank','asc');

        $query = $query->get()->getResult();

        $i = 0;
        $table = array();

        foreach ($query as $row) {
            $link = $this->make_pdb_widget_link($row->ife_id);
            $assembly = $row->assembly_id;

            $ife_components = explode("|", $row->ife_id);
            $pdb_id = $ife_components[0];


            $lsu_large_chain = $row->lsu_large_chain;
            $lsu_medium_chain = $row->lsu_medium_chain;
            $lsu_small_chain = $row->lsu_small_chain;
            $mrna = $row->mrna;

            $aminoacyl_trna_occupancy = "";
            if (!empty($row->aminoacyl_trna)) {
                $aminoacyl_trna_occupancy = $row->aminoacyl_trna . " (" . $row->aminoacyl_trna_state . ")";
            }

            $peptidyl_trna_occupancy = "";
            if (!empty($row->peptidyl_trna)) {
                $peptidyl_trna_occupancy = $row->peptidyl_trna . " (" . $row->peptidyl_trna_state . ")";
            }

            $exit_trna_occupancy = "";
            if (!empty($row->exit_trna)) {
                $exit_trna_occupancy = $row->exit_trna . " (" . $row->exit_trna_state . ")";
            }


            $i++;
            $table[] = array($i,
                            $link,
                            $row->pdb_id,
                            $assembly,
                            $lsu_large_chain,
                            $lsu_medium_chain,
                            $lsu_small_chain,
                            $mrna,
                            $aminoacyl_trna_occupancy,
                            $peptidyl_trna_occupancy,
                            $exit_trna_occupancy
                            );

        }

        return $table;
    }

    function get_ribosome_chain($pdb, $assembly, $value)
    {
        $builder = $this->db->table('chain_annotation');
        $query = $builder->select('chain')
                ->where('pdb_id', $pdb)
                ->where('assembly', $assembly)
                ->like('value', $value);

        $query = $query->get()->getResult();

        $result = "";
        foreach ($query as $row) {
            $result = $row->chain;
        }

        return $result;
    }

    function get_trna_occupancy($chain)
    {
        $builder = $this->db->table('chain_annotation');
        $query = $builder->select('value')
                ->where('chain', $chain)
                ->where('feature', 'tRNA_occupancy');
                //->like('value', $value);

        $query = $query->get()->getResult();

        $result = "";
        foreach ($query as $row) {
            $result = $row->value;
        }

        return $result;
    }

    function get_compound_single($ife)
    {
        // look up the compound name for a single IFE
        $query = $this->db->table('ife_info AS ii')
                ->select('group_concat(DISTINCT ci.compound separator ", ") as compound', FALSE)
                ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id AND ii.model = ic.model')
                ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id AND ci.pdb_id = ii.pdb_id')
                ->where('ii.ife_id', $ife)
                ->orderBy('ci.chain_name')
                ->get()
                ->getRow();

        $result = $query->compound;

        return $result;
    }

    function get_compound_list($id)
    {
        $builder = $this->db->table('chain_info');
        $query = $builder->select('group_concat(compound separator ", ") as compounds', FALSE)
                ->where('pdb_id', $id)
                ->groupBy('pdb_id');
        $query = $query->get()->getResult();

        foreach ($query as $row) {
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
            $s[$i] = $this->add_space_to_long_IFE($s[$i]);
            $s[$i] = "<a class='pdb'>$s[$i]</a>";
        }

        return implode(', ', $s);
    }

    function count_pdb_class($list)
    {
        if (!is_array($list)) {
            $s = explode(',', $list);
        } else {
            $s = $list;
        }

        return count($s);
    }

    function get_history($id,$mode)
    {
        if ($mode == 'parents') {
            $sql = "CALL nr_set_diffs_parents(?, ?)";
            $par = [$id, $this->first_seen_in];
        } elseif ($mode == 'children') {
            $sql = "CALL nr_set_diffs_children(?, ?)";
            $par = [$id, $this->last_seen_in];
        }

        // Execute the stored procedure
        $query = $this->db->query($sql, $par)->getResult();

        $table = array();

        foreach ($query as $row) {
            $nr_class_name_out = ( $mode == 'parents' ) ? $row->nr_class_name_parent : $row->nr_class_name_child;
            $one_minus_two = ( $mode == 'parents' ) ? $row->added : $row->only;
            $two_minus_one = ( $mode == 'parents' ) ? $row->removed : $row->added;
            $one_minus_two_count = ( $mode == 'parents' ) ? $row->add_count : $row->only_count;
            $two_minus_one_count = ( $mode == 'parents' ) ? $row->rem_count : $row->add_count;

            $table[] = array($row->nr_class_name_base,
                            anchor(base_url("nrlist/view/".$nr_class_name_out),$nr_class_name_out),
                            anchor(base_url("nrlist/release/".$row->nr_release_id), $row->nr_release_id),
                            "(" . $row->int_count . ") " . $this->add_pdb_class($row->intersection),
                            "(" . $one_minus_two_count . ") " . $this->add_pdb_class($one_minus_two),
                            "(" . $two_minus_one_count . ") " . $this->add_pdb_class($two_minus_one)
                            );
        }

        // $query->next_result(); ### clears the extra empty MySQL result set

        return $table;
    }

    function beautify_description_date($s)
    {
        return substr($s,0,4) .'-'. substr($s,4,2) .'-'. substr($s,6,2);
    }

    function get_change_counts_by_release()
    {
        $builder = $this->db->table('nr_parent_counts');
        $query = $builder->select('nr_release_id')
                ->select('resolution')
                ->select('new_class_count AS nag')
                ->select('removed_class_count AS nrg')
                ->select('updated_class_count AS nug');
        $query = $query->get()->getResult();

        $changes = array();
        foreach ($query as $row) {
            if ($row->resolution == 'all') {
                $changes[$row->nr_release_id] = $row->nag + $row->nug + $row->nrg;
            }
        }

        return $changes;
    }

    function get_label_type($changes)
    {
        if ($changes == 0) {
            $label = 'success';
        } elseif ($changes <= 20) {
            $label = 'notice';
        } elseif ($changes <= 100) {
            $label = 'warning';
        } else {
            $label = 'important';
        }

        return $label;
    }

    function get_release_to_ife_count($type) {
        // This method uses too much memory!  Over 900 MB.
        if ($type == 'DNA') {
            $prefix = 'DNA_all';
        } else {
            $prefix = 'NR_all_';
        }
        // Map equivalence class to total ife count in that class
        $query = $this->db->table('nr_class_rank')
                        ->select('nr_class_name')
                        // ->select('count(ife_id) as num')
                        ->select('max(rank) as num')
                        ->where('SUBSTRING(nr_class_name, 1, 7)', $prefix)
                        ->groupBy('nr_class_name')
                        ->get()
                        ->getResult();

        $class_to_count = array();
        foreach ($query as $row) {
            $class_to_count[$row->nr_class_name] = $row->num+1;
        }

        // Get releases and class names in that release
        $query = $this->db->table('nr_classes')
                        ->select('nr_release_id')
                        ->select('name')
                        ->where('resolution','all')
                        ->where('SUBSTRING(name, 1, 7)', $prefix)
                        ->get()
                        ->getResult();

        $release_to_count = array();
        foreach ($query as $row) {
            $release_to_count[$row->nr_release_id] = 0;
        }
        foreach ($query as $row) {
            $release_to_count[$row->nr_release_id] += $class_to_count[$row->name];
        }

        return $release_to_count;
    }

    function get_pdb_files_counts($type)
    {
        if ($type == 'DNA') {
            $prefix = 'DNA_all';
        } else {
            $prefix = 'NR_all_';
        }
        // This query is too slow
        // This seems to count ife ids in each release ... is that right?
        // $query = $this->db->table('ife_info AS ii')
        //                 ->select('ncl.nr_release_id, count(ii.pdb_id) as num')
        //                 ->join('nr_class_rank AS nch', 'ii.ife_id = nch.ife_id')
        //                 ->join('nr_classes AS ncl', 'nch.nr_class_name = ncl.name')
        //                 ->where('ncl.resolution', 'all')
        //                 ->where('SUBSTRING(name, 1, 7)', $prefix)
        //                 ->groupBy('ncl.nr_release_id')
        //                 ->get()
        //                 ->getResult();

        // ->like('ncl.name', $type.'%')
        // ->where("SUBSTRING(ncl.name, 1, 3)", $type3)

        // The constraint on the next line is not OK because nr_class_rank doesn't have all the nr_class_id values
        // ->join('nr_class_rank AS ncr', 'ncl.nr_class_id = ncr.nr_class_id')
                        // ->where('ncl.nr_class_id >', 999999)

        // this query is also too slow, about 66 seconds on 2024-09-09
        // $query = $this->db->table('nr_classes as ncl')
        //                 ->select('ncl.nr_release_id')
        //                 ->select('count(ncr.ife_id) as num')
        //                 ->join('nr_class_rank AS ncr', 'ncl.name = ncr.nr_class_name')
        //                 ->where('ncl.resolution', 'all')
        //                 ->where('SUBSTRING(ncl.name, 1, 7)', $prefix)
        //                 ->groupBy('ncl.nr_release_id');

        // Write the SQL query as a string
        $sql = "SELECT ncl.nr_release_id, count(ncr.ife_id) as num
        FROM nr_classes AS ncl
        JOIN nr_class_rank AS ncr ON ncl.name = ncr.nr_class_name
        WHERE ncl.resolution = 'all'
        AND SUBSTRING(ncl.name, 1, 7) = ?
        GROUP BY ncl.nr_release_id";

        // Execute the raw SQL query
        $unquery = $this->db->query($sql, [$prefix]);

        $release_to_count = array();

        while ($row = $unquery->getUnbufferedRow()) {
            $release_to_count[$row->nr_release_id] = $row->num;
        }

        // foreach ($query->getResult() as $row) {
        //     $release_to_count[$row->nr_release_id] = $row->num;

        //     // if the$row->nr_release_id is not already a key, add it with count 1
        //     // if (!array_key_exists($row->nr_release_id, $release_to_count)) {
        //     //     $release_to_count[$row->nr_release_id] = 1;
        //     // } else {
        //     //     $release_to_count[$row->nr_release_id]++;
        //     // }
        // }

        return $release_to_count;
    }

    function get_newest_pdb_images()
    {
        // $sql = "SELECT DISTINCT(`pdb_id`) FROM `pdb_info`" .
        //     "WHERE `release_date` >= DATE_ADD('" .
        //     date("Y-m-d H:i:s") . "', INTERVAL -1 WEEK);";
        // $query = $this->db->query($sql);

        $builder = $this->db->table('pdb_info');
        $query = $builder->select('pdb_id')
                ->where('release_date >=', date("Y-m-d H:i:s", strtotime("-1 week")))
                ->distinct();

        $query = $query->get()->getResult();

        $new_files = array();
        foreach ($query as $row) {
            $new_files[] = $row->pdb_id;
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
        $builder = $this->db->table('nr_releases');
        $query = $builder->select('nr_release_id')
                ->select('date')
                ->select('description')
                ->orderBy('index', 'desc')
                ->limit(2);
        $query = $query->get()->getResult();

        foreach ($query as $row) {
            $releases[] = $row->nr_release_id;
        }

        // get their release difference
        $builder = $this->db->table('__trash_nr_release_diff');
        $query = $builder->select('added_pdbs')
                ->where('nr_release_id1', $releases[0])
                ->where('nr_release_id2', $releases[1])
                ->where('resolution', 'all');
        $query = $query->get()->getResult();

        foreach ($query as $row) {
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
        $builder = $this->db->table('pdb_info');
        $query = $builder->select('pdb_id')
                ->distinct()
                ->get();

        return $query->getNumRows();
    }

    function get_all_releases($type)
    {
        // the query on the next line is slow
        $release_to_count = $this->get_pdb_files_counts($type);
        // the query on the next line uses too much memory
        // $release_to_count = $this->get_release_to_ife_count($type);

        $changes   = $this->get_change_counts_by_release();
        $releases  = $this->get_release_precedence();

        $query = $this->db->table('nr_classes')
            ->select('DISTINCT(nr_release_id)')
            ->where('name LIKE', $type.'%')
            ->get()
            ->getResult();
        $released = array();
        foreach ($query as $row){
            $released[] = $row->nr_release_id;
        }
        $query = $this->db->table('nr_releases')
            ->select('nr_release_id')
            ->select('date')
            ->select('description')
            ->orderBy('index','desc')
            ->whereIn('nr_release_id', $released)
            ->get()
            ->getResult();

        if ($type == 'DNA'){
            $url_type = 'dna';
        } else{
            $url_type = 'rna';
        }

        $i = 0;
        foreach ($query as $row) {
            if ($i == 0) {
                $id = anchor(base_url("nrlist/release/".$url_type."/".$row->nr_release_id), $row->nr_release_id.' (current)');
                $i++;
            } else {
                $id = anchor(base_url("nrlist/release/".$url_type."/".$row->nr_release_id), $row->nr_release_id);
            }

            if (array_key_exists($row->nr_release_id,$changes)) {
                $label = $this->get_label_type($changes[$row->nr_release_id]);
                // Remove old URL because not all comparisons are actually being produced, to avoid robots wasting time
                // $compare_url = base_url(array('nrlist','compare',$row->nr_release_id,$releases[$row->nr_release_id]));
                $compare_url = base_url(array('nrlist','compare_releases'));
                $status = "<a href='$compare_url' class='nodec'><span class='label {$label}'>{$changes[$row->nr_release_id]} changes</span></a>";
            } else {
                $status = '';
            }

            $description = $this->beautify_description_date($row->description);
            if (array_key_exists($row->nr_release_id,$release_to_count)) {
                $count = $release_to_count[$row->nr_release_id];
            } else {
                $count = 0;
            }
            $table[] = array($id, $status, $description, $count);
        }

        return $table;
    }

    function get_latest_release($molecule='rna')
    {
        if ($molecule == 'rna'){
            $group_id = 'NR_';
        } elseif ($molecule == 'dna'){
            $group_id = 'DNA_';
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // new query 2024-09-06 to account for NR or DNA
        $query = $this->db->table('nr_classes')
                ->select('nr_release_id')
                ->like('name', $group_id . '%')
                ->orderBy('nr_class_id','desc')
                ->limit(1);
        $result = $query->get()->getRow();

        if ($result) {
            return $result->nr_release_id;
        } else {
            return null;
        }
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
        $builder = $this->db->table('nr_releases');
        $query = $builder->select('nr_release_id')
                ->orderBy('index','desc');
        $query = $query->get()->getResult();

        foreach ($query as $row) {
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

        $builder = $this->db->table('nr_release_compare_counts');
        $query = $builder->select('nr_release_id')
                ->select('description')
                ->select('parent_nr_release_id')
                ->select('resolution')
                ->select('new_class_count')
                ->select('removed_class_count')
                ->select('updated_class_count')
                ->select('pdb_added_count')
                ->select('pdb_removed_count')
                ->orderBy('index', 'desc');
        $query = $query->get()->getResult();

        foreach ($query as $row) {
            if ($row->parent_nr_release_id == $releases[$row->nr_release_id]) {
                $tables[$row->resolution][] = array(
                    anchor(base_url(array('nrlist','release',$row->nr_release_id)),$row->nr_release_id),
                    $this->beautify_description_date($row->description),
                    anchor(base_url(array('nrlist','compare',$row->nr_release_id,$row->parent_nr_release_id)), $row->parent_nr_release_id),
                    $this->make_release_label($row->new_class_count),
                    $this->make_release_label($row->removed_class_count),
                    $this->make_release_label($row->updated_class_count),
                    $this->make_release_label($row->pdb_added_count),
                    $this->make_release_label($row->pdb_removed_count)
                );
            }
        }

        return $tables;
    }

    function get_release_description($id)
    {
        $builder = $this->db->table('nr_releases');
        $query = $builder->select('description')
                ->where('nr_release_id',$id);
        $query = $query->get()->getResult();

        foreach ($query as $row) {
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

    function add_space_to_long_IFE($ifename)
    {
        if (strlen($ifename) > 36) {
            $ife_set = explode('+', $ifename);
            for ($i=4; $i < count($ife_set); $i = $i + 4) {
                $ife_set[$i] = " $ife_set[$i]";
            }
            $ifename = implode("+",$ife_set);
        }
        return $ifename;
    }

    function read_chain_property_value_table()
    {
        # Query the entire cpv table. Load all data approach.
        # cpv = chain_property_value
        $builder = $this->db->table('chain_property_value AS cpv');
        $query = $builder->select('cpv.pdb_id')
            ->select('cpv.chain')
            ->select('cpv.property')
            ->select('cpv.value');
        $query = $query->get()->getResult();

        # Create dictionaries to store cpv data
        $chain_to_standardized_name = array();
        $chain_to_source = array();
        $chain_to_rfam = array();

        # populate dictionaries with pdb_chain keys
        foreach ($query as $row) {
            $row_pdb = $row->pdb_id;
            $row_chain = $row->chain;
            $row_property = $row->property;
            $row_value = $row->value;
            if ($row_property == "standardized_name"){
                $chain_to_standardized_name["{$row_pdb}_{$row_chain}"] = $row_value;
            }
            elseif ($row_property == "source") {
                $chain_to_source["{$row_pdb}_{$row_chain}"] = $row_value;
            }
            elseif ($row_property == "rfam_family"){
                $chain_to_rfam["{$row_pdb}_{$row_chain}"] = $row_value;
            }
        }

        $return_list = array($chain_to_standardized_name, $chain_to_source, $chain_to_rfam);

        return $return_list;
    }

    function read_chain_info_table()
    {
        # Query the chain_info table, omitting protein chains
        $builder = $this->db->table('chain_info AS ci');
        $query = $builder->select('ci.pdb_id')
            ->select('ci.chain_name')
            ->select('ci.compound')
            ->select('ci.entity_macromolecule_type')
            ->select('ci.source')
            ->select('ci.taxonomy_id')
            ->where('ci.entity_macromolecule_type !=', 'Polypeptide(L)')
            ->get();

        # Create dictionaries to store data
        $chain_to_taxid = array();
        $chain_to_species = array();
        $chain_to_compound = array();
        $chain_to_type = array();

        # populate dictionaries with pdb_chain keys
        foreach ($query->getResult() as $row) {
            $row_pdb = $row->pdb_id;
            $row_chain = $row->chain_name;
            $key = "{$row_pdb}_{$row_chain}";
            $chain_to_taxid[$key] = $row->taxonomy_id;
            $chain_to_species[$key] = $row->source;
            $chain_to_compound[$key] = $row->compound;
            $emt = $row->entity_macromolecule_type;
            if ($emt == 'polyribonucleotide'){
                $chain_to_type[$key] = 'RNA';
            }
            elseif ($emt == 'Polyribonucleotide (RNA)') {
                $chain_to_type[$key] = 'RNA';
            }
            elseif ($emt == 'polydeoxyribonucleotide') {
                $chain_to_type[$key] = 'DNA';
            }
            elseif ($emt == 'Polydeoxyribonucleotide (DNA)') {
                $chain_to_type[$key] = 'DNA';
            }
            elseif ($emt == 'Peptide nucleic acid') {
                $chain_to_type[$key] = 'PNA';
            }
            elseif ($emt == 'polydeoxyribonucleotide/polyribonucleotide hybrid') {
                $chain_to_type[$key] = 'hybrid';
            }
            elseif ($emt == 'DNA/RNA Hybrid') {
                $chain_to_type[$key] = 'hybrid';
            }
        }

        $return_list = array($chain_to_taxid, $chain_to_species, $chain_to_compound, $chain_to_type);

        return $return_list;
    }

    function read_taxid_species_domain_table()
    {
        # Query the taxid_species_domain table
        $builder = $this->db->table('taxid_species_domain AS tsd');
        $query = $builder->select('tsd.taxonomy_id')
            ->select('tsd.species_taxid')
            ->get();

        # Create dictionaries to store data
        $taxid_to_species_taxid = array();
        foreach ($query->getResult() as $row) {
            $taxid_to_species_taxid[$row->taxonomy_id] = $row->species_taxid;
        }

        return $taxid_to_species_taxid;
    }

    function get_release($id, $resolution, $molecule)
    // This function populates the Representative set pages
    {
        $resolution = str_replace('A', '', $resolution);
        if ($molecule == 'rna'){
            $group_id = 'NR_' . $resolution;
        } elseif ($molecule == 'dna'){
            $group_id = 'DNA_' . $resolution;
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $builder = $this->db->table('ife_info AS ii');
        $query = $builder->select('ii.ife_id, ii.pdb_id, nl.name, nc.rank')
            ->join('nr_class_rank AS nc', 'ii.ife_id = nc.ife_id')
            ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
            ->where('nl.nr_release_id', $id)
            ->where("nl.name LIKE '$group_id%'")
            ->orderBy('nc.rank','asc');
        $query = $query->get()->getResult();

        // reorganize by class and rep and pdb
        $class = array();
        $ifes = array();
        $pdbs = array();
        foreach ($query as $row) {
            $ifes[] = $row->ife_id;
            $pdbs[] = $row->pdb_id;

            if ($row->rank == 0) {
                $reps[$row->name] = $row->ife_id;
                $pdbs_rep[$row->name] = $row->pdb_id;
            }

            if (!array_key_exists($row->name, $class) ) {
                $class[$row->name] = array();
            }

            $class[$row->name][] = $row->ife_id;
        }

        $ifes = array_unique($ifes);
        $pdbs = array_unique($pdbs);

        // get general pdb info
        $builder = $this->db->table('pdb_info');
        $query = $builder->select('pdb_id, title, resolution, experimental_technique, release_date')
                ->whereIn('pdb_id', $pdbs)
                ->groupBy('pdb_id');
        $query = $query->get()->getResult();
        // Convert experimental technique to lower case equivalent (without changing pdb_info table)
        $experimental_technique = array();
        $experimental_technique['X-RAY DIFFRACTION'] = 'X-ray diffraction';
        $experimental_technique['ELECTRON MICROSCOPY'] = 'Electron microscopy';
        $experimental_technique['SOLUTION NMR'] = 'Solution NMR';
        $experimental_technique['FIBER DIFFRACTION'] = 'Fiber diffraction';
        $experimental_technique['THEORETICAL MODEL, SOLUTION NMR'] = 'Theoretical model, solution NMR';
        $experimental_technique['SOLUTION NMR, THEORETICAL MODEL'] = 'Solution NMR, theoretical model';
        $experimental_technique['FLUORESCENCE TRANSFER'] = 'Fluorescence transfer';
        $experimental_technique['NEUTRON DIFFRACTION'] = 'Neutron diffraction';
        $experimental_technique['SOLUTION NMR, SOLUTION SCATTERING'] = 'Solution NMR, solution scattering';
        $experimental_technique['SOLUTION SCATTERING, SOLUTION NMR'] = 'Solution scattering, solution NMR';
        $experimental_technique['SOLID-STATE NMR'] = 'Solid-state NMR';
        $experimental_technique['ELECTRON MICROSCOPY, SOLUTION NMR'] = 'Electron microscopy, solution NMR';
        $experimental_technique['X-RAY DIFFRACTION, SOLUTION SCATTERING'] = 'X-ray diffraction, solution scattering';


        foreach($query as $row) {
            $pdb[$row->pdb_id]['title']      = $row->title;
            $pdb[$row->pdb_id]['resolution'] = (is_null($row->resolution)) ? '' : number_format($row->resolution, 1) . ' &Aring';
            $pdb[$row->pdb_id]['experimental_technique'] = $experimental_technique[$row->experimental_technique];
            $pdb[$row->pdb_id]['release_date'] = $row->release_date;

        }

        // check if any of the files became obsolete ... not sure this still works
        $builder = $this->db->table('pdb_obsolete');
        $query = $builder->select('pdb_obsolete_id, replaced_by')
                ->whereIn('pdb_obsolete_id', $pdbs);
        $query = $query->get()->getResult();

        foreach($query as $row) {
            $pdb[$row->pdb_obsolete_id]['title'] = "OBSOLETE: replaced by <a class='pdb'>{$row->replaced_by}</a>";
            $pdb[$row->pdb_obsolete_id]['resolution'] = '';
        }

        // get annotations: "updated/>2 parents" etc.
        $builder = $this->db->table('nr_classes');
        $query = $builder->select('nr_class_id, comment')
                ->where('nr_release_id',$id)
                ->where('resolution', $resolution);
        $query = $query->get()->getResult();

        foreach ($query as $row) {
            $reason[$row->nr_class_id] = $row->comment;
            $reason_flat[]             = $row->comment;
        }

        // count all annotation types
        $counts = array_count_values($reason_flat);
        $counts_text = '';

        foreach ($counts as $comment => $count) {
            $label = $this->get_annotation_label_type($comment);
            $counts_text .= "<span class='label $label'>$comment</span> <strong>$count</strong>;    ";
        }
        $counts_text .= '<br><br>';

        // make the table
        $table = array();
        $i = 1;

        // get order
        $builder = $this->db->table('nr_class_rank AS nc');
        $query = $builder->select('nl.name')
                ->select('ii.ife_id')
                ->select('ii.pdb_id')
                ->select('ii.length AS analyzed_length')
                ->select('group_concat(DISTINCT ci.compound separator ", ") as compound', FALSE)
                ->select('ci.source as species_name')     # changed 2022-06-29
                ->select('ci.taxonomy_id AS species_id')  # changed 2022-06-29
                ->select('nl.nr_class_id')
                ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
                ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id')
                ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id')
                ->where('nl.nr_release_id', $id)
                ->where('nl.resolution', $resolution)
                ->where("nl.name LIKE '$group_id%'")
                ->groupBy('nl.name')
                ->groupBy('nl.nr_release_id')
                ->groupBy('nl.resolution')
                ->orderBy('ii.length','desc');
        $query = $query->get()->getResult();

        list($chain_to_standardized_name, $chain_to_source, $chain_to_rfam) = $this->read_chain_property_value_table();

        # fill in data for the release
        foreach ($query as $row) {
            $class_id = $row->name;
            $ife_id   = $reps[$class_id]; //Representative IFE
            $pdb_id   = $pdbs_rep[$class_id];   //representative pdb
            $tax_link = $this->tax_url . $row->species_id;

            $source   = ( is_null($row->species_name) ) ? "" : anchor_popup("$tax_link", "$row->species_name");
            $compound = (strlen($row->compound) > 40 ) ? substr($row->compound, 0, 40) . "[...]" : $row->compound;

            // this if else block could be reduced to a single explode function / made more efficient
            if (preg_match('/\+/',$ife_id)){
                $best_chains = "";
                $best_models = "";
                $ife_set     = explode('+', $ife_id);
                $idx         = 0;

                foreach ($ife_set as $each_ife){
                    $ife_split        = explode('|', $each_ife);
                    $get_chains[$idx] = $ife_split[2];
                    $get_models[$idx] = $ife_split[1];
                    $idx++;
                }

                $sort_chains = array_unique($get_chains);
                $sort_models = array_unique($get_models);
                sort($sort_chains);
                sort($sort_models);
                $best_chains = implode(', ', $sort_chains);
                $best_models = implode(', ', $sort_models);
            } elseif (count(explode('|', $ife_id)) == 3){
                $ife_split   = explode('|', $ife_id);
                $best_chains = $ife_split[2];
                $best_models = $ife_split[1];
            } else {
                ## old ifes have no model numbers, we set them as 1
                $ife_split   = explode('|', $ife_id);
                $best_chains = $ife_split[1];
                $best_models = "1";
            }

            // adding cpv data using the representative ife as the key to stdname, source and rfam dicts.
            $ife_chain_list = explode('+', $ife_id);
            $rfam_representative = "";
            $source_representative = "";
            $standardized_name_representative = "";
            foreach ($ife_chain_list as $ife_chain){
                $ife_split = explode('|', $ife_chain);
                $ife_pdb_id = $ife_split[0];
                $chain = end($ife_split);
                if (array_key_exists("{$ife_pdb_id}_{$chain}", $chain_to_rfam)){
                    $rfam_representative .= $chain_to_rfam["{$ife_pdb_id}_{$chain}"] . " + ";
                }
                if (array_key_exists("{$ife_pdb_id}_{$chain}", $chain_to_source)){
                    $source_representative = $chain_to_source["{$ife_pdb_id}_{$chain}"];
                }
                if (array_key_exists("{$ife_pdb_id}_{$chain}", $chain_to_standardized_name)){
                    $stdname = $chain_to_standardized_name["{$ife_pdb_id}_{$chain}"];
                    $names = explode(';', $stdname);
                    $short_name = end($names);
                    $standardized_name_representative .= $short_name . " + ";
                }

            }
            //creating a single string with <li> tags corresponding to the cpv data.
            // If a cpv datum is none for the ife, that datum will not be listed.
            $cpv_html_list_item = "";
            if (!empty($standardized_name_representative)){
                $standardized_name_representative = substr($standardized_name_representative, 0, -3);
                $cpv_html_list_item .= '<li>Standardized name: ' . $standardized_name_representative . '</li>';
            }
            if (!empty($source_representative)){
                $biological_context = "Source";
                // $biological_context = "source";
                // if ($source_representative == "Mitochondria" or $source_representative == "Chloroplast"){
                    // $biological_context = "Organelle";
                // } elseif ($source_representative == "Synthetic"){
                    // $biological_context = "Source";
                // }
                $cpv_html_list_item .= '<li>' . $biological_context . ': ' . $source_representative . '</li>';
            }
            if (!empty($rfam_representative)){
                $rfam_representative = substr($rfam_representative, 0, -3);
                $cpv_html_list_item .= '<li>Rfam: ' . $rfam_representative . '</li>';
            }

            # read pdb_property_value table for annotation information
            $builder = $this->db->table('pdb_property_value AS ppv');
            $query_property = $builder->select('ppv.pdb_id')
                    ->select('ppv.property')
                    ->select('ppv.value')
                    ->where('ppv.pdb_id',$row->pdb_id);
            $query_property = $query_property->get();
            // $property_value = array();
            // foreach ($query_property->result as $row){
            //     $property_value[$row->property] = ($row->value ?: "NULL");
            // }
            if ($query_property->getNumRows() > 0) {
                $property_value = array();
                foreach ($query_property->getResult() as $row1) {
                    $property_value[$row1->property] = ($row1->value ?: "NULL");
                }
            } else {
                $property_value['NAKB_NA_annotation'] = 'NULL';
                $property_value['NAKB_protein_annotation'] = 'NULL';
            }

            // $id refers to the release_id
            if ($molecule == 'dna'){
                $table[] = array($i,
                                anchor(base_url("nrlist/view/".$class_id),$class_id)
                                #anchor(base_url("nrlist/view/".$class_id."/".$id),$class_id,$id)
                                . '<br>' . $this->add_annotation_label($row->nr_class_id, $reason)
                                . '<br>' . $source,
                                $this->add_space_to_long_IFE($ife_id) . ' (<a class="pdb">' . $pdb_id . '</a>)' .
                                '<ul>' .
                                '<li>' . $compound . '</li>' .
                                '<li>' . $pdb[$pdb_id]['experimental_technique'] . '</li>' .
                                //  '<li>Chain(s): ' . $best_chains . '; model(s): ' . $best_models . '</li>' .
                                '<li>Release Date: ' . $pdb[$pdb_id]['release_date'] . '</li>' .
                                $cpv_html_list_item.
                                //'<li>' . $pdb[$pdb_id]['release_date'] '</li>' .//
                                '</ul>',
                                $pdb[$pdb_id]['resolution'],
                                $row->analyzed_length,
                                #$row->analyzed_length . '&nbsp;(analyzed)<br>' .
                                #$row->experimental_length . '&nbsp;(experimental)',
                                "(" . $this->count_pdb_class($class[$class_id]) . ") " . $this->add_pdb_class($class[$class_id]),
                                #"(" . $nums . "," . $this->count_pdb_class($class[$class_id]) . ") " . $this->add_pdb_class($class[$class_id])
                                ($property_value['NAKB_NA_annotation'] ?: 'NULL'),
                                ($property_value['NAKB_protein_annotation'] ?: 'NULL')
                                // '1','1'
                                );
                }else{
                    $table[] = array($i,
                                anchor(base_url("nrlist/view/".$class_id),$class_id)
                                #anchor(base_url("nrlist/view/".$class_id."/".$id),$class_id,$id)
                                . '<br>' . $this->add_annotation_label($row->nr_class_id, $reason)
                                . '<br>' . $source,
                                $this->add_space_to_long_IFE($ife_id) . ' (<a class="pdb">' . $pdb_id . '</a>)' .
                                '<ul>' .
                                '<li>' . $compound . '</li>' .
                                '<li>' . $pdb[$pdb_id]['experimental_technique'] . '</li>' .
                                //  '<li>Chain(s): ' . $best_chains . '; model(s): ' . $best_models . '</li>' .
                                '<li>Release Date: ' . $pdb[$pdb_id]['release_date'] . '</li>' .
                                $cpv_html_list_item.
                                //'<li>' . $pdb[$pdb_id]['release_date'] '</li>' .//
                                '</ul>',
                                $pdb[$pdb_id]['resolution'],
                                $row->analyzed_length,
                                #$row->analyzed_length . '&nbsp;(analyzed)<br>' .
                                #$row->experimental_length . '&nbsp;(experimental)',
                                "(" . $this->count_pdb_class($class[$class_id]) . ") " . $this->add_pdb_class($class[$class_id])
                                );
                }
            $i++;
        }

        return array('table' => $table, 'counts' => $counts_text);
    }

    function count_all_nucleotides($pdb_id)
    {
        $builder = $this->db->table('unit_info');
        $query = $builder->select('count(unit_id) as length')
                ->where('pdb_id', $pdb_id)
                ->whereIn('unit', array('A','C','G','U'))
                ->orderBy('count(*)', 'DESC')
                ->limit(1);
        $query = $query->get()->getResult();

        $result = $query;
        return $result[0]->length;
    }

    function get_csv($release, $resolution, $type)
    // Retrieve information on one representative set release
    {
        $resolution = str_replace('A', '', $resolution);
        $builder = $this->db->table('nr_class_rank AS nc');
        $query = $builder->select('ii.ife_id as id, nl.name as class_id, nc.rank')
                ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
                ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                ->where('nl.nr_release_id', $release)
                ->where('resolution', $resolution)
                ->where('nl.name LIKE', $type."%")
                ->orderBy('nc.rank','asc');
        $query = $query->get()->getResult();

        foreach($query as $row) {
            if ( $row->rank == 0 ) {
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

    function get_csv_full($release, $resolution, $type, $format)
    # Retrieve information on one representative set release at given resolution
    # $type is NR for RNA or DNA for DNA
    {

        # set resolution cutoff
        $resolution = str_replace('A', '', $resolution);
        $builder = $this->db->table('nr_class_rank AS ncr');
        $query = $builder->select('ncr.ife_id as id')
                ->select('nl.name as class_id')
                ->select('ncr.rank')
                ->select('cqs.nr_name as nr_name')
                ->select('cqs.composite_quality_score as cq')
                ->select('cqs.percent_observed')
                ->select('cqs.maximum_experimental_length')
                ->select('ic.average_rsr')
                ->select('ic.average_rscc')
                ->select('ic.obs_length')
                ->select('ic.percent_clash')
                ->select('ic.rfree')
                ->select('pi.resolution')
                ->select('pi.experimental_technique')
                ->select('pi.release_date')
                ->select('pi.title')
                ->join('nr_classes AS nl', 'ncr.nr_class_name = nl.name')
                ->join('nr_cqs AS cqs', 'ncr.ife_id = cqs.ife_id','inner')
                ->join('ife_cqs as ic','ic.ife_id = ncr.ife_id')
                ->join('ife_info as ii','ii.ife_id = ncr.ife_id')
                ->join('pdb_info as pi','pi.pdb_id = ii.pdb_id')
                ->where('nl.nr_release_id', $release)
                ->where('nl.resolution', $resolution)
                ->where('nl.name LIKE', $type."%")
                ->where('REPLACE(cqs.nr_name, "all", "'.$resolution.'") = nl.name')
                ->orderBy('nl.name','asc')
                ->orderBy('ncr.rank','asc');
        $query = $query->get()->getResult();

        list($chain_to_standardized_name, $chain_to_source, $chain_to_rfam) = $this->read_chain_property_value_table();

        list($chain_to_taxid, $chain_to_species, $chain_to_compound, $chain_to_type) = $this->read_chain_info_table();

        $taxid_to_species_taxid = $this->read_taxid_species_domain_table();

        # get maximum observed length for each Rfam family
        $rfam_to_max_observed = array();
        foreach($query as $row) {
            # split ife id into chains
            $chains = explode('+',$row->id);
            $rfams = array();
            foreach ($chains as $chain) {
                $chain_fields = explode('|',$chain);
                if (count($chain_fields) == 3) {
                    # split ife id into chains (pdb_id, model, chain_id
                    $pdb_id = $chain_fields[0];
                    $chain_id = $chain_fields[2];
                    $key = "{$pdb_id}_{$chain_id}";
                    if (array_key_exists($key,$chain_to_rfam)){
                        $rfam = $chain_to_rfam[$key];
                        if (array_key_exists($rfam,$rfam_to_max_observed)){
                            $rfam_to_max_observed[$rfam] = max($rfam_to_max_observed[$rfam],$row->obs_length);
                        } else {
                            $rfam_to_max_observed[$rfam] = $row->obs_length;
                        }
                        # nr.cqs stores the maximum over the equivalence class at "all" resolution
                        # this will likely give a larger value than the calculation above
                        // if ($row->maximum_experimental_length > $rfam_to_max_observed[$rfam]) {
                        //     $rfam_to_max_observed[$rfam] = $row->maximum_experimental_length;
                        // }
                    }
                }
            }
        }

        // because of stapled ribosomes, joint 5S+23S, and other cases,
        // we need to set a maximum observed length for some Rfam families
        $rfam_max_allowed = $this->set_rfam_max_allowed();
        foreach($rfam_max_allowed as $rfam => $max_allowed) {
            if (array_key_exists($rfam,$rfam_to_max_observed)) {
                $rfam_to_max_observed[$rfam] = min($rfam_to_max_observed[$rfam],$max_allowed);
            }
        }

        $header_values = array("ec_id","ife_id","pdb_resolution","na_type","ec_rank","ec_cqs","average_rsr","average_rscc","percent_clash","rfree","ec_fraction_observed","nts_observed","rfam_reference_nts","rfam_fraction_observed","rfam_cqs","source","rfam","standardized_name","pdb_species","pdb_taxid","species_taxid","pdb_description","pdb_release_date","pdb_experimental_technique","pdb_title");
        if ($format == 'csv'){
            $header = '"' . implode('","',$header_values) . '"' . "\n";
        } elseif ($format == 'tsv'){
            $header = implode("\t",$header_values) . "\n";
        }

        $all_lines = array();
        $all_lines[] = $header;
        foreach($query as $row) {
            $compounds = array();
            $types = array();
            $taxids = array();
            $species = array();
            $species_taxids = array();
            $rfams = array();
            $sources = array();
            $standardized_names = array();
            $nts_observed = 0;
            $rfam_max_observed = 0;
            $taxid = '';
            $species_taxid = '';

            # split ife id into chains
            $chains = explode('+',$row->id);
            foreach($chains as $chain){
                $chain_fields = explode('|',$chain);
                $pdb_id = $chain_fields[0];
                $chain_id = $chain_fields[2];
                $key = "{$pdb_id}_{$chain_id}";
                if (array_key_exists($key,$chain_to_compound)){
                    $compounds[] = $chain_to_compound[$key];
                } else {
                    $compounds[] = 'NA';
                }
                if (array_key_exists($key,$chain_to_type)){
                    $types[] = $chain_to_type[$key];
                } else {
                    $types[] = 'NA';
                }
                if (array_key_exists($key,$chain_to_species)){
                    $species[] = $chain_to_species[$key];
                } else {
                    $species[] = 'NA';
                }
                if (array_key_exists($key,$chain_to_taxid)){
                    $taxid = $chain_to_taxid[$key];
                    $taxids[] = $taxid;
                    if (array_key_exists($taxid,$taxid_to_species_taxid)){
                        $species_taxid = $taxid_to_species_taxid[$taxid];
                        $species_taxids[] = $species_taxid;
                    } else {
                        $species_taxids[] = 'NA';
                    }
                } else {
                    $taxids[] = 'NA';
                    $species_taxids[] = 'NA';
                }
                if (array_key_exists($key,$chain_to_standardized_name)){
                    $standardized_names[] = $chain_to_standardized_name[$key];
                } else {
                    $standardized_names[] = 'NA';
                }
                if (array_key_exists($key,$chain_to_source)){
                    $sources[] = $chain_to_source[$key];
                } else {
                    $sources[] = 'NA';
                }
                if (array_key_exists($key,$chain_to_rfam)){
                    $rfam = $chain_to_rfam[$key];
                    $rfams[] = $rfam;
                    if (array_key_exists($rfam,$rfam_to_max_observed)) {
                        $rfam_max_observed = $rfam_max_observed + $rfam_to_max_observed[$rfam];
                    }
                } else {
                    $rfams[] = 'NA';
                }
            }
            $compound = implode('+',$compounds);
            $type = implode('+',$types);
            $taxid = implode('+',$taxids);
            $species = implode('+',$species);
            $species_taxid = implode('+',$species_taxids);
            $standardized_name = implode('+',$standardized_names);
            $source = implode('+',$sources);
            $rfam = implode('+',$rfams);

            if ($rfam_max_observed == 0){
                $rfam_fraction_observed = 0;
            } else {
                $rfam_fraction_observed = $row->obs_length / $rfam_max_observed;
                // Some stapled or joint molecules have artificially large numbers
                if ($rfam_fraction_observed > 1){
                    $rfam_fraction_observed = 1;
                }
            }

            // COMPSCORE_COEFFICENTS = {
            //     'resolution': 1,
            //     'average_rsr': 8,
            //     'percent_clash': 0.6,
            //     'average_rscc': 8,
            //     'rfree': 18,
            //     'fraction_unobserved': 4,
            // }
            // compscore = COMPSCORE_COEFFICENTS['resolution'] * member[0]['resolution']
            // compscore += COMPSCORE_COEFFICENTS['percent_clash'] * member[0]['percent_clash']
            // compscore += COMPSCORE_COEFFICENTS['average_rsr'] * member[0]['average_rsr']
            // compscore += COMPSCORE_COEFFICENTS['average_rscc'] * (1 - member[0]['average_rscc'])
            // compscore += COMPSCORE_COEFFICENTS['rfree'] * member[0]['rfree']
            // compscore += COMPSCORE_COEFFICENTS['fraction_unobserved'] * member[0]['fraction_unobserved']

            $rfam_cqs = 1 * $row->resolution + 8 * $row->average_rsr + 0.6 * ($row->percent_clash);
            $rfam_cqs = $rfam_cqs + 8 * (1 - $row->average_rscc) + 18 * $row->rfree + 4 * (1 - $rfam_fraction_observed);

            // make an array with all of the data fields in $row
            $data = array($row->class_id,$row->id,$row->resolution,$type,($row->rank+1),$row->cq,
                            $row->average_rsr,$row->average_rscc,$row->percent_clash,$row->rfree,$row->percent_observed,$row->obs_length,$rfam_max_observed,$rfam_fraction_observed,$rfam_cqs,
                            $source,$rfam,$standardized_name,
                            $species,$taxid,$species_taxid,$compound,
                            $row->release_date,$row->experimental_technique,$row->title);

            if ($format == 'csv'){
                // convert $data to a string that is comma separated and has double quotation marks
                $line = '"' . implode('","', $data) . '"' . "\n";
            } else {
                // convert $data to a string that is tab separated
                $line = implode("\t", $data) . "\n";
            }
            // append $data to a $all_lines
            $all_lines[] = $line;
        }

        // concatenate $all_lines into string
        $final = implode('', $all_lines);

        return $final;
    }

    function get_compare_radio_table()
    {
        $changes = $this->get_change_counts_by_release();

        $query = $this->db->table('nr_releases')
                ->select('nr_release_id AS id, description')
                ->orderBy('index','desc');
        $query = $query->get()->getResult();

        helper('form');

        $table = array();
        foreach ($query as $row) {
            if (array_key_exists($row->id,$changes)) {
                $label_type = $this->get_label_type($changes[$row->id]);
                $label = " <span class='label {$label_type}'>{$changes[$row->id]} changes</span>";
            } else {
                $label = '';
            }

            $attributes = ['name' => 'release1', 'value' => $row->id];
            $radio = form_radio($attributes);
            $table[] = $radio . $row->id . $label;

            $attributes = ['name' => 'release2', 'value' => $row->id];
            $radio = form_radio($attributes);
            $table[] = $radio . $row->id;

            $table[] = $this->beautify_description_date($row->description);
        }

        return $table;
    }

    function is_valid_release($id)
    {
        $builder = $this->db->table('nr_releases');
        $query = $builder->select('nr_release_id')
                ->where('nr_release_id', $id)
                ->limit(1);

        if ( $query->get()->getNumRows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

    function is_valid_class($id)
    {
        $builder = $this->db->table('nr_classes');
        $query = $builder->select('name')
                ->where('name', $id)
                ->limit(1);

        if ( $query->get()->getNumRows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

    function get_two_newest_releases()
    {
        $builder = $this->db->table('nr_releases');
        $query = $builder->select('nr_release_id')
                ->select('parent_nr_release_id')
                ->orderBy('index', 'desc')
                ->limit(1);
        $query = $query->get()->getResult();

        foreach ($query as $row){
            $rel1 = $row->nr_release_id;
            $rel2 = $row->parent_nr_release_id;
        }

        return array($rel1, $rel2);
    }

    function set_rfam_max_allowed()
    {
        // these values are taken from the infernal matches to chains on 2024-08-20
        // set absolute maximum values for scoring fraction observed
        // Avoids trouble with stapled ribosomes, joint 5S+23S, and other cases
        // Also avoids giving too much credit to very long viral chains, CRISPR, things
        // that can be much longer than the functional part of the molecule
        // These were checked down to nts_observed / rfam_reference_observed = 1.2
        $rfam_max_allowed = array();
        $rfam_max_allowed['RF00001'] = 126;  # 5S rRNA, some are joint with 23S
        $rfam_max_allowed['RF00002'] = 169;
        $rfam_max_allowed['RF00003'] = 164;
        $rfam_max_allowed['RF00004'] = 228;  // 8RO1|1|2
        $rfam_max_allowed['RF00005'] = 93;   # 8CBK|1|T; viruses have longer matches to Infernal
        $rfam_max_allowed['RF00007'] = 150;
        $rfam_max_allowed['RF00008'] = 56;
        $rfam_max_allowed['RF00009'] = 358;
        $rfam_max_allowed['RF00010'] = 376;
        $rfam_max_allowed['RF00011'] = 397;
        $rfam_max_allowed['RF00012'] = 217;
        $rfam_max_allowed['RF00013'] = 125;
        $rfam_max_allowed['RF00015'] = 161;
        $rfam_max_allowed['RF00017'] = 299;
        $rfam_max_allowed['RF00020'] = 179;    // using 6J6G|1|D
        $rfam_max_allowed['RF00023'] = 377;
        $rfam_max_allowed['RF00024'] = 438;
        $rfam_max_allowed['RF00025'] = 159;
        $rfam_max_allowed['RF00026'] = 107;
        $rfam_max_allowed['RF00027'] = 70;
        $rfam_max_allowed['RF00028'] = 434;    // group I intron, using 7XD6|1|N
        $rfam_max_allowed['RF00029'] = 866;    // group II intron, Rfam aligns only 98, using
        $rfam_max_allowed['RF00030'] = 332;
        $rfam_max_allowed['RF00031'] = 66;
        $rfam_max_allowed['RF00032'] = 46;
        $rfam_max_allowed['RF00036'] = 67;
        $rfam_max_allowed['RF00037'] = 30;
        $rfam_max_allowed['RF00044'] = 118;
        $rfam_max_allowed['RF00050'] = 112;
        $rfam_max_allowed['RF00059'] = 85;
        $rfam_max_allowed['RF00061'] = 256;
        $rfam_max_allowed['RF00066'] = 60;
        $rfam_max_allowed['RF00075'] = 101;
        $rfam_max_allowed['RF00080'] = 85;
        $rfam_max_allowed['RF00083'] = 207;
        $rfam_max_allowed['RF00094'] = 75;
        $rfam_max_allowed['RF00100'] = 57;
        $rfam_max_allowed['RF00102'] = 111;
        $rfam_max_allowed['RF00114'] = 114;
        $rfam_max_allowed['RF00161'] = 53;
        $rfam_max_allowed['RF00162'] = 125;
        $rfam_max_allowed['RF00163'] = 33;
        $rfam_max_allowed['RF00164'] = 43;
        $rfam_max_allowed['RF00166'] = 72;
        $rfam_max_allowed['RF00167'] = 69;
        $rfam_max_allowed['RF00168'] = 170;
        $rfam_max_allowed['RF00169'] = 99;
        $rfam_max_allowed['RF00173'] = 36;
        $rfam_max_allowed['RF00174'] = 177;
        $rfam_max_allowed['RF00175'] = 40;
        $rfam_max_allowed['RF00177'] = 1808;  // bacterial SSU
        $rfam_max_allowed['RF00180'] = 11;
        $rfam_max_allowed['RF00185'] = 95;
        $rfam_max_allowed['RF00207'] = 48;
        $rfam_max_allowed['RF00209'] = 233;
        $rfam_max_allowed['RF00210'] = 108;
        $rfam_max_allowed['RF00220'] = 33;
        $rfam_max_allowed['RF00228'] = 92;
        $rfam_max_allowed['RF00230'] = 168;
        $rfam_max_allowed['RF00233'] = 86;
        $rfam_max_allowed['RF00234'] = 141;
        $rfam_max_allowed['RF00240'] = 70;
        $rfam_max_allowed['RF00250'] = 58;
        $rfam_max_allowed['RF00254'] = 81;
        $rfam_max_allowed['RF00270'] = 13;
        $rfam_max_allowed['RF00373'] = 257;
        $rfam_max_allowed['RF00374'] = 101;
        $rfam_max_allowed['RF00375'] = 99;
        $rfam_max_allowed['RF00379'] = 124;
        $rfam_max_allowed['RF00380'] = 158;
        $rfam_max_allowed['RF00382'] = 36;
        $rfam_max_allowed['RF00386'] = 92;
        $rfam_max_allowed['RF00390'] = 23;
        $rfam_max_allowed['RF00436'] = 31;
        $rfam_max_allowed['RF00442'] = 126;
        $rfam_max_allowed['RF00455'] = 59;
        $rfam_max_allowed['RF00458'] = 200;
        $rfam_max_allowed['RF00480'] = 45;
        $rfam_max_allowed['RF00488'] = 568;
        $rfam_max_allowed['RF00500'] = 45;
        $rfam_max_allowed['RF00504'] = 230;  // glycine riboswitch, using 6WLT|1|A
        $rfam_max_allowed['RF00505'] = 65;
        $rfam_max_allowed['RF00507'] = 78;
        $rfam_max_allowed['RF00522'] = 40;
        $rfam_max_allowed['RF00525'] = 81;
        $rfam_max_allowed['RF00548'] = 134;
        $rfam_max_allowed['RF00610'] = 14;
        $rfam_max_allowed['RF00617'] = 14;
        $rfam_max_allowed['RF00618'] = 127;
        $rfam_max_allowed['RF00619'] = 125;
        $rfam_max_allowed['RF00622'] = 69;
        $rfam_max_allowed['RF00634'] = 119;
        $rfam_max_allowed['RF00658'] = 31;
        $rfam_max_allowed['RF00661'] = 71;
        $rfam_max_allowed['RF00843'] = 82;
        $rfam_max_allowed['RF00957'] = 93;
        $rfam_max_allowed['RF01047'] = 61;
        $rfam_max_allowed['RF01051'] = 91;
        $rfam_max_allowed['RF01054'] = 59;
        $rfam_max_allowed['RF01057'] = 54;
        $rfam_max_allowed['RF01068'] = 8;
        $rfam_max_allowed['RF01073'] = 59;
        $rfam_max_allowed['RF01080'] = 9;
        $rfam_max_allowed['RF01081'] = 13;
        $rfam_max_allowed['RF01083'] = 14;
        $rfam_max_allowed['RF01084'] = 129;
        $rfam_max_allowed['RF01097'] = 35;
        $rfam_max_allowed['RF01103'] = 18;
        $rfam_max_allowed['RF01111'] = 13;
        $rfam_max_allowed['RF01120'] = 10;
        $rfam_max_allowed['RF01303'] = 10;
        $rfam_max_allowed['RF01315'] = 13;
        $rfam_max_allowed['RF01317'] = 21;
        $rfam_max_allowed['RF01319'] = 8;
        $rfam_max_allowed['RF01321'] = 12;
        $rfam_max_allowed['RF01322'] = 11;
        $rfam_max_allowed['RF01325'] = 10;
        $rfam_max_allowed['RF01330'] = 37;
        $rfam_max_allowed['RF01335'] = 30;
        $rfam_max_allowed['RF01338'] = 14;
        $rfam_max_allowed['RF01343'] = 30;
        $rfam_max_allowed['RF01344'] = 52;
        $rfam_max_allowed['RF01346'] = 8;
        $rfam_max_allowed['RF01347'] = 14;
        $rfam_max_allowed['RF01355'] = 8;
        $rfam_max_allowed['RF01358'] = 16;
        $rfam_max_allowed['RF01363'] = 56;
        $rfam_max_allowed['RF01375'] = 31;
        $rfam_max_allowed['RF01380'] = 19;
        $rfam_max_allowed['RF01381'] = 23;
        $rfam_max_allowed['RF01394'] = 16;
        $rfam_max_allowed['RF01415'] = 68;
        $rfam_max_allowed['RF01510'] = 64;
        $rfam_max_allowed['RF01666'] = 46;
        $rfam_max_allowed['RF01684'] = 58;
        $rfam_max_allowed['RF01689'] = 83;
        $rfam_max_allowed['RF01704'] = 50;
        $rfam_max_allowed['RF01716'] = 15;
        $rfam_max_allowed['RF01725'] = 96;
        $rfam_max_allowed['RF01727'] = 43;
        $rfam_max_allowed['RF01734'] = 51;
        $rfam_max_allowed['RF01739'] = 61;
        $rfam_max_allowed['RF01750'] = 75;
        $rfam_max_allowed['RF01763'] = 41;
        $rfam_max_allowed['RF01764'] = 127;
        $rfam_max_allowed['RF01767'] = 51;
        $rfam_max_allowed['RF01786'] = 74;
        $rfam_max_allowed['RF01790'] = 10;
        $rfam_max_allowed['RF01792'] = 9;
        $rfam_max_allowed['RF01807'] = 184;
        $rfam_max_allowed['RF01826'] = 49;
        $rfam_max_allowed['RF01831'] = 99;
        $rfam_max_allowed['RF01834'] = 11;
        $rfam_max_allowed['RF01835'] = 30;
        $rfam_max_allowed['RF01836'] = 17;
        $rfam_max_allowed['RF01846'] = 333;
        $rfam_max_allowed['RF01852'] = 95;
        $rfam_max_allowed['RF01854'] = 264;
        $rfam_max_allowed['RF01856'] = 95;
        $rfam_max_allowed['RF01857'] = 136;
        $rfam_max_allowed['RF01959'] = 1497;  // archaeal SSU
        $rfam_max_allowed['RF01960'] = 2318;  // eukaryotic SSU
        $rfam_max_allowed['RF01988'] = 36;
        $rfam_max_allowed['RF01998'] = 414;   // Group II intron; infernal 84, but 414 is the max observed
        $rfam_max_allowed['RF02001'] = 390;   // Group II intron; infernal 177
        $rfam_max_allowed['RF02012'] = 158;
        $rfam_max_allowed['RF02033'] = 248;
        $rfam_max_allowed['RF02064'] = 26;
        $rfam_max_allowed['RF02095'] = 71;
        $rfam_max_allowed['RF02253'] = 29;
        $rfam_max_allowed['RF02340'] = 71;
        $rfam_max_allowed['RF02348'] = 79;
        $rfam_max_allowed['RF02359'] = 36;
        $rfam_max_allowed['RF02399'] = 8;
        $rfam_max_allowed['RF02448'] = 15;
        $rfam_max_allowed['RF02519'] = 34;
        $rfam_max_allowed['RF02521'] = 85;
        $rfam_max_allowed['RF02540'] = 3049;  // archaeal LSU
        $rfam_max_allowed['RF02541'] = 3393;  // bacterial LSU
        $rfam_max_allowed['RF02542'] = 1400;  // microsporidia SSU
        $rfam_max_allowed['RF02543'] = 5067;  // eukaryotic LSU
        $rfam_max_allowed['RF02545'] = 627;   // Trypanosomatid mitochondria SSU
        $rfam_max_allowed['RF02546'] = 1176;  // Trypanosomatid mitochondria LSU, using 6YXX|1|AA
        $rfam_max_allowed['RF02547'] = 95;
        $rfam_max_allowed['RF02553'] = 80;
        $rfam_max_allowed['RF02597'] = 12;
        $rfam_max_allowed['RF02678'] = 81;
        $rfam_max_allowed['RF02679'] = 51;
        $rfam_max_allowed['RF02680'] = 101;
        $rfam_max_allowed['RF02681'] = 61;
        $rfam_max_allowed['RF02683'] = 86;
        $rfam_max_allowed['RF02747'] = 64;
        $rfam_max_allowed['RF02765'] = 12;
        $rfam_max_allowed['RF02796'] = 57;
        $rfam_max_allowed['RF02885'] = 51;
        $rfam_max_allowed['RF02977'] = 39;
        $rfam_max_allowed['RF02990'] = 12;
        $rfam_max_allowed['RF03013'] = 56;
        $rfam_max_allowed['RF03054'] = 44;
        $rfam_max_allowed['RF03064'] = 73;
        $rfam_max_allowed['RF03117'] = 157;
        $rfam_max_allowed['RF03120'] = 145;
        $rfam_max_allowed['RF03125'] = 40;
        $rfam_max_allowed['RF03128'] = 11;
        $rfam_max_allowed['RF03130'] = 16;
        $rfam_max_allowed['RF03131'] = 22;
        $rfam_max_allowed['RF03160'] = 56;
        $rfam_max_allowed['RF03231'] = 19;
        $rfam_max_allowed['RF03803'] = 14;
        $rfam_max_allowed['RF03819'] = 72;
        $rfam_max_allowed['RF03852'] = 48;
        $rfam_max_allowed['RF04036'] = 65;
        $rfam_max_allowed['RF04104'] = 118;
        $rfam_max_allowed['RF04190'] = 68;
        $rfam_max_allowed['RF04222'] = 50;

        return $rfam_max_allowed;
    }
}




/* End of file nrlist_model.php */
/* Location: ./application/model/nrlist_model.php */
/* before delete */
