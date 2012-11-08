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
        $this->db->select('distinct(structureId)')
                 ->from('pdb_info');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $pdbs[] = $row->structureId; //anchor(base_url(array('pdb',$row->structureId)), $row->structureId );
        }
        return $pdbs;
    }

    function get_recent_rna_containing_structures($num)
    {
        $this->db->select('distinct(structureId)')
                 ->from('pdb_info')
                 ->order_by('releaseDate', 'desc')
                 ->limit($num);
        $query = $this->db->get();
        foreach($query->result() as $row) {
            $pdbs[] = $row->structureId;
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
                 ->like('id', strtoupper($loop_type) . '_' . $pdb_id, 'right');
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data[$row->id] = $row->motif_id;
        }
        return $data;
    }

    function get_loops($pdb_id)
    {
        $release_id = $this->get_latest_loop_release();
        $this->db->select()
                 ->from('loop_qa')
                 ->join('loops_all', 'loops_all.id=loop_qa.id')
                 ->where('pdb', $pdb_id)
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
            $loop_type = substr($row->id, 0, 2);
            if ($row->status == 1) {
                if ( array_key_exists($row->id, $motifs) ) {
                    $motif_id = anchor_popup("motif/view/{$motifs[$row->id]}", $motifs[$row->id]);
                } else {
                    $motif_id = 'NA';
                }
                $valid_tables[$loop_type][] = array(count($valid_tables[$loop_type]) + 1,
                                                    array( 'class' => 'loop',
                                                           'data'  => $this->get_checkbox($row->id, $row->nt_ids)
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
                                                             'data'  => $this->get_checkbox($row->id, $row->nt_ids)
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
        return "<label><input type='radio' name='p' id='{$id}' class='jmolInline' data-type='loop_id' data-nt='{$id}'>{$id}</label>" .
        "<span class='loop_link'>" . anchor_popup("loops/view/{$id}", '&#10140;') . "</span>" ;
    }

    function get_latest_loop_release()
    {
        $this->db->select('id')
                 ->from('loop_releases')
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->result_array();
        return $result[0]['id'];
    }

    function pdb_exists($pdb_id)
    {
        // does RNA 3D Hub know about this structure?
        $this->db->select('structureId')
                 ->from('pdb_info')
                 ->where('structureId', $pdb_id)
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
        $this->db->select()
                 ->from('pdb_analysis_status')
                 ->where('id', $pdb_id)
                 ->where("$interaction_type IS NOT NULL");
        if ( $this->db->get()->num_rows() > 0 ) {
            return True;
        } else {
            return False;
        }
    }

    function _get_unit_ids($pdb_id)
    {
        // get correspondences between old and new ids
        $this->db->select('old_id, unit_id')
                 ->from('pdb_unit_id_correspondence')
                 ->where('pdb', $pdb_id);
        $query = $this->db->get();
        $unit_ids = array();
        foreach ( $query->result() as $row ) {
            $unit_ids[$row->old_id] = $row->unit_id;
        }

        // if no new unit ids are found, fall back on old ids
        if ( count($unit_ids) == 0 ) {
            $this->db->select('id')
                     ->from('pdb_coordinates')
                     ->where('pdb', $pdb_id);
            $query = $this->db->get();
            foreach ( $query->result() as $row ) {
                $unit_ids[$row->id] = $row->id;
            }
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

        $this->db->select('iPdbSig,jPdbSig,' . $db_field)
                 ->from('pdb_pairwise_interactions')
                 ->join('pdb_coordinates', 'pdb_pairwise_interactions.iPdbSig = pdb_coordinates.id')
                 ->where('pdb_id', $pdb_id)
                 ->where($where)
                 ->order_by('index');
        $query = $this->db->get();

        $i = 1;
        $html = '';
        $csv  = '';
        foreach ( $query->result() as $row ) {
            $output_fields = array();
            $csv_fields    = array();

            $csv_fields[0] = $unit_ids[$row->iPdbSig];
            foreach ($targets as $target) {
                if ( isset($row->{$db_fields[$target]}) and ($row->{$db_fields[$target]} != '') ) {
                    $output_fields[] = $row->{$db_fields[$target]};
                    $csv_fields[]    = $row->{$db_fields[$target]};
                } else {
                    $csv_fields[] = '';
                }
            }
            $csv_fields[] = $unit_ids[$row->jPdbSig];

            $html .= '<span>' .
                      str_pad($unit_ids[$row->iPdbSig], 20, ' ') .
                    "</span>".
                    "<a class='jmolInline' id='s{$i}'>" .
                      str_pad(implode(', ', $output_fields), 20, ' ', STR_PAD_BOTH) .
                    "</a>" .
                    "<span>" .
                    str_pad($unit_ids[$row->jPdbSig], 20, ' ', STR_PAD_LEFT) .
                    "</span>\n";
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
        $this->db->select()
                 ->from('pdb_info')
                 ->where('structureId', $pdb_id);
        $query = $this->db->get();

        $data['rna_chains'] = 0;
        foreach ($query->result() as $row) {
            // get this info only once because it applies to all chains
            if ( $data['rna_chains'] == 0 ) {
                $data['structureTitle'] = $row->structureTitle;
                $data['experimentalTechnique'] = $row->experimentalTechnique;
                $data['resolution'] = $row->resolution;
                $data['releaseDate'] = $row->releaseDate;
                $data['structureAuthor'] = $row->structureAuthor;
                $data['pdb_url'] = "http://www.pdb.org/pdb/explore/explore.do?structureId={$pdb_id}";
                $data['ndb_url'] = "http://ndbserver.rutgers.edu/servlet/IDSearch.NDBSearch1?id={$row->ndbId}";
            }
            // only for RNA chains
            if ( $row->entityMacromoleculeType == 'Polyribonucleotide (RNA)' or
                 $row->entityMacromoleculeType == 'DNA/RNA Hybrid' ) {
                $data['rna_chains']++;
                $organisms[] = $row->source;
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
        $this->db->select('id')
                 ->from('nr_releases')
                 ->order_by('date', 'desc')
                 ->limit(1);
        $result = $this->db->get()->row();
        return $result->id;
    }

    function get_nrlist_info($pdb_id)
    {
        // get the latest nr release
        $data['latest_nr_release'] = $this->get_latest_nr_release($pdb_id);

        // get nr equivalence classes
        $this->db->select()
                 ->from('nr_pdbs')
                 ->where('id', $pdb_id)
                 ->where('release_id', $data['latest_nr_release']);
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data['nr_classes'][] = $row->class_id;
            $data['nr_urls'][$row->class_id] = anchor('nrlist/view/' . $row->class_id, $row->class_id);
            $data['representatives'][$row->class_id] = $row->rep;
        }
        return $data;
    }

    function get_loops_info($pdb_id)
    {
        $this->db->select('count(*) as counts, type')
                 ->from('loops_all')
                 ->where('pdb', $pdb_id)
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
        $this->db->select('id')
                 ->from('ml_releases')
                 ->order_by('date', 'desc')
                 ->where('type', $motif_type)
                 ->limit(1);
        $result = $this->db->get()->row();
        return $result->id;
    }

    function get_motifs_info($pdb_id, $motif_type)
    {
        $latest_release = $this->get_latest_motif_release($motif_type);
        // count motifs
        $this->db->select('count(distinct motif_id) as counts')
                 ->from('ml_loops')
                 ->where('release_id', $latest_release)
                 ->like('id', $motif_type . '_' . $pdb_id, 'right');
        $result = $this->db->get()->row();

        return $result->counts;
    }

    function get_pairwise_info($pdb_id, $interaction)
    {
        $this->db->select("count($interaction)/2 as counts")
                 ->from('pdb_pairwise_interactions')
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
        $latest_nr_release = $this->get_latest_nr_release($pdb_id);

        // choose the equivalence class
        $this->db->select()
                 ->from('nr_pdbs')
                 ->join('nr_classes', 'nr_pdbs.class_id = nr_classes.id')
                 ->where('nr_pdbs.id', $pdb_id)
                 ->where('nr_pdbs.release_id', $latest_nr_release)
                 ->where('nr_classes.resolution', 'all')
                 ->where('nr_classes.release_id', $latest_nr_release);
        $result = $this->db->get();
        if ( $result->num_rows() == 0 ) {
            $equivalence_class = 'Not a member of any equivalent class, most likely due to the absence of complete nucleotides.';
            $equivalence_class_found = False;
        } else {
            $equivalence_class = $result->row()->class_id;
            $equivalence_class_found = True;
        }

        $pdbs = array();
        if ( $equivalence_class_found ) {
            // choose all structures from the selected equivalence class
            $this->db->select()
                     ->from('nr_pdbs')
                     ->where('release_id', $latest_nr_release)
                     ->where('class_id', $equivalence_class)
                     ->order_by('rep', 'desc');
            $query = $this->db->get();

            foreach($query->result() as $row) {
                if ( $row->id != $pdb_id ) {
                    $pdbs[] = "<a class='pdb'>{$row->id}</a>";
                }
            }
        }
        return array('related_pdbs' => $pdbs, 'eq_class' => $equivalence_class);
    }

}

/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */