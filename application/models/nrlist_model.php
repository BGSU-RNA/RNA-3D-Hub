<?php

ini_set("memory_limit","512M");

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
        $this->tax_url = 'http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=';
        // Call the Model constructor
        parent::__construct();
        //$this->load->database();

    }

    function count_motifs($rel)
    {
        $this->db->select('resolution, count(nr_class_id) as ids')
                 ->from('nr_classes')
                 ->where('nr_release_id', $rel)
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

        $sql = "CALL nr_release_diff(?,?)";
        $par = array($rel1, $rel2);

        $query = $this->db->query($sql, $par);

        if ($query->num_rows == 0) {
            $par = array($rel2,$rel1);
            $query = $this->db->query($sql, $par);
        }

        foreach ($query->result() as $row) {
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
        $this->db
                 #->select('nrc.nr_class_id')
                 #->select('nrc.name')
                 ->select('nrc.nr_release_id')
                 #->select('nrc.resolution')
                 #->select('nrc.handle')
                 #->select('nrc.version')
                 #->select('nrc.comment')
                 /*->select('nrr.nr_release_id')*/
                 #->select('nrr.date')
                 ->select('nrr.description')
                 ->from('nr_classes AS nrc')
                 ->join('nr_releases AS nrr','nrc.nr_release_id = nrr.nr_release_id')
                 ->where('nrc.name',$id)
                 ->order_by('nrr.date');
        $query = $this->db->get();
        $releases[0][0] = 'Release';
        $releases[1][0] = 'Date';
        $i = 0;
        foreach ($query->result() as $row) {
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

    function get_status($id)
    {
        /* TODO:  Reduce to nr_release_id? */
        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index','desc')
                 ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $current_release = $row->nr_release_id;
        }

        $this->current_release = $current_release;

        /* TODO:  Reduce to one column? */
        $this->db->select('nr_class_id')
                 ->select('name')
                 ->select('nr_release_id')
                 ->select('resolution')
                 ->select('handle')
                 ->select('version')
                 ->select('comment')
                 ->from('nr_classes')
                 ->where('name',$id)
                 ->where('nr_release_id',$current_release);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return 'Current';
        } else {
            $this->db->select('nr_class_id')
                    ->select('name')
                    ->select('nr_release_id')
                    ->select('resolution')
                    ->select('handle')
                    ->select('version')
                    ->select('comment')
                    ->from('nr_classes')
                    ->where('name',$id);
            $query = $this->db->get();
            if (substr($query->row()->name,0,3)=='DNA'){
                return 'Current';
            }
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

        $this->db->select('source, taxonomy_id')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb_id)
                 ->where('chain_name', $chain);
        $query = $this->db->get();

        if ( $query->num_rows() > 0 ) {
            $result = $query->result();
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

    // function get_members($id)
    // {
    //     $this->db->select('pi.pdb_id')
    //              ->select('ch.ife_id')
    //              ->select('pi.title')
    //              ->select('pi.experimental_technique')
    //              ->select('pi.release_date')
    //              ->select('pi.resolution')
    //              ->from('pdb_info AS pi')
    //              ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
    //              ->join('nr_chains AS ch', 'ii.ife_id = ch.ife_id')
    //              ->join('nr_classes AS cl', 'ch.nr_class_id = cl.nr_class_id AND ch.nr_release_id = cl.nr_release_id')
    //              ->where('cl.name',$id)
    //              #->where('nch.nr_release_id',$this->last_seen_in) # what was this doing? still necessary?
    //              ->group_by('pi.pdb_id')
    //              ->group_by('ii.ife_id')
    //              ->order_by('ch.rep','desc');
    //     $query = $this->db->get();

    //     $i = 0;
    //     $table = array();

    //     foreach ($query->result() as $row) {
    //         $link = $this->make_pdb_widget_link(str_replace('+','+ ',$row->ife_id));

    //         if ( $i==0 ) {
    //             $link = $link . ' <strong>(rep)</strong>';
    //         }

    //         $i++;

    //         $table[] = array($i,
    //                          $link,
    //                          $this->get_compound_single($row->ife_id),
    //                          #  may add get_compound_list as popover
    //                          #  to get_compound_single field
    //                          #$this->get_compound_list($row->pdb_id),
    //                          $this->get_source_organism($row->ife_id),
    //                          $row->title,
    //                          $row->experimental_technique,
    //                          $row->resolution,
    //                          $row->release_date);
    //     }

    //     return $table;
    // }

    function get_members($id)
    {
        $this->db->select('pi.pdb_id')
                 ->select('ch.ife_id')
                 ->select('pi.title')
                 ->select('pi.experimental_technique')
                 ->select('pi.release_date')
                 ->select('pi.resolution')
                 ->from('pdb_info AS pi')
                 ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                 ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
                 ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
                 ->where('cl.name',$id)
                 ->where('cl.nr_release_id',$this->last_seen_in) # copy from the comment function above
                 ->group_by('pi.pdb_id')
                 ->group_by('ii.ife_id')
                 ->order_by('ch.rank','asc');
        $query = $this->db->get();

        # get chain, source, rfam, standardized names for pdb ids in this equivalence class
        # query on 2024-03-30 gets multiple lines from nr_classes whose name matches $id
        #
        // $this->db->select('ii.pdb_id')
        //          ->select('ch.ife_id')
        //          ->select('cpv.chain')
        //          ->select('cpv.property')
        //          ->select('cpv.value')
        //          ->from('ife_info AS ii')
        //          ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
        //          ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
        //          ->join('chain_property_value AS cpv', 'cpv.pdb_id = ii.pdb_id')
        //          ->where('cl.name',$id);
        # new version, skip the join between nr_classes and nr_class_rank
        $this->db->select('ii.pdb_id')
                 ->select('ch.ife_id')
                 ->select('cpv.chain')
                 ->select('cpv.property')
                 ->select('cpv.value')
                 ->from('ife_info AS ii')
                 ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
                 ->join('chain_property_value AS cpv', 'cpv.pdb_id = ii.pdb_id')
                 ->where('ch.nr_class_name',$id);
        $query_cpv = $this->db->get();

        // converting experimental_technique without changing pdb_info table.
        $experimental_technique = array();
        $experimental_technique['X-RAY DIFFRACTION'] = 'X-ray diffraction';
        $experimental_technique['ELECTRON MICROSCOPY'] = 'Electron microscopy';
        $experimental_technique['SOLUTION NMR'] = 'Solution NMR';
        $experimental_technique['FIBER DIFFRACTION'] = 'Fiber diffraction';
        $experimental_technique['THEORETICAL MODEL, SOLUTION NMR'] = 'Theoretical model, solution NMR';
        $experimental_technique['FLUORESCENCE TRANSFER'] = 'Fluorescence transfer';
        $experimental_technique['NEUTRON DIFFRACTION'] = 'Neutron diffraction';
        $experimental_technique['SOLUTION NMR, SOLUTION SCATTERING'] = 'Solution NMR, solution scattering';
        $experimental_technique['SOLUTION SCATTERING, SOLUTION NMR'] = 'Solution scattering, solution NMR';
        $experimental_technique['SOLID-STATE NMR'] = 'Solid-state NMR';
        $experimental_technique['ELECTRON MICROSCOPY, SOLUTION NMR'] = 'Electron microscopy, solution NMR';
        $experimental_technique['X-RAY DIFFRACTION, SOLUTION SCATTERING'] = 'X-ray diffraction, solution scattering';

        $ife_to_cpv = array();

        foreach ($query_cpv->result() as $row) {
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
                $chain = end(explode('|', $ife_chain));
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

        foreach ($query->result() as $row) {
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
                // echo "{$ife_item}\n";
                $ife_chain = end(explode('|', $ife_item));
                $ife_list = explode('|', $ife_item);

                // echo "{$ife_chain}\n";

                $pdb_from_ife = $ife_list[0];
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
                // echo "\n-------";
                // echo "{$rfam_key} | {$rfam_str}\n";
                // echo "{$source_key} | {$source_str}\n";
                // echo "{$standardized_name_key}| {$standardized_name_str}\n";

                // echo " {$standardized_name_key} : {$standardized_name_str} \n";

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

            // if(substr($standardized_name_str, -1) == "+") {
            //     $new_name = substr($standardized_name_str, 0, -1);
            //     $standardized_name_str = $new_name[0];
            // }
            // if(substr($rfam_str, -1) == "+") {
            //     $new_rfam = substr($rfam_str, 0, -1);
            //     $rfam_str =  $new_rfam[0];
            // }

            // echo "{$standardized_name_str}";


            ### Standard name edit
            $table[] = array($i,
                             $link,
                             $standardized_name_str,
                             $this->get_compound_single($row->ife_id),
                             $this->get_source_organism($row->ife_id),
                             $source_str,
                             $rfam_str,
                             #  may add get_compound_list as popover
                             #  to get_compound_single field
                             #$this->get_compound_list($row->pdb_id),
                             $row->title,
                             $experimental_technique[$row->experimental_technique],
                             $row->resolution,
                             $row->release_date);
                             #$row->value;
            // $this->db->select('ppv.pdb_id')
            //                 ->select('ppv.property')
            //                 ->select('ppv.value')
            //                  ->from('pdb_property_value AS ppv')
            //                  ->where('ppv.pdb_id',$row->pdb_id);
            //         $query_property = $this->db->get();
            //         foreach ($query_property->result as $row){
            //             $property_name = $row->property;
            //             $value = $row->value;
            //         }


        }
        // pdb_property_value


        return $table;
    }

    function get_statistics($id)
    {   if(substr($id, 0, 3) === "DNA"){
            $this->db->select('pi.pdb_id')
                    ->select('ii.ife_id')
                    ->select('pi.title')
                    ->select('pi.experimental_technique')
                    ->select('pi.release_date')
                    ->select('pi.resolution')
                    ->select('ii.length')
                    ->select('ii.bp_count')
                    ->select('ot.class_order')
                    // ->select('ppv.property')
                    // ->select('ppv.value')
                    ->from('pdb_info AS pi')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_ordering_test AS ot', 'ii.ife_id = ot.ife_id')
                    // ->join('pdb_property_value AS ppv', 'pi.pdb_id = ppv.pdb_id')
                    ->where('ot.nr_class_name',$id)
                    ->order_by('ot.class_order','asc');
            $query = $this->db->get();
            $i = 0;
            $table = array();
            foreach ($query -> result() as $row) {
                $link = $this->make_dna_pdb_widget_link($row->ife_id,$i);
                // $link = '<input type='checkbox' id='$counter' data-coord= '$row->ife_id' class='jmolInline'>';
                //if ( $i==0 ) {
                    //$link = $link . ' <strong>(rep)</strong>';
                //}

                $i++;
                $table[] = array($i,
                                $row->ife_id,
                                $link,
                                $row->title,
                                // "<br><strong>Property:</strong> " . $row->property .
                                // "; <br><strong>Value:</strong> " . $row->value,
                                //$this->get_source_organism($row->ife_id),
                                //$this->get_compound_list($row->pdb_id),
                                $row->experimental_technique,
                                $row->resolution,
                                $row->length,
                                // $row->property,
                                // $row->value
                                'NAKB_NA_annotation',
                                'NAKB_protein_annotation',
                                );
                                //$row->bp_count);
            }
            $annotations = array();
            $this->db->select('pi.pdb_id')
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
                    ->from('pdb_info AS pi')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_ordering_test AS ot', 'ii.ife_id = ot.ife_id')
                    ->join('pdb_property_value AS ppv', 'pi.pdb_id = ppv.pdb_id')
                    ->where('ot.nr_class_name',$id)
                    ->order_by('ot.class_order','asc');
            $query = $this->db->get();
            $counter = 0;
            foreach ($query -> result() as $row) {
                $link = $this->make_dna_pdb_widget_link($row->ife_id,floor($counter));
                // print_r($link);
                $counter = $counter+0.5;
                // print_r($counter);
                $annotations[] = array(
                                $row->ife_id,
                                $row->property,
                                $row->value
                                );
                // print_r($annotations);
            }
            // print_r($annotations);

            $return_table = array();
            foreach ($table as $r){
                $tem_r = $r;
                foreach($annotations as $l){
                    // print_r(1111);
                    // print_r($tem_r[1]);
                    // print_r($l[0]);
                    // var_dump($tem_r[1] == $l[0]);
                    // if (compare_ife_id(strval($tem_r[1]),strval($l[0]))){
                    if ($tem_r[1] == $l[0]){

                        // print_r($tem_r[6]);
                        // print_r($l[1]);
                        // $tem_r[2] .= "<br><strong>" . ($l[1] ?: "NULL") . ": </strong>"  . ($l[2] ?: "NULL");
                        // $tem_r[] = ($l[1] ?: "NULL");
                        // $tem_r[] = ($l[2] ?: "NULL");
                        // $tem_r[2] .= "; <br><strong>Value:</strong> " . $l[2];
                        if ($tem_r[7] == $l[1]){
                            $tem_r[7] = ($l[2] ?: "");
                            // print_r($l[2]);
                        }
                        if ($tem_r[8] == $l[1]){
                            $tem_r[8] = ($l[2] ?: "");
                            // print_r($l[2]);
                        }
                    }
                }
                array_splice($tem_r, 1, 1);
                $return_table[] = $tem_r;

            }
            return $return_table;
    }else{
            $this->db->select('pi.pdb_id')
                    ->select('ii.ife_id')
                    ->select('pi.title')
                    ->select('pi.experimental_technique')
                    ->select('pi.release_date')
                    ->select('pi.resolution')
                    ->select('ii.length')
                    ->select('ii.bp_count')
                    ->select('ot.class_order')
                    ->from('pdb_info AS pi')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_ordering_test AS ot', 'ii.ife_id = ot.ife_id')
                    ->where('ot.nr_class_name',$id)
                    ->order_by('ot.class_order','asc');
            $query = $this->db->get();
            $i = 0;
            $table = array();
            foreach ($query -> result() as $row) {
                $link = $this->make_dna_pdb_widget_link($row->ife_id,$i);
                //if ( $i==0 ) {
                    //$link = $link . ' <strong>(rep)</strong>';
                //}

                $i++;
                $table[] = array($i,
                                $link,
                                $row->title,
                                //$this->get_source_organism($row->ife_id),
                                //$this->get_compound_list($row->pdb_id),
                                $row->experimental_technique,
                                $row->resolution,
                                $row->length);
                                //$row->bp_count);
            }
            return $table;
    }

    }

    /*
    //What Sri was adapting into new get_statistics
    function get_statistics_test($id)
    {
        $this->db->select('nc1.ife_id')
                 ->select('ca.pdb_id')
                 ->select('ca.assembly')
                 ->from('nr_chains AS nc1')
                 ->join('nr_classes AS nc2','nc1.nr_class_id = nc2.nr_class_id AND nc1.nr_release_id = nc2.nr_release_id')
                 ->join('chain_annotation AS ca', 'nc1.ife_id = ca.chain')
                 ->where('nc2.name', $id)
                 ->order_by('ca.pdb_id','asc')
                 ->order_by('ca.assembly','asc');

        $query = $this->db->get();

        $i = 0;
        $table = array();

        foreach ($query -> result() as $row) {
            $link = $this->make_pdb_widget_link($row->ife_id);
            $assembly = $row->assembly;
            //if ( $i==0 ) {
                //$link = $link . ' <strong>(rep)</strong>';
            //}

            $ife_components = explode("|", $row->ife_id);
            $pdb_id = $ife_components[0];


            $lsu_23S = $this->get_ribosome_chain($row->pdb_id, $assembly, "LSU_23S");
            $lsu_5S = $this->get_ribosome_chain($row->pdb_id, $assembly, "LSU_5S");
            $mrna = $this->get_ribosome_chain($row->pdb_id, $assembly, "mRNA");
            $aminoacyl_trna = $this->get_ribosome_chain($row->pdb_id, $assembly, "A_tRNA");
            $aminoacyl_trna_seq = $this->get_trna_sequence($aminoacyl_trna);
            //print $aminoacyl_trna_seq;

            $peptidyl_trna = $this->get_ribosome_chain($row->pdb_id, $assembly, "P_tRNA");
            $exit_trna = $this->get_ribosome_chain($row->pdb_id, $assembly, "E_tRNA");

            $trna_chains = array($aminoacyl_trna, $peptidyl_trna, $exit_trna);
            $trna_chains_filtered = array_filter($trna_chains);
            $trna_chains_display = join(", ", $trna_chains_filtered);

            $aminoacyl_trna_state = $this->get_trna_occupancy($aminoacyl_trna);
            $peptidyl_trna_state = $this->get_trna_occupancy($peptidyl_trna);
            $exit_trna_state = $this->get_trna_occupancy($exit_trna);

            $trna_occupancy = array($aminoacyl_trna_state, $peptidyl_trna_state, $exit_trna_state);
            $trna_occupancy_filtered = array_filter($trna_occupancy);
            $trna_occupancy_display = join(",", $trna_occupancy_filtered);

            $protein_chains = $this->get_bound_protein_chains($lsu_23S);
            $unique_protein_chains = array_unique($protein_chains);

            $protein_names = $this->get_protein_names($pdb_id, $unique_protein_chains);
            $protein_factors = array_filter($protein_names, function ($var) { return (stripos($var, 'ribosomal') === false); });
            $factors_display = $this->format_protein_factors_display($protein_factors);

            $i++;
            $table[] = array($i,
                             $link,
                             $row->pdb_id,
                             //$this->get_source_organism($row->ife_id),
                             //$this->get_compound_list($row->pdb_id),
                             //$row->resolution,
                             //$row->length);
                             //$row->bp_count);
                             $assembly,
                             $lsu_23S,
                             $lsu_5S,
                             $mrna,
                             $trna_chains_display,
                             $trna_occupancy_display,
                             $aminoacyl_trna_seq,
                             $factors_display
                             //$this->get_trna_occupancy($aminoacyl_trna),
                             //$this->get_trna_occupancy($peptidyl_trna)
                             //$this->get_trna_occupancy($exit_trna)
                            );

        }

        return $table;
    }

    function get_trna_sequence($chain)
    {

        if (!empty($chain)) {
            $chain_components = explode("|", $chain);
            $pdb = $chain_components[0];
            $chain_id = $chain_components[2];


            $anticodon_positions = array(36, 35, 34);

            $this->db->select('unit');
            $this->db->from('unit_info');
            $this->db->where('pdb_id', $pdb);
            $this->db->where('chain', $chain_id);
            $this->db->where_in('chain_index', $anticodon_positions);
            $this->db->_protect_identifiers = FALSE;
            $order = sprintf('FIELD(chain_index, %s)', implode(', ', $anticodon_positions));
            $this->db->order_by($order);
            $this->db->_protect_identifiers = TRUE;

            $query = $this->db->get();

            $trna_sequence = array();
            foreach ($query->result() as $row) {
                array_push($trna_sequence, $row->unit);
            }

            $trna_sequence = implode("", $trna_sequence);

            return $trna_sequence;
        }
    }

    function format_protein_factors_display($protein_factors)
    {
        if (!empty($protein_factors)) {
            return join(", ", $protein_factors);
        } else {
            return " ";
        }

    }

    function get_protein_names($pdb_id, $protein_chains)
    {
        $this->db->select('compound')
                 ->from('chain_info')
                 ->where('pdb_id', $pdb_id)
                 ->where_in('chain_name', $protein_chains);

        $query = $this->db->get();

        $protein_names = array();
        foreach ($query->result() as $row) {
            array_push($protein_names, $row->compound);
        }

        return $protein_names;
    }

    function get_bound_protein_chains($chain_id)
    {
        $search_keyword = $chain_id . "|";

        $this->db->select('UPD.unit_id_2 AS unit_id_2')
                 ->from('unit_pairs_distances AS UPD')
                 ->join('unit_info AS UI', 'UPD.unit_id_2 = UI.unit_id', 'inner')
                 ->like('UPD.unit_id_1', $search_keyword, 'after')
                 ->where('UI.unit_type_id', 'aa')
                 ->where('UPD.distance <=', 8.5);

        $query = $this->db->get();

        $chain_list = array();
        foreach ($query->result() as $row) {
            $chain_component = explode("|", $row->unit_id_2);
            array_push($chain_list, $chain_component[2]);
        }

        return $chain_list;
    }
    */

    function get_heatmap_data_revised_original($id)
    {
        $this->db->select('NO1.ife_id AS ife1')
                 ->select('NO1.class_order AS ife1_index')
                 ->select('NO2.ife_id AS ife2')
                 ->select('NO2.class_order AS ife2_index')
                 ->select('CCS.discrepancy')
                 ->from('nr_ordering_test AS NO1')
                 ->join('nr_ordering_test AS NO2', 'NO1.nr_class_name = NO2.nr_class_name', 'inner')
                 ->join('ife_chains AS IC1', 'NO1.ife_id = IC1.ife_id AND IC1.index = 0', 'inner')
                 ->join('ife_chains AS IC2', 'NO2.ife_id = IC2.ife_id AND IC2.index = 0', 'inner')
                 ->join('chain_chain_similarity AS CCS', 'IC1.chain_id = CCS.chain_id_1 AND IC2.chain_id = CCS.chain_id_2', 'left outer')
                 ->where('NO1.nr_class_name', $id);

        $query = $this->db->get();

        $heatmap_data = json_encode($query->result());

        return $heatmap_data;

    }

    function get_heatmap_data_revised($id)
    {
        // Retrieve IFE names and order index for this equivalence class
        $this->db->select('NO1.ife_id AS ife1')
                 ->select('NO1.class_order AS ife1_index')
                 ->from('nr_ordering_test AS NO1')
                 ->where('NO1.nr_class_name', $id);

        $query = $this->db->get();

        // Assemble IFE names into an array
        $ife_list = array();
        $index_list = array();
        foreach ($query->result() as $row) {
            array_push($ife_list,$row->ife1);
            array_push($index_list,$row->ife1_index);
        }

        // Load discrepancies from large classes from flat file, small from database
        if (count($ife_list) > 300) {

            // store all million or more discrepancies in an associative array
            $discrepancy_array = array();
            $file_lines = file('/var/www/html/discrepancy/IFEdiscrepancy.txt');
            foreach ($file_lines as $line) {
                $line = str_replace("\n","",$line);
                $resultArray = explode("\t", $line);
                if (in_array($resultArray[0],$ife_list)) {
                    $discrepancy_array[$resultArray[0]." ".$resultArray[1]] = $resultArray[2];
//                    $discrepancy_array[$resultArray[1]." ".$resultArray[0]] = $resultArray[2];
                }
            }

            $file_lines = array();

            // build one line of $result for each pair of discrepancies
            $result = array();

            for ($i = 0; $i < count($ife_list); $i++) {
                for ($j = 0; $j < count($ife_list); $j++) {
                    $ife1 = $ife_list[$i];
                    $ife2 = $ife_list[$j];
                    $newrow["ife1"] = $ife1;
                    $newrow["ife2"] = $ife2;
                    $newrow["ife1_index"] = $index_list[$i];
                    $newrow["ife2_index"] = $index_list[$j];
                    $key  = $ife1." ".$ife2;
                    $key2 = $ife2." ".$ife1;
                    if (array_key_exists($key, $discrepancy_array)) {
                        $newrow["discrepancy"] = $discrepancy_array[$key];
                    } elseif (array_key_exists($key2, $discrepancy_array)) {
                        $newrow["discrepancy"] = $discrepancy_array[$key2];
                    } else {
                        $newrow["discrepancy"] = null;
                    }
                    array_push($result,$newrow);
                }
            }

            $heatmap_data = json_encode($result);

        } else {
            $this->db->select('NO1.ife_id AS ife1')
                     ->select('NO1.class_order AS ife1_index')
                     ->select('NO2.ife_id AS ife2')
                     ->select('NO2.class_order AS ife2_index')
                     ->select('CCS.discrepancy')
                     ->from('nr_ordering_test AS NO1')
                     ->join('nr_ordering_test AS NO2', 'NO1.nr_class_name = NO2.nr_class_name', 'inner')
                     ->join('ife_chains AS IC1', 'NO1.ife_id = IC1.ife_id AND IC1.index = 0', 'inner')
                     ->join('ife_chains AS IC2', 'NO2.ife_id = IC2.ife_id AND IC2.index = 0', 'inner')
                     ->join('chain_chain_similarity AS CCS', 'IC1.chain_id = CCS.chain_id_1 AND IC2.chain_id = CCS.chain_id_2', 'left outer')
                     ->where('NO1.nr_class_name', $id);

            $query = $this->db->get();

            $heatmap_data = json_encode($query->result());

        }
        $eff_data = json_decode($heatmap_data,true);

        $eff_ifes = array();
        $eff_discrepancy = array();
        $eff_discrepancy_one_row = array();
        foreach ($eff_data as $row) {

            foreach ($row as $key => $value) {
                if ($key == 'ife1'){
                    if (!in_array($value, $eff_ifes)){
                        $eff_ifes[] = $value;
                    }
                }
                if ($key == 'discrepancy'){

                    $eff_discrepancy_one_row[] = (number_format($value,4) >0? number_format($value,4) : NULL );

                }
            }

        }
        $eff_data_cleaned = array();

        $eff_data_cleaned[] = '#heatmap';

        $two_d_array = array();
        $size = count($eff_ifes);
        // echo $size;
        for ($i = 0; $i < count($eff_discrepancy_one_row); $i += $size) {

            $two_d_array[] = array_slice($eff_discrepancy_one_row, $i, $size);
        }

        for ($i = 0; $i < $size; $i++) {
            $two_d_array[$i][$i] = 0;
        }

        $eff_data_cleaned[] = $two_d_array;



        $eff_data_cleaned[] = $eff_ifes;


        return json_encode($eff_data_cleaned);

    }


    function get_heatmap_data($id)
    {
        $this->db->select('NR.nr_release_id')
                 ->from('nr_classes AS NC')
                 ->join('nr_releases AS NR', 'NC.nr_release_id = NR.nr_release_id')
                 ->where('NC.name', $id)
                 ->order_by('NR.index', 'DESC')
                 ->limit(1);
        $result = $this->db->get()->result_array();

        $release_id = $result[0]['nr_release_id'];

        $this->db->select('NC1.ife_id AS ife1')
                 ->select('NO1.index AS ife1_index')
                 ->select('NC2.ife_id AS ife2')
                 ->select('NO2.index AS ife2_index')
                 ->select('CSS.discrepancy')
                 ->from('nr_classes AS NCL')
                 ->join('nr_class_rank as NC1', 'NC1.nr_class_name = NCL.name', 'inner')
                 ->join('nr_ordering as NO1', 'NO1.nr_class_id = NC1.nr_class_id', 'inner')
                 ->join('nr_class_rank as NC2', 'NC2.nr_class_name = NCL.name', 'inner')
                 ->join('nr_ordering as NO2', 'NO2.nr_class_id = NC2.nr_class_id', 'inner')
                 ->join('ife_chains as IC1', 'IC1.ife_id = NC1.ife_id and IC1.index = 0', 'inner')
                 ->join('ife_chains as IC2', 'IC2.ife_id = NC2.ife_id and IC2.index = 0', 'inner')
                 ->join('chain_chain_similarity as CSS', 'CSS.chain_id_1 = IC1.chain_id and CSS.chain_id_2 = IC2.chain_id', 'left outer')
                 ->where('NC1.nr_class_rank_id !=', 'NC2.nr_class_rank_id')
                 ->where('NCL.name', $id)
                 ->where('NCL.nr_release_id', $release_id);

        $query = $this->db->get();

        foreach($query->result() as $row) {
            $ife1[] = $row->ife1;
            $ife1_index[] = $row->ife1_index;
            $ife2[] = $row->ife2;
            $ife2_index[] = $row->ife2_index;
            $discrepancy[] = $row->discrepancy;
        }

        $heatmap_data = json_encode($query->result());

        return $heatmap_data;
    }

    function get_ribosome_annotation_new($id)
    {
        $this->db->select('ch.ife_id')
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
                    ->from('pdb_info AS pi')
                    ->join('ife_info AS ii','pi.pdb_id = ii.pdb_id')
                    ->join('nr_class_rank AS ch', 'ii.ife_id = ch.ife_id')
                    ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
                    ->join('ribosome_chain_annotation AS ca', 'ch.ife_id = ca.ssu_chain')
                    ->where('cl.name',$id)
                    ->where('cl.nr_release_id', $this->last_seen_in) # copy from the comment function above
                    ->group_by('pi.pdb_id')
                    ->group_by('ii.ife_id')
                    ->order_by('ch.rank','asc');

        $query = $this->db->get();

        $i = 0;
        $table = array();

        foreach ($query -> result() as $row) {
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
        $this->db->select('chain')
                 ->from('chain_annotation')
                 ->where('pdb_id', $pdb)
                 ->where('assembly', $assembly)
                 ->like('value', $value);

        $query = $this->db->get();

        $result = "";
        foreach ($query->result() as $row) {
            $result = $row->chain;
        }

        return $result;
    }

    function get_trna_occupancy($chain)
    {
        $this->db->select('value')
                 ->from('chain_annotation')
                 ->where('chain', $chain)
                 ->where('feature', 'tRNA_occupancy');
                 //->like('value', $value);

        $query = $this->db->get();

        $result = "";
        foreach ($query->result() as $row) {
            $result = $row->value;
        }

        return $result;
    }

    function get_compound_single($ife)
    {
        $this->db->select('group_concat(DISTINCT ci.compound separator ", ") as compound', FALSE)
                 ->from('ife_info AS ii')
                 ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id AND ii.model = ic.model')
                 ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id AND ci.pdb_id = ii.pdb_id')
                 ->where('ii.ife_id', $ife)
                 ->order_by('ci.chain_name');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $result = $row->compound;
        }

        return $result;
    }

    function get_compound_list($id)
    {
        $this->db->select('group_concat(compound separator ", ") as compounds', FALSE)
                 ->from('chain_info')
                 ->where('pdb_id', $id)
                 ->group_by('pdb_id');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
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
            $sql = "CALL nr_set_diffs_parents(?,?)";
            $par = array($id,$this->first_seen_in);
        } elseif ($mode == 'children') {
            $sql = "CALL nr_set_diffs_children(?,?)";
            $par = array($id,$this->last_seen_in);
        }

        $query = $this->db->query($sql, $par);

        $table = array();

        foreach ($query->result() as $row) {
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

        $query->next_result(); ### clears the extra empty MySQL result set

        return $table;
    }

    function beautify_description_date($s)
    {
        return substr($s,0,4) .'-'. substr($s,4,2) .'-'. substr($s,6,2);
    }

    function get_change_counts_by_release()
    {
        $this->db->select('nr_release_id')
                 ->select('resolution')
                 ->select('new_class_count AS nag')
                 ->select('removed_class_count AS nrg')
                 ->select('updated_class_count AS nug')
                 ->from('nr_parent_counts');
        $query = $this->db->get();

        $changes = array();
        foreach ($query->result() as $row) {
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

    function get_pdb_files_counts($type)
    {
        // This seems to count ife ids in each release ... is that right?
        $this->db->select('ncl.nr_release_id, count(ii.pdb_id) as num')
                 ->from('ife_info AS ii')
                 ->join('nr_class_rank AS nch', 'ii.ife_id = nch.ife_id')
                 ->join('nr_classes AS ncl', 'nch.nr_class_name = ncl.name')
                 ->where('ncl.resolution', 'all')
                 ->where('ncl.name LIKE', $type.'%')
                 ->group_by('ncl.nr_release_id');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $counts[$row->nr_release_id] = $row->num;
        }

        return $counts;
    }

    function get_newest_pdb_images()
    {
        $sql = "SELECT DISTINCT(`pdb_id`) FROM `pdb_info`" .
               "WHERE `release_date` >= DATE_ADD('" .
               date("Y-m-d H:i:s") . "', INTERVAL -1 WEEK);";
        $query = $this->db->query($sql);

        $new_files = array();
        foreach ($query->result() as $row) {
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
        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index', 'desc')
                 ->limit(2);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $releases[] = $row->nr_release_id;
        }

        // get their release difference
        $this->db->select('added_pdbs')
                 ->from('__trash_nr_release_diff')
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
        $this->db->select('pdb_id')
                 ->from('pdb_info')
                 ->distinct();
        $query = $this->db->get();

        return $query->num_rows();
    }

    function get_all_releases($type)
    {
        $changes   = $this->get_change_counts_by_release();
        $pdb_count = $this->get_pdb_files_counts($type);
        $releases  = $this->get_release_precedence();

        $this->db->select('DISTINCT(nr_release_id)')
        ->from('nr_classes')
        ->where('name LIKE', $type.'%');
        $query = $this->db->get();
        $released = array();
        foreach ($query->result() as $row){
            $released[] = $row->nr_release_id;
        }
        $this->db->select('nr_release_id')
        ->select('date')
        ->select('description')
        ->from('nr_releases')
        ->order_by('index','desc')
        ->where_in('nr_release_id', $released);
        $query = $this->db->get();

        if ($type == 'DNA'){
            $url_type = 'dna';
        } else{
            $url_type = 'rna';
        }
        $i = 0;
        foreach ($query->result() as $row) {
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
            $table[] = array($id, $status, $description, $pdb_count[$row->nr_release_id] );
        }

        return $table;
    }

    function get_latest_release()
    {
        $this->db->select('nr_release_id')
                 ->select('date')
                 ->select('description')
                 ->from('nr_releases')
                 ->order_by('index','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();

        return $result[0]['nr_release_id'];
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
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->order_by('index','desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
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

        $this->db->select('nr_release_id')
                 ->select('description')
                 ->select('parent_nr_release_id')
                 ->select('resolution')
                 ->select('new_class_count')
                 ->select('removed_class_count')
                 ->select('updated_class_count')
                 ->select('pdb_added_count')
                 ->select('pdb_removed_count')
                 ->from('nr_release_compare_counts')
                 ->order_by('index', 'desc');
        $query = $this->db->get();

        foreach ($query->result() as $row) {
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
        $this->db->select('description')
                 ->from('nr_releases')
                 ->where('nr_release_id',$id);
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

/*
    ### DEFUNCT FUNCTION?
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
*/

    function read_chain_property_value_table()
    {
        # Query the entire cpv table. Load all data approach.
        # cpv = chain_property_value
        $this->db->select('cpv.pdb_id')
            ->select('cpv.chain')
            ->select('cpv.property')
            ->select('cpv.value')
            ->from('chain_property_value AS cpv');
        $query_cpv = $this->db->get();

        # Create dictionaries to store cpv data
        $chain_to_standardized_name = array();
        $chain_to_source = array();
        $chain_to_rfam = array();

        # populate dictionaries with pdb_chain keys
        foreach ($query_cpv->result() as $row) {
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
        $this->db->select('ci.pdb_id')
            ->select('ci.chain_name')
            ->select('ci.compound')
            ->select('ci.entity_macromolecule_type')
            ->select('ci.source')
            ->select('ci.taxonomy_id')
            ->from('chain_info AS ci')
            ->where('ci.entity_macromolecule_type !=', 'Polypeptide(L)');
        $query_cpv = $this->db->get();

        # Create dictionaries to store data
        $chain_to_taxid = array();
        $chain_to_species = array();
        $chain_to_compound = array();
        $chain_to_type = array();

        # populate dictionaries with pdb_chain keys
        foreach ($query_cpv->result() as $row) {
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
        $this->db->select('tsd.taxonomy_id')
            ->select('tsd.species_taxid')
            ->from('taxid_species_domain AS tsd');
        $query_cpv = $this->db->get();

        # Create dictionaries to store data
        $taxid_to_species_taxid = array();
        foreach ($query_cpv->result() as $row) {
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
            show_404();
        }
        $this->db->select('ii.ife_id, ii.pdb_id, nl.name, nc.rank')
            ->from('ife_info AS ii')
            ->join('nr_class_rank AS nc', 'ii.ife_id = nc.ife_id')
            ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
            ->where('nl.nr_release_id', $id)
            ->where("nl.name LIKE '$group_id%'")
            ->order_by('nc.rank','asc');
            $query = $this->db->get();

        // reorganize by class and rep and pdb
        $class = array();
        foreach ($query->result() as $row) {
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
        $this->db->select('pdb_id, title, resolution, experimental_technique, release_date')
                 ->from('pdb_info')
                 ->where_in('pdb_id', $pdbs)
                 ->group_by('pdb_id');
        $query = $this->db->get();
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


        foreach($query->result() as $row) {
            $pdb[$row->pdb_id]['title']      = $row->title;
            $pdb[$row->pdb_id]['resolution'] = (is_null($row->resolution)) ? '' : number_format($row->resolution, 1) . ' &Aring';
            $pdb[$row->pdb_id]['experimental_technique'] = $experimental_technique[$row->experimental_technique];
            $pdb[$row->pdb_id]['release_date'] = $row->release_date;

        }

        // check if any of the files became obsolete ... not sure this still works
        $this->db->select('pdb_obsolete_id, replaced_by')
                 ->from('pdb_obsolete')
                 ->where_in('pdb_obsolete_id', $pdbs);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $pdb[$row->pdb_obsolete_id]['title'] = "OBSOLETE: replaced by <a class='pdb'>{$row->replaced_by}</a>";
            $pdb[$row->pdb_obsolete_id]['resolution'] = '';
        }

        // get annotations: "updated/>2 parents" etc.
        $this->db->select('nr_class_id, comment')
                 ->from('nr_classes')
                 ->where('nr_release_id',$id)
                 ->where('resolution', $resolution);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
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
        $this->db->select('nl.name')
                 ->select('ii.ife_id')
                 ->select('ii.pdb_id')
                 ->select('ii.length AS analyzed_length')
                 ->select('group_concat(DISTINCT ci.compound separator ", ") as compound', FALSE)
                 ->select('ci.source as species_name')     # changed 2022-06-29
                 ->select('ci.taxonomy_id AS species_id')  # changed 2022-06-29
                 ->select('nl.nr_class_id')
                 #->select('COUNT(DISTINCT ii.ife_id) AS num')
                 ->from('nr_class_rank AS nc')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
                 ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id')
                 ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id')
                 #->join('species_mapping AS sm', 'ci.taxonomy_id = sm.species_mapping_id', 'left')
                 ->where('nl.nr_release_id', $id)
                 ->where('nl.resolution', $resolution)
                 ->where("nl.name LIKE '$group_id%'")
                 ->group_by('nl.name')
                 ->group_by('nl.nr_release_id')
                 ->group_by('nl.resolution')
                 ->order_by('ii.length','desc');
        $query = $this->db->get();

        list($chain_to_standardized_name, $chain_to_source, $chain_to_rfam) = $this->read_chain_property_value_table();

        # fill in data for the release
        foreach ($query->result() as $row) {
            $class_id = $row->name;
            #$nums     = $row->num;
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
                $chain = end(explode('|', $ife_chain));
                $ife_split = explode('|', $ife_chain);
                $ife_pdb_id = $ife_split[0];
                if (array_key_exists("{$ife_pdb_id}_{$chain}", $chain_to_rfam)){
                    $rfam_representative .= $chain_to_rfam["{$ife_pdb_id}_{$chain}"] . " + ";
                }
                if (array_key_exists("{$ife_pdb_id}_{$chain}", $chain_to_source)){
                    $source_representative = $chain_to_source["{$ife_pdb_id}_{$chain}"];
                }
                if (array_key_exists("{$ife_pdb_id}_{$chain}", $chain_to_standardized_name)){
                    $stdname = $chain_to_standardized_name["{$ife_pdb_id}_{$chain}"];
                    $short_name = end(explode(';', $stdname));
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
            $this->db->select('ppv.pdb_id')
                    ->select('ppv.property')
                    ->select('ppv.value')
                    ->from('pdb_property_value AS ppv')
                    ->where('ppv.pdb_id',$row->pdb_id);
            $query_property = $this->db->get();
            // $property_value = array();
            // foreach ($query_property->result as $row){
            //     $property_value[$row->property] = ($row->value ?: "NULL");
            // }
            if ($query_property->num_rows() > 0) {
                $property_value = array();
                foreach ($query_property->result() as $row1) {
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
        $this->db->select('count(unit_id) as length')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb_id)
                 ->where_in('unit', array('A','C','G','U'))
                 ->order_by('count(*)', 'DESC')
                 ->limit(1);
        $query = $this->db->get();

        $result = $query->result();
        return $result[0]->length;
    }

    function get_csv($release, $resolution, $type)
    // Retrieve information on one representative set release
    {
        $resolution = str_replace('A', '', $resolution);
        $this->db->select('ii.ife_id as id, nl.name as class_id, nc.rank')
                 ->from('nr_class_rank AS nc')
                 ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->where('nl.nr_release_id', $release)
                 ->where('resolution', $resolution)
                 ->where('nl.name LIKE', $type."%")
                 ->order_by('nc.rank','asc');
        $query = $this->db->get();

        foreach($query->result() as $row) {
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
        $this->db->select('ncr.ife_id as id')
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
                ->from('nr_class_rank AS ncr')
                ->join('nr_classes AS nl', 'ncr.nr_class_name = nl.name')
                ->join('nr_cqs AS cqs', 'ncr.ife_id = cqs.ife_id','inner')
                ->join('ife_cqs as ic','ic.ife_id = ncr.ife_id')
                ->join('ife_info as ii','ii.ife_id = ncr.ife_id')
                ->join('pdb_info as pi','pi.pdb_id = ii.pdb_id')
                ->where('nl.nr_release_id', $release)
                ->where('nl.resolution', $resolution)
                ->where('nl.name LIKE', $type."%")
                ->where('REPLACE(cqs.nr_name, "all", "'.$resolution.'") = nl.name')
                ->order_by('nl.name','asc')
                ->order_by('ncr.rank','asc');
        $query = $this->db->get();

        list($chain_to_standardized_name, $chain_to_source, $chain_to_rfam) = $this->read_chain_property_value_table();

        list($chain_to_taxid, $chain_to_species, $chain_to_compound, $chain_to_type) = $this->read_chain_info_table();

        $taxid_to_species_taxid = $this->read_taxid_species_domain_table();

        # get maximum observed length for each Rfam family
        $rfam_to_max_observed = array();
        foreach($query->result() as $row) {
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
        foreach($query->result() as $row) {
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
            $species = '';
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

        $this->db->select('nr_release_id AS id, description')
                 ->from('nr_releases')
                 ->order_by('index','desc');
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
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->where('nr_release_id', $id)
                 ->limit(1);

        if ( $this->db->get()->num_rows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

    function is_valid_class($id)
    {
        $this->db->select('name')
                 ->from('nr_classes')
                 ->where('name', $id)
                 ->limit(1);

        if ( $this->db->get()->num_rows() == 0 ) {
            return False;
        } else {
            return True;
        }
    }

    function get_two_newest_releases()
    {
        $this->db->select('nr_release_id')
                 ->select('parent_nr_release_id')
                 ->from('nr_releases')
                 ->order_by('index', 'desc')
                 ->limit(1);
        $query = $this->db->get();

        foreach ($query->result() as $row){
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
