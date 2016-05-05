<?php
class Pdb_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');

        // Call the Model constructor
        parent::__construct();
    }

    function get_all_pdbs()
    {
        $this->db->select('distinct(pdb_id)')
                 ->from('pdb_info');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $pdbs[] = $row->pdb_id; //anchor(base_url(array('pdb',$row->structureId)), $row->structureId );
        }
        return $pdbs;
    }

    function get_recent_rna_containing_structures($num)
    {
        $this->db->select('distinct(pdb_id)')
                 ->from('pdb_info')
                 ->order_by('release_date', 'desc')
                 ->limit($num);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdbs[] = $row->pdb_id;
        }
        return $pdbs;
    }

    function get_latest_motif_assignments($pdb_id, $loop_type)
    {
        // $loop_type = IL or HL
        $latest_release = $this->get_latest_motif_release($loop_type);

        $this->db->select()
                 ->from('ml_loops')
                 ->where('release_id', $latest_release)
                 ->like('ml_loops_id', strtoupper($loop_type) . '_' . $pdb_id, 'right');
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data[$row->ml_loops_id] = $row->motif_id;
        }
        return $data;
    }

    function get_loops($pdb_id)
    {
        $release_id = $this->get_latest_loop_release();
        $this->db->select('lq.loop_qa_id')
                 ->select('lq.status')
                 ->select('lq.modifications')
                 ->select('lq.nt_signature')
                 ->select('lq.complementary')
                 ->select('li.unit_ids')
                 ->select('li.loop_name')
                 ->from('loop_qa AS lq')
                 ->join('loop_info AS li', 'li.loop_id = lq.loop_qa_id')
                 ->where('pdb_id', $pdb_id)
                 ->where('release_id', $release_id);
        $query = $this->db->get();

        $loop_types = array('IL','HL','J3');
        foreach ($loop_types as $loop_type) {
            $valid_tables[$loop_type] = array();
            $invalid_tables[$loop_type] = array();
        }

        $motifs = $this->get_latest_motif_assignments($pdb_id, 'IL');
        $motifs = array_merge($motifs, $this->get_latest_motif_assignments($pdb_id, 'HL'));

        foreach ($query->result() as $row) {
            $loop_type = substr($row->loop_qa_id, 0, 2);
            if ($row->status == 1) {
                if ( array_key_exists($row->loop_qa_id, $motifs) ) {
                    $motif_id = anchor_popup("motif/view/{$motifs[$row->loop_qa_id]}", $motifs[$row->loop_qa_id]);
                } else {
                    $motif_id = 'NA';
                }

                $valid_tables[$loop_type][] = array(count($valid_tables[$loop_type]) + 1,
                                                    array( 'class' => 'loop',
                                                           'data'  => $this->get_checkbox($row->loop_qa_id, $row->unit_ids)
                                                          ),
                                                    $row->loop_name,
                                                    $motif_id
                                                    );
            } else {
                if (!is_null($row->complementary)) {
                    $annotation = $row->complementary;
                } elseif (!is_null($row->modifications)) {
                    $annotation = $row->modifications;
                } else {
                    $annotation = $row->nt_signature;
                }

                $invalid_tables[$loop_type][] = array(count($invalid_tables[$loop_type])+1,
                                                      array( 'class' => 'loop',
                                                             'data'  => $this->get_checkbox($row->loop_qa_id, $row->unit_ids)
                                                            ),
                                                      $this->make_reason_label($row->status),
                                                      $annotation);
            }
        }

        return array('valid' => $valid_tables, 'invalid' => $invalid_tables);
    }

    function make_reason_label($status)
    {
        return '<label class="label important">' . $this->qa_status[$status] . '</label>';
    }

    function get_checkbox($id, $nt_ids)
    {
        return "<label><input type='radio' id='{$id}' class='jmolInline' data-coord='{$id}'>{$id}</label>" .
        "<span class='loop_link'>" . anchor_popup("loops/view/{$id}", '&#10140;') . "</span>" ;
    }

    function get_latest_loop_release()
    {
        $this->db->select('loop_releases_id')
                 ->from('loop_releases')
                 ->order_by('date','desc')
                 ->limit(1);

        $result = $this->db->get()->result_array();
        return $result[0]['loop_releases_id'];
    }

    function pdb_exists($pdb_id)
    {
        // does RNA 3D Hub know about this structure?
        $this->db->select('pdb_id')
                 ->from('pdb_info')
                 ->where('pdb_id', $pdb_id)
                 ->limit(1);

        if ( $this->db->get()->num_rows() > 0 ) {
            return true;
        }

        // if not, is it in PDB itself?
        $pdb_rest_url = 'http://www.pdb.org/pdb/rest/describePDB?structureId=';
        $pdb_description = file_get_contents($pdb_rest_url . $pdb_id);

        // when a pdb doesn't exist, $pdb_description == '</PDBdescription>'
        if ( strpos($pdb_description, '<PDBdescription>') === false ) {
            return false;
        } else {
            return true;
        }
    }

    function pdb_is_annotated($pdb_id, $interaction_type)
    {
        //$this->db->select('pdb_analysis_status_id')
        //         ->from('pdb_analysis_status')
        //         ->where('pdb_id', $pdb_id)
        //         ->where("$interaction_type IS NOT NULL");

        //$query = "COUNT(IF(`li`.`type` = `$interaction_type`, 1, NULL)) AS numloops";

        $this->db->select('pas.pdb_analysis_status_id, li.type')
                 //->select($query, FALSE)
                 ->from('pdb_analysis_status AS pas')
                 ->join('loop_info AS li', 'pas.pdb_id = li.pdb_id')
                 ->where('pas.pdb_id', $pdb_id);

        if ( $this->db->get()->num_rows() > 0 ) {
            return True;
        } else {
            return False;
        }
    }

    function _get_unit_ids($pdb_id)
    {
    #    // get correspondences between old and new ids
    #    $this->db->select('old_id, unit_id')
    #             ->from('__pdb_unit_id_correspondence')
    #             ->where('pdb_id', $pdb_id);
    #    $query = $this->db->get();
    #    $unit_ids = array();
    #    foreach ( $query->result() as $row ) {
    #        $unit_ids[$row->old_id] = $row->unit_id;
    #    }
    #
    #    // if no new unit ids are found, fall back on old ids
    #    if ( count($unit_ids) == 0 ) {
    #        $this->db->select('pdb_coordinates_id')
    #                 ->from('__pdb_coordinates')
    #                 ->where('pdb_id', $pdb_id);
    #        $query = $this->db->get();
    #        foreach ( $query->result() as $row ) {
    #            $unit_ids[$row->pdb_coordinates_id] = $row->pdb_coordinates_id;
    #        }
    #    }

        // retrieve new-style IDs from unit_info
        $this->db->select('unit_id')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb_id);
        $query = $this->db->get();
        $unit_ids = array();
        foreach ( $query->result() as $row ) {
            $unit_ids[$row->unit_id] = $row->unit_id;
        }

        return $unit_ids;
    }

    function get_interactions($pdb_id, $interaction_type)
    {
        $url_parameters = array('basepairs', 'stacking', 'basephosphate', 'baseribose');
        $db_fields      = array('f_lwbp', 'f_stacks', 'f_bphs', 'f_brbs');
        $header_values  = array('Base-pair', 'Base-stacking', 'Base-phosphate', 'Base-ribose');
        $header = array('#', 'Nucleotide id 1', 'Nucleotide id 2');

        if ( in_array($interaction_type, $url_parameters) ) {
            $targets = array_keys($url_parameters, $interaction_type);
            $db_field = $db_fields[$targets[0]];
            $interaction_description = $header_values[$targets[0]];
            $where = "$db_field IS NOT NULL";
        } elseif ( $interaction_type == 'all' ) {
            $targets = array_keys($url_parameters);
            $db_field = implode(',', $db_fields);
            $interaction_description = implode(',', $header_values);
            $where = '(' . implode(' IS NOT NULL OR ', $db_fields) . ')';
        } else {
            return array( 'data'   => array(),
                          'header' => array(),
                          'csv'    => ''
                         );
        }

        $unit_ids = $this->_get_unit_ids($pdb_id);

        $this->db->select('upi.unit_id_1, upi.unit_id_2,' . $db_field)
                 ->from('unit_pairs_interactions AS upi')
                 ->join('unit_info AS ui', 'upi.unit_id_1 = ui.unit_id')
                 ->where('upi.pdb_id', $pdb_id)
                 ->where($where)
                 ->order_by('number');
        $query = $this->db->get();

        $i = 1;
        $html = '';
        $csv  = '';
        foreach ( $query->result() as $row ) {
            $output_fields = array();
            $csv_fields    = array();

            $csv_fields[0] = $unit_ids[$row->unit_id_1];
            foreach ($targets as $target) {
                if ( isset($row->{$db_fields[$target]}) and ($row->{$db_fields[$target]} != '') ) {
                    $output_fields[] = $row->{$db_fields[$target]};
                    $csv_fields[]    = $row->{$db_fields[$target]};
                } else {
                    $csv_fields[] = '';
                }
            }
            $csv_fields[] = $unit_ids[$row->unit_id_2];

            $html .= str_pad('<span>' . $unit_ids[$row->unit_id_1] . '</span>', 38, ' ') .
                    "<a class='jmolInline' id='s{$i}'>" .
                      str_pad(implode(', ', $output_fields), 10, ' ', STR_PAD_BOTH) .
                    "</a>" .
                    str_pad('<span>' . $unit_ids[$row->unit_id_2] . '</span>', 38, ' ', STR_PAD_LEFT) .
                    "\n";
            $csv .= '"' . implode('","', $csv_fields) . '"' . "\n";
            $i++;
        }

        return array( 'data'   => $html,
                      'header' => array_merge( $header, explode(',', $interaction_description) ),
                      'csv'    => $csv
                     );
    }

    function get_general_info($pdb_id)
    {
        //  QUERY NEEDS TO BE REWRITTEN
        //  NEEDS DATA FROM BOTH pdb_info and chain_info
        $this->db->select()
                 ->from('pdb_info AS pi')
                 ->join('chain_info AS ci', 'pi.pdb_id = ci.pdb_id')
                 ->where('pi.pdb_id', $pdb_id);
        $query = $this->db->get();

        $data['rna_chains'] = 0;
        foreach ($query->result() as $row) {
            // get this info only once because it applies to all chains
            if ( $data['rna_chains'] == 0 ) {
                $data['title'] = $row->title;
                $data['experimental_technique'] = $row->experimental_technique;
                $data['resolution'] = $row->resolution;
                $data['release_date'] = $row->release_date;
                $data['authors'] = $row->authors;
                $data['pdb_url'] = "http://www.pdb.org/pdb/explore/explore.do?structureId={$pdb_id}";
                $data['ndb_url'] = "http://ndbserver.rutgers.edu/service/ndb/atlas/summary?searchTarget={$row->ndb_id}";
            }
            // only for RNA chains
            if ( $row->entity_macromolecule_type == 'Polyribonucleotide (RNA)' or
                 $row->entity_macromolecule_type == 'DNA/RNA Hybrid' ) {
                $data['rna_chains']++;
                $organisms[] = $row->source;
                $data['rna_compounds'][] = array("chain"    => $row->chain_id,
                                                 "compound" => $row->compound,
                                                 "length"   => $row->chain_length,
                                                 "organism" => $row->source);
            }
            // for all chains
            $compounds[] = $row->compound;
        }
        $data['non_rna_chains'] = $query->num_rows() - $data['rna_chains'];
        $data['organisms'] = implode(', ', array_unique($organisms));
        $data['compounds'] = implode(', ', $compounds);

        return $data;
    }

    function get_latest_nr_release($pdb_id)
    {
        $this->db->select('nr_release_id')
                 ->from('nr_releases')
                 ->order_by('date', 'desc')
                 ->limit(1);
        $result = $this->db->get()->row();
        return $result->nr_release_id;
    }

    function get_nrlist_info($pdb_id)
    {
        // get the latest nr release
        $data['latest_nr_release'] = $this->get_latest_nr_release($pdb_id);

        // get nr equivalence classes
        $this->db->select('nc.nr_class_id')
                 ->select('nc.rep')
                 ->from('nr_chains AS nc')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->where('ii.pdb_id', $pdb_id)
                 ->where('nr_release_id', $data['latest_nr_release']);
        $query = $this->db->get();

        $data = array();
        foreach ($query->result() as $row) {
            $data['nr_classes'][] = $row->nr_class_id;
            $data['nr_urls'][$row->nr_class_id] = anchor('nrlist/view/' . $row->nr_class_id, $row->nr_class_id);
            $data['representatives'][$row->nr_class_id] = $row->rep;
        }

        return $data;
    }

    function get_loops_info($pdb_id)
    {
        $this->db->select('count(loop_id) as counts, type')
                 ->from('loop_info')
                 ->where('pdb_id', $pdb_id)
                 ->group_by('type');
        $query = $this->db->get();

        $data['loops'] = array();
        foreach ($query->result() as $row) {
            $data['loops'][$row->type] = $row->counts;
        }
        
        // add zeros if some loop types are not present
        foreach ( array('IL', 'HL', 'J3') as $loop_type ) {
            if ( !array_key_exists($loop_type, $data['loops']) ) {
                $data['loops'][$loop_type] = 0;
            }
        }
        
        $data['loops']['url'] = anchor('pdb/' . $pdb_id . '/motifs', 'More');
        
        return $data;
    }

    function get_latest_motif_release($motif_type)
    {
        $this->db->select('ml_releases_id')
                 ->from('ml_releases')
                 ->order_by('date', 'desc')
                 ->where('type', $motif_type)
                 ->limit(1);
        
        $result = $this->db->get()->row();
        
        return $result->ml_releases_id;
    }

    function get_motifs_info($pdb_id, $motif_type)
    {
        $latest_release = $this->get_latest_motif_release($motif_type);
        // count motifs
        $this->db->select('count(distinct motif_id) as counts')
                 ->from('ml_loops')
                 ->where('release_id', $latest_release)
                 ->like('ml_loops_id', $motif_type . '_' . $pdb_id, 'right');
        $result = $this->db->get()->row();

        return $result->counts;
    }

    function get_pairwise_info($pdb_id, $interaction)
    {
        $this->db->select("count($interaction)/2 as counts")
                 ->from('unit_pairs_interactions')
                 ->where('pdb_id', $pdb_id);

        if ( $interaction == 'f_bphs' ) {
            $this->db->where("char_length($interaction) = 4");
        } else {
            $this->db->where("char_length($interaction) = 3");
        }

        $result = $this->db->get()->row();

        return number_format($result->counts, 0);
    }

    function get_related_structures($pdb_id)
    {
        $pdb_id = strtoupper($pdb_id);
        $latest_nr_release = $this->get_latest_nr_release($pdb_id);

        // choose the equivalence class
        $this->db->select('np.nr_class_id')
                 ->from('nr_chains AS np')
                 ->join('nr_classes AS nc', 'np.nr_class_id = nc.nr_class_id')
                 ->join('ife_info AS ii', 'np.ife_id = ii.ife_id')
                 ->where('ii.pdb_id', $pdb_id)
                 ->where('np.nr_release_id', $latest_nr_release)
                 ->where('nc.resolution', 'all')
                 ->where('nc.nr_release_id', $latest_nr_release);
        $result = $this->db->get();

        if ( $result->num_rows() == 0 ) {
            $equivalence_class = 'Not a member of any equivalent class, most likely due to the absence of complete nucleotides.';
            $equivalence_class_found = False;
        } else {
            $equivalence_class = $result->row()->nr_class_id;
            $equivalence_class_found = True;
        }

        $pdbs = array();
        $representative = Null;

        if ( $equivalence_class_found ) {
            // choose all structures from the selected equivalence class
            $this->db->select('ii.pdb_id')
                     ->from('nr_chains AS np')
                     ->join('ife_info AS ii', 'np.ife_id = ii.ife_id')
                     ->where('nr_release_id', $latest_nr_release)
                     ->where('nr_class_id', $equivalence_class)
                     ->order_by('rep', 'desc');
            $query = $this->db->get();

            $isFirst = True;

            foreach($query->result() as $row) {
                if ( $isFirst ) {
                    $representative = $row->pdb_id;
                    $isFirst = False;
                }

                if ( $row->pdb_id != $pdb_id ) {
                    $pdbs[] = $row->pdb_id;
                }
            }
        }

        return array('related_pdbs' => $pdbs,
                     'eq_class' => $equivalence_class,
                     'representative' => $representative);
    }

    function get_ordered_nts($pdb_id)
    {
        $this->db->select('best_chains')
                 ->from('pdb_best_chains_and_models')
                 ->where('pdb_id', $pdb_id);

        $result = $this->db->get()->row();
        $chains = explode(",", $result->best_chains);

        #$this->db->select('pu.unit_id as id, pc.chain as chain, pc.unit as sequence')
        #         ->from('pdb_unit_ordering AS uo')
        #         ->join('__pdb_coordinates AS pc', 'pc.pdb_coordinates_id = uo.nt_id')
        #         ->join('__pdb_unit_id_correspondence AS pu', 'pu.old_id = uo.nt_id')
        #         ->where('uo.pdb', $pdb_id)
        #         ->where_in('pc.chain', $chains)
        #         ->order_by('uo.index', 'asc');

        $this->db->select('unit_id as id, chain, unit as sequence')
                 ->from('unit_info')
                 ->where('pdb_id', $pdb_id)
                 ->where_in('chain', $chains)
                 ->order_by('number', 'asc');

        $query = $this->db->get();
        $chain_data = array();

        foreach($chains as $chain) {
            $chain_data[$chain] = array('id' => 'chain-' + $chain,
                                        'nts' => array());
        }

        foreach($query->result() as $row) {
            $chain_data[$row->chain]['nts'][] = array('id' => $row->id,
                                                      'sequence' => $row->sequence);
        }
        
        return array_values($chain_data);
    }

    function get_airport($pdb_id)
    {
        $table='pdb_airport';

        if (! $this->db->table_exists($table)) {
            return false;
        }

        $this->db->select('json_structure')
                 ->from($table)
                 ->where('pdb_id', $pdb_id);

        $result = $this->db->get()->row();

        return ($result) ? $result->json_structure : false;
    }

    function get_longrange_bp($pdb)
    {
        $this->db->select('U1.unit_id as nt1')
                 ->select('U2.unit_id as nt2')
                 ->select('upi.f_lwbp as family')
                 ->select('upi.f_crossing as crossing')
                 ->from('unit_pairs_interactions AS upi')
                 ->join('unit_info as U1', 'U1.unit_id = upi.unit_id_1')
                 ->join('unit_info as U2', 'U2.unit_id = upi.unit_id_2')
                 ->where('upi.pdb_id', $pdb)
                 ->where('f_crossing > 3')
                 ->where('f_lwbp is not null');

        $query = $this->db->get();

        if (!$query->num_rows()) {
            return array();
        }

        foreach($query->result() as $row) {
            $longrange[] = $row;
        }

        return $longrange;
    }
}

/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */
