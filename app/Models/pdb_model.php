<?php

namespace App\Models;
use CodeIgniter\Model;
ini_set("memory_limit","300M");
class Pdb_model extends Model {

    public array $qa_status = [];

    function __construct()
    {
        // $CI = & get_instance();
        // $CI->load->helper('url');
        $this->qa_status = array(NULL,'valid','missing nts','modified nts','abnormal chain','incomplete nts','complementary','symmetry','all high RSRZ','pair high RSRZ');
        // Call the Model constructor
        parent::__construct();
    }
    function get_all_pdbs()
    {
        $query = $this->db->table('pdb_info')
                  ->select('distinct(pdb_id)')
                  ->get()
                  ->getResult();
        foreach ($query as $row) {
            $id = $row->pdb_id;
            if ($id != 'XXXX') {
                $pdbs[] = $id;
            }
        }
        return $pdbs;
    }

    function get_all_pdbs_data()
    {
        $query = $this->db->table('pdb_info')
                  ->select('pdb_id,release_date')
                  ->get()
                  ->getResult();
        $data = array();
        foreach ($query as $row) {
            $id = $row->pdb_id;
            if ($id != 'XXXX') {
                $data[$id] = $row;
            }
        }
        return $data;
    }

    function get_recent_rna_containing_structures($num)
    // Note: now that we have DNA structures, this will also show DNA structures with no RNA
    {
        $query = $this->db->table('pdb_info')
                 ->select('distinct(pdb_id)')
                 ->orderBy('release_date', 'desc')
                 ->limit($num)
                 ->get()
                 ->getResult();
        foreach($query as $row) {
            $pdbs[] = $row->pdb_id;
        }
        return $pdbs;
    }
    function get_latest_motif_assignments($pdb_id, $loop_type)
    {
        // This does not actually get all the most recent assignments
        // It only searches the most recent motif atlas release
        // If the structure has loops in that release, they are shown
        // If it does not, then no motif assignments are shown
        // That may be safer for structures that used to be in the Motif Atlas

        // $loop_type = IL or HL

        $latest_release = $this->get_latest_motif_release($loop_type);

        $query = $this->db->table('ml_loops')
                 ->select()
                 ->where('ml_release_id', $latest_release)
                 ->like('loop_id', strtoupper($loop_type) . '_' . $pdb_id, 'right');
        $query = $query->get()->getResult();
        $data = array();
        foreach ($query as $row) {
            $data[$row->loop_id] = $row->motif_id;
        }
        return $data;
    }
    function get_all_latest_motif_assignments($loop_type) // new
    {
        // This does not actually get all the most recent assignments
        // It only searches the most recent motif atlas release
        // If the structure has loops in that release, they are shown
        // If it does not, then no motif assignments are shown
        // That may be safer for structures that used to be in the Motif Atlas

        // $loop_type = IL or HL

        $latest_release = $this->get_latest_motif_release($loop_type);
        $builder = $this->db->table('ml_loops');
        $query = $builder ->select()
                 ->where('ml_release_id', $latest_release)
                 ->like('loop_id', strtoupper($loop_type) . '_', 'right');
        $query = $query->get()->getResult();
        $data = array();
        foreach ($query as $row) {
            $data[$row->loop_id] = $row->motif_id;
        }
        return $data;
    }
    function get_latest_loop_release()
    {
        $query = $this->db->table('loop_releases')
                 ->select('loop_release_id')
                 ->orderBy('date','desc')
                 ->limit(1)
                 ->get()
                 ->getResult();
        return $query[0]['loop_release_id'];
    }
    function get_latest_loop_release_for_this_pdb($pdb_id)
    {
        $query = $this->db->table('loop_releases AS lr')
                 ->select('lq.loop_release_id')
                 ->join('loop_qa AS lq', 'lq.loop_release_id = lr.loop_release_id')
                 ->join('loop_info AS li', 'li.loop_id = lq.loop_id')
                 ->where('pdb_id', $pdb_id)
                 ->orderBy('date','desc')
                 ->limit(1)
                 ->get()
                 ->getResult();
        return $query[0]['loop_release_id'];
    }
    function get_loop_mappings($pdb_id)
    {
        $builder = $this->db->table('loop_mapping AS lm');
    	$query = $builder ->select('lm.loop_id')
    			->select('lm.query_loop_id AS similar_loop')
    			->select('la.annotation_1 AS similar_annotation')
    			->select('lm.match_type')
    			->join('loop_annotations AS la', 'lm.query_loop_id = la.loop_id', 'left')
    			->where('lm.pdb_id', $pdb_id)
    			->orderBy('loop_mapping_id'); // use (...,'desc') if opposite order
    	$query = $query->get()->getResult();
    	$loop_mapping_table = array();
		foreach ($query as $row) {
            $loop_mapping_table[$row->loop_id] = $row;
        }
        // need to add loop annotations
    	return $loop_mapping_table;
    }

    function get_loops($pdb_id)
    {
        // sub query for only most recent loop_mapping per loop id
        $loop_mapping_table = $this->get_loop_mappings($pdb_id);

        // big query for output table
        // CAST(li.sort_name AS BINARY) makes the sort case sensitive, to separate chain 1a from 1A
        $query = $this->db->table('loop_qa AS lq')
                 ->select('lq.loop_id')
                 ->select('lq.status')
                 ->select('lq.modifications')
                 ->select('lq.complementary')
                 ->select('li.loop_name')
                 ->select('li.sort_name')
                 ->select('li.deprecate')
                 ->select('la.annotation_1')
                 ->join('loop_info AS li', 'li.loop_id = lq.loop_id')
                 ->join('loop_annotations AS la', 'lq.loop_id = la.loop_id', 'left')
                 ->where('li.pdb_id', $pdb_id)
                 ->orderBy('CAST(li.sort_name AS BINARY),li.loop_id')
                 ->get()
                 ->getResult();

        $loop_types = array('IL','HL','J');
        foreach ($loop_types as $loop_type) {
            $valid_tables[$loop_type] = array();
            $invalid_tables[$loop_type] = array();
        }

        // $motifs = $this->get_latest_motif_assignments($pdb_id, 'IL');
        // $motifs = array_merge($motifs, $this->get_latest_motif_assignments($pdb_id, 'HL'));
        $motifs = $this->get_all_latest_motif_assignments('IL');
        $motifs = array_merge($motifs, $this->get_all_latest_motif_assignments('HL'));

        foreach ($query as $row) {
            $loop_type = substr($row->loop_id, 0, 2);

            // Put J3, J4, J5, etc. all together
            if ($loop_type[0] == 'J') {
                $loop_type = 'J';
            }

            // building direct annotation + motif group (column 4)
            if (!is_null($row->annotation_1)){
                  $annotation = $row->annotation_1;
                } else {
                  if( array_key_exists($row->loop_id, $loop_mapping_table) ){
                    if(!is_null($loop_mapping_table[$row->loop_id]->similar_annotation)) {
                      $annotation = $loop_mapping_table[$row->loop_id]->similar_annotation;
                    } else {
                      $annotation = "No text annotation";
                    }
                  } else {
                    $annotation = "No text annotation";
                  }
                }

            if (($row->status == 1 or $row->status == 3) and $row->deprecate == 0) {

                if ( array_key_exists($row->loop_id, $motifs) ) {
                    $motif_id = anchor_popup("motif/view/{$motifs[$row->loop_id]}",
                      $motifs[$row->loop_id]);
                } else {
                  $motif_id = '';
                }

                $annotation_and_motif_group = "{$annotation}<br>{$motif_id}";

                // building loop mapping info (column 5)
                if ( array_key_exists($row->loop_id, $loop_mapping_table) ){
                    // if($row->loop_id != $loop_mapping_table[$row->loop_id]->similar_loop){
                    if(is_null($row->annotation_1) && !array_key_exists($row->loop_id, $motifs)){
                    $match_type = $loop_mapping_table[$row->loop_id]->match_type;

                    $similar_loop = anchor_popup("loops/view/{$loop_mapping_table[$row->loop_id]->similar_loop}",
                        $loop_mapping_table[$row->loop_id]->similar_loop);

                    if ( array_key_exists($loop_mapping_table[$row->loop_id]->similar_loop, $motifs) ) {
                        $similar_motif = anchor_popup("motif/view/{$motifs[$loop_mapping_table[$row->loop_id]->similar_loop]}", $motifs[$loop_mapping_table[$row->loop_id]->similar_loop]);
                    } else {
                        $similar_motif = 'NA';
                    }
                    $loop_mapping_info = "{$match_type}<br>{$similar_loop}<br>{$similar_motif}";
                    } else {
                    $loop_mapping_info = "";
                    }
                } else {
                    $loop_mapping_info = "";
                }


                $valid_tables[$loop_type][] = array(array( 'class' => 'loop',
                                                            'data' => $this->get_checkbox($row->loop_id)
                                                        ),
                                                    anchor_popup("loops/view/{$row->loop_id}", $row->loop_id), // turning loop id into link
                                                    str_replace(",", ",<br>", $row->loop_name), //location
                                                    // $motif_id, //motif
                                                    $annotation_and_motif_group, // column 4
                                                    $loop_mapping_info // column 5
                                                    );
            } else {
                // if (!is_null($row->complementary)) {
                //     $annotation = $row->complementary;
                // } elseif (!is_null($row->modifications)) {
                //     $annotation = $row->modifications;
                // } else {
                //     $annotation = $row->nt_signature;
                // }
                $invalid_tables[$loop_type][] = array(array( 'class' => 'loop',
                                                             'data' => $this->get_checkbox($row->loop_id)
                                                         ),
                                                      anchor_popup("loops/view/{$row->loop_id}",
                                                        $row->loop_id),
                                                      $this->make_reason_label($row->status,$row->deprecate),
                                                      $annotation);
            }
        }
        return array('valid' => $valid_tables, 'invalid' => $invalid_tables);
    }
    function make_reason_label($status,$deprecate)
    {
        if ($deprecate == 1) {
            return '<label class="label important">Deprecated</label>';
        } else {
            return '<label class="label important">' . $this->qa_status[$status] . '</label>';
        }
    }
    function get_checkbox($id)
    {
        return "<input type='radio' id='{$id}' class='jmolInline' data-coord='{$id}' data-quality='{$id}'>";
    }
    function pdb_exists($pdb_id)
    {
        // does BGSU RNA Site know about this structure?
        $builder = $this->db->table('pdb_info');
        $query = $builder ->select('pdb_id')
                 ->where('pdb_id', $pdb_id)
                 ->limit(1);
        if ( $query->get()->getRow() != NULL ) {
            return true;
        }
        // if not, is it in PDB itself?
        $pdb_version_url = 'https://www.rcsb.org/versions/' . $pdb_id;
        $headers = @get_headers($pdb_version_url);

        // If headers are returned and the first header contains '200', the URL exists
        if ($headers && strpos($headers[0], '200') !== false) {
            return true; // URL exists
        } else {
            return false; // URL does not exist
        }

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
        $builder = $this->db->table('pdb_info AS pi');
        $query = $builder ->select('pi.pdb_id')
                 ->where('pi.pdb_id', $pdb_id);
        if ( $query->get()->getResult() != NULL ) {
            return True;
        } else {
            return False;
        }
    }
    function _get_unit_ids($pdb_id)
    {
        // retrieve all unit_id values from unit_info
        $builder = $this->db->table('unit_info');
        $query = $builder ->select('unit_id')
                 ->where('pdb_id', $pdb_id);
        $query = $query->get()->getResult();
        $unit_ids = array();
        foreach ( $query as $row ) {
            $unit_ids[$row->unit_id] = $row->unit_id;
        }
        return $unit_ids;
    }
    function get_interactions($pdb_id, $interaction_type)
    {
        /* Commented out since we don't have aa-nt annotations yet
        if ( $interaction_type == 'baseaa' ) {
            $unit_ids = $this->_get_unit_ids($pdb_id);
            $query = $this->db->table('unit_aa_interactions AS uai')
                 ->select('uai.na_unit_id, uai.aa_unit_id, uai.annotation, uai.value')
                 ->join('unit_info AS u1', 'uai.na_unit_id = u1.unit_id')
                 ->join('unit_info AS u2', 'uai.aa_unit_id = u2.unit_id')
                 ->where('uai.pdb_id', $pdb_id)
                 ->orderBy('u1.chain, u1.chain_index, u2.chain, u2.chain_index');
            $query = $query->get()->getResult();
            foreach($query as $row) {
                $na_unit_id[] = $row->na_unit_id;
                $aa_unit_id[] = $row->aa_unit_id;
                $annotation[] = $row->annotation;
                $value[] = $row->value;
            }
            $array_size = count($na_unit_id);
            $html = '';
            for ($i = 0; $i <= ($array_size-1); $i++) {
                // Don't display value for cation-pi interactions
                if ($value[$i] == NULL) {

                    $html .= str_pad('<span>' . $na_unit_id[$i] . '</span>', 38, ' ') .
                             "<a class='jmolInline' id='s{$i}'>" .
                             str_pad( '<span>' . $annotation[$i] . '</span>' , 10, '',STR_PAD_BOTH) .
                             "</a>" .
                             str_pad('<span>' . $aa_unit_id[$i] . '</span>', 38, ' ', STR_PAD_LEFT) .
                             "\n";
                } else {
                    $html .= str_pad('<span>' . $na_unit_id[$i] . '</span>', 38, ' ') .
                             "<a class='jmolInline' id='s{$i}'>" .
                             str_pad( '<span>' . $annotation[$i] . '</span>', 10, '', STR_PAD_BOTH) .
                             "</a>" .
                             str_pad('<span>' . $aa_unit_id[$i] . '</span>', 38, ' ', STR_PAD_LEFT) .
                             "\n";
                }
            }
            return array( 'data'   => $html,
                          'header' => array('#', 'Nucleotide id', 'Amino acid id', "Base-amino acid")
                     );
        }
        */

        $url_parameters = array('basepairs', 'stacking', 'basephosphate', 'baseribose', 'basepair_detail', 'oxygen_stacking', 'sugar_ribose');
        $db_fields      = array('f_lwbp', 'f_stacks', 'f_bphs', 'f_brbs', 'f_lwbp_detail', 'f_so', 'f_sugar_ribose');
        $header_values  = array('Basepair', 'Base-stacking', 'Base-phosphate', 'Base-ribose', 'Basepair detail', 'Oxygen stacking', 'Sugar-ribose');
        $header         = array('#', 'Nucleotide id 1', 'Nucleotide id 2');

        if (in_array($interaction_type, $url_parameters) ) {
            $targets = array_keys($url_parameters, $interaction_type);
            $db_field = $db_fields[$targets[0]];
            $interaction_description = $header_values[$targets[0]];
            $has_desired_interaction_type = "$db_field IS NOT NULL";
        } elseif ( $interaction_type == 'all' ) {
            $targets = array_keys(array_slice($url_parameters,1));
            $db_field = implode(',', array_slice($db_fields,1));
            $interaction_description = implode(',', array_slice($header_values,1));
            $has_desired_interaction_type = '(' . implode(' IS NOT NULL OR ', $db_fields) . ')';
        } elseif ($interaction_type == 'ligand') {



        } else {
            return array( 'data'   => array(),
                          'header' => array(),
                          'csv'    => ''
                         );
        }

        $query = $this->db->table('unit_pairs_interactions_2024 AS upi')
                ->select('program, upi.unit_id_1, upi.unit_id_2,' . $db_field)
                ->join('unit_info AS u1', 'upi.unit_id_1 = u1.unit_id')
                ->join('unit_info AS u2', 'upi.unit_id_1 = u2.unit_id')
                ->where('upi.pdb_id', $pdb_id)
                ->where($has_desired_interaction_type)
                ->orderBy('u1.model, u1.chain, u1.sym_op, u1.chain_index, u2.model, u2.chain, u2.sym_op, u2.chain_index')
                ->get()
                ->getResult();

        $i = 1;
        $html = '';
        $csv  = '';
        foreach ($query as $row) {
            $output_fields = array();
            $csv_fields    = array();
            $csv_fields[0] = $row->unit_id_1;
            foreach ($targets as $target) {
                if ( isset($row->{$db_fields[$target]}) and ($row->{$db_fields[$target]} != '') ) {
                    $output_fields[] = $row->{$db_fields[$target]};
                    $csv_fields[]    = $row->{$db_fields[$target]};
                } else {
                    $csv_fields[] = '';
                }
            }
            $csv_fields[] = $row->unit_id_2;
            $ids = $row->unit_id_1 .','. $row->unit_id_2;
            $html .= str_pad('<span>' . $row->unit_id_1 . '</span>', 32, ' ') .
                    "<a class='jmolInline' id='s{$i}'>" .
                    str_pad(implode(', ', $output_fields), 8, ' ', STR_PAD_BOTH) .
                    "</a>" .
                    str_pad('<span>' . $row->unit_id_2. '</span>', 32, ' ', STR_PAD_LEFT) . ' <a href="http://rna.bgsu.edu/correspondence/SVS?id=' . $ids . '&format=unique&input_form=True" target="_blank" rel="noopener noreferrer">R3DSVS</a>' .
                    "\n";
            $csv .= '"' . implode('","', $csv_fields) . '"' . "\n";
            $i++;
        }

        $header2 = array_merge( $header, explode(',', $interaction_description) );
        return array( 'data'   => $html,
                      'header' => array_merge( $header, explode(',', $interaction_description) ),
                      'csv'    => $csv
                     );
    }

    function get_general_info($pdb_id)
    {
        // get a list of all chains in the pdb_id
        $builder = $this->db->table('pdb_info AS pi');
        $query = $builder ->select()
                 ->join('chain_info AS ci', 'pi.pdb_id = ci.pdb_id', 'left')
                 ->where('pi.pdb_id', $pdb_id);
        $query = $query->get()->getResult();

        $rna_types   = ['Polyribonucleotide (RNA)','polyribonucleotide'];
        $hybrid_types = ['DNA/RNA Hybrid','polydeoxyribonucleotide/polyribonucleotide hybrid'];
        $dna_types    = ['Polydeoxyribonucleotide (DNA)','polydeoxyribonucleotide'];
        $pna_types    = ['Peptide nucleic acid'];

        $rna_compounds = array();
        $hybrid_compounds = array();
        $dna_compounds = array();
        $pna_compounds = array();
        $non_na_compounds = array();

        $c = 0;
        foreach ($query as $row) {
            $ndb_id = ( $row->ndb_id ) ? $row->ndb_id : $pdb_id;
            if ( $c == 0 ) {
                // get this info only once because it applies to all chains
                $data['title'] = $row->title;
                $data['experimental_technique'] = $row->experimental_technique;
                $data['resolution'] = $row->resolution;
                $data['release_date'] = $row->release_date;
                $data['authors'] = $row->authors;
                $data['pdb_url'] = "https://www.rcsb.org/structure/{$pdb_id}";
                $data['ndb_url'] = "http://ndbserver.rutgers.edu/service/ndb/atlas/summary?searchTarget={$ndb_id}";
                $data['NAKB_url'] = "https://www.nakb.org/atlas={$pdb_id}";
                $c++;
            }
            // only for RNA chains
            // Todo: add a section for DNA chains as well
            if (in_array($row->entity_macromolecule_type, $rna_types)) {
                $organisms[] = $row->source;
                $data['rna_compounds'][] = array("chain"      => $row->chain_id,
                                                 "chain_name" => $row->chain_name,
                                                 "compound"   => $row->compound,
                                                 "length"     => $row->chain_length,
                                                 "organism"   => $row->source);
                $rna_compounds[] = $row->compound;
            } elseif (in_array($row->entity_macromolecule_type, $hybrid_types)) {
                $hybrid_compounds[] = $row->compound;
            } elseif (in_array($row->entity_macromolecule_type, $dna_types)) {
                $dna_compounds[] = $row->compound;
            } elseif (in_array($row->entity_macromolecule_type, $pna_types)) {
                $pna_compounds[] = $row->compound;
            } else {
                $non_na_compounds[] = $row->compound;
            }
        }
        if ( empty($organisms) ) {
          $organisms[] = 'synthetic';
        }
        $data['rna_chains'] = count($rna_compounds);
        $data['hybrid_chains'] = count($hybrid_compounds);
        $data['dna_chains'] = count($dna_compounds);
        $data['pna_chains'] = count($pna_compounds);
        $data['non_na_chains'] = count($query) - $data['rna_chains'] - $data['hybrid_chains'] - $data['dna_chains'] - $data['pna_chains'];

        $data['organisms'] = implode(', ', array_unique($organisms));
        $data['na_compounds'] = implode(', ', array_merge($rna_compounds, $hybrid_compounds, $dna_compounds, $pna_compounds));
        // $data['compounds'] = implode(', ', array_merge($rna_compounds, $hybrid_compounds, $dna_compounds, $pna_compounds, $non_na_compounds));
        $data['non_na_compounds'] = implode(', ', $non_na_compounds);
        return $data;
    }
    function get_latest_nr_release($pdb_id)
    {
        $builder = $this->db->table('nr_releases');
        $query = $builder ->select('nr_release_id')
                 ->orderBy('date', 'desc')
                 ->limit(1);
        $query = $query->get()->getRow();
        return $query->nr_release_id;
    }
    function get_nrlist_info($pdb_id)
    {
        // get the latest nr release
        $data['latest_nr_release'] = $this->get_latest_nr_release($pdb_id);
        // get nr equivalence classes
        $builder = $this->db->table('nr_class_rank AS nc');
        $query = $builder ->select('nl.name')
                 ->selectmin('nc.rank')
                 ->select('COUNT(nl.name) AS count')
                 ->join('nr_classes AS nl', 'nc.nr_class_name = nl.name')
                 ->join('ife_info AS ii', 'nc.ife_id = ii.ife_id')
                 ->where('ii.pdb_id', $pdb_id)
                 ->where('nl.nr_release_id', $data['latest_nr_release'])
                 ->groupBy('nl.nr_class_id')
                 ->groupBy('nl.name');
        $query = $query->get()->getResult();
        $data = array();
        foreach ($query as $row) {
            $data['nr_classes'][] = $row->name;
            $data['nr_urls'][$row->name] = anchor('nrlist/view/' . $row->name, $row->name);
            # $data['representatives'][$row->name] = $row->rep;
            if ($row->rank == 0){
                $data['representatives'][$row->name] = 1;
            }
            else {$data['representatives'][$row->name] = 0;}
            $data['count'][$row->name] = $row->count;
        }
        return $data;
    }
    function get_loops_info($pdb_id)
    {
        $query = $this->db->table('loop_info')
                 ->select('count(loop_id) as counts, type')
                 ->where('pdb_id', $pdb_id)
                 ->groupBy('type')
                 ->get()
                 ->getResult();
        $data['loops'] = array();
        $data['loops']['J'] = 0;
        foreach ($query as $row) {
            // if $row->type starts with J, just use J
            if ($row->type[0] == 'J') {
                $data['loops']['J'] += $row->counts;
            } else {
                $data['loops'][$row->type] = $row->counts;
            }
        }

        // add zeros if some loop types are not present
        foreach ( array('IL', 'HL', 'J') as $loop_type ) {
            if ( !array_key_exists($loop_type, $data['loops']) ) {
                $data['loops'][$loop_type] = 0;
            }
        }

        $data['loops']['url'] = anchor('pdb/' . $pdb_id . '/motifs', 'More');

        return $data;
    }
    function get_latest_motif_release($motif_type)
    {
        // this is unreliable as a way to find most recent motif assignment
        $builder = $this->db->table('ml_releases');
        $query = $builder ->select('ml_release_id')
                 ->orderBy('date', 'desc')
                 ->where('type', $motif_type)
                 ->limit(1);

        $result = $query->get()->getRow();

        if ( $result == NULL ) {
            return 0;
        } else {
            return $result->ml_release_id;
        }
    }
    function get_motifs_info($pdb_id, $motif_type)
    {
        // count how many motif groups are present in the loops of the given type
        $latest_release = $this->get_latest_motif_release($motif_type);

        if ( $latest_release == 0 ) {
            return 0;
        }

        // count motifs
        if ($motif_type == 'J') {
            $query = $this->db->table('ml_loops')
                    ->select('count(distinct motif_id) as counts')
                    ->where('ml_release_id', $latest_release)
                    ->like('loop_id', 'J%', 'after')
                    ->like('loop_id', $pdb_id)
                    ->get()
                    ->getRow();
        } else {
            $query = $this->db->table('ml_loops')
                    ->select('count(distinct motif_id) as counts')
                    ->where('ml_release_id', $latest_release)
                    ->like('loop_id', $motif_type . '_' . $pdb_id, 'right')
                    ->get()
                    ->getRow();
        }
        return $query->counts;
    }

    function get_pairwise_info($pdb_id, $interaction_type)
    {
        // query to count the number of interactions of each type
        // start using unit_pairs_interactions_2024 for these counts,
        // even if the numbers are a little different from the
        // matlab interactions still being displayed for older structures

        // omit entries starting with the letter "n"
        $query = $this->db->table('unit_pairs_interactions_2024')
                 ->select("count($interaction_type)/2 as counts")
                 ->where('pdb_id', $pdb_id)
                 ->notLike("$interaction_type", 'n%', 'after');

        // if ( $interaction_type == 'f_bphs' ) {
        //     $query->where("char_length($interaction_type) = 4");
        // } else {
        //     $query->where("char_length($interaction_type) = 3");
        // }

        $result = $query->get()->getRow();
        return number_format($result->counts, 0);
    }

    function get_baseaa_info($pdb_id)
    {
        $builder = $this->db->table('unit_aa_interactions');
        $query = $builder ->select("count(na_unit_id) as counts")
                 ->where('pdb_id', $pdb_id);
        $result = $query->get()->getRow();
        return number_format($result->counts, 0);
    }
    function get_related_structures($pdb_id)
    {
        $pdb_id = strtoupper($pdb_id);
        $latest_nr_release = $this->get_latest_nr_release($pdb_id);
        // choose the equivalence class
        $builder = $this->db->table('nr_class_rank AS ch');
        $query = $builder ->select('cl.nr_class_id')
                 ->select('cl.name')
                 ->join('nr_classes AS cl', 'ch.nr_class_name = cl.name')
                 ->join('ife_info AS ii', 'ch.ife_id = ii.ife_id')
                 ->where('ii.pdb_id', $pdb_id)
                 ->where('cl.nr_release_id', $latest_nr_release)
                 ->where('cl.resolution', 'all');
        $result = $query->get()->getResult();
        if ( count($result) == 0 ) {
            $equivalence_class = 'Not a member of any equivalence class, possibly due to the absence of complete nucleotides.';
            $equivalence_class_name = "";
            $equivalence_class_found = False;
        } else {
            $equivalence_class = $result[0]->nr_class_id;
            $equivalence_class_name = $result[0]->name;
            $equivalence_class_found = True;
        }
        $pdbs = array();
        $representative = Null;
        if ( $equivalence_class_found ) {
            // choose all structures from the selected equivalence class
            $builder = $this->db->table('nr_class_rank AS np');
            $query = $builder ->select('ii.pdb_id')
                     ->join('ife_info AS ii', 'np.ife_id = ii.ife_id')
                     # no need for this limitation
                     # ->where('nr_release_id', $latest_nr_release)
                     ->where('nr_class_id', $equivalence_class)
                     ->orderBy('rank', 'asc');
            $query = $query->get()->getResult();
            $isFirst = True;
            foreach($query as $row) {
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
                     'eq_class' => $equivalence_class_name,
                     'representative' => $representative);
    }

    function get_ordered_nts($pdb_id)
    {
        // look up nucleotides in the chains in $pdb_id
        $query = $this->db->table('unit_info AS ui')
                 ->select('ui.unit_id as id, ui.model, ui.chain, ui.sym_op, ui.unit as sequence, ui.unit_type_id')
                 ->selectmin('ui.sym_op')
                 ->where('ui.pdb_id', $pdb_id)
                 ->where('chain_index is NOT NULL', NULL, FALSE)
                 ->where('model','1')
                 ->orderBy('ui.model', 'asc')
                 ->orderBy('ui.sym_op', 'asc')
                 ->orderBy('ui.chain', 'asc')
                 ->orderBy('ui.chain_index', 'asc')
                 ->orderBy('ui.alt_id', 'desc')
                 ->groupBy('ui.pdb_id, ui.model, ui.sym_op, ui.chain, ui.number, ui.unit, ui.ins_code')
                 ->get()
                 ->getResult();

        $chain_data = array();
        foreach($query as $row) {
            // modified nts can have null unit_type_id, but avoid 'aa'
            if (is_null($row->unit_type_id) or $row->unit_type_id != 'aa') {
                if ($row->sym_op == '1_555') {
                    $chain = $pdb_id . '|' . $row->model . '|' . $row->chain;
                } else {
                    $chain = $pdb_id . '|' . $row->model . '|' . $row->chain . ' ' . $row->sym_op;
                }

                if ( !array_key_exists($chain, $chain_data) ){
                  $chain_data[$chain] = array('id' => $chain,
                                              'chain' => $chain,
                                              'nts' => array());
                }

                $chain_data[$chain]['nts'][] = array('id' => $row->id,
                                                 'sequence' => $row->sequence);
            }
        }

        return $chain_data;
    }

    function get_ordered_nts_very_old($pdb_id)
    {
        $query = this->db->table('unit_info AS ui')
                 ->select('ui.unit_id as id, ui.chain, ui.unit as sequence')
                 ->selectmin('ui.sym_op')
                 ->join('ife_info AS ii', 'ui.pdb_id = ii.pdb_id AND ui.model = ii.model')
                 ->join('ife_chains AS ic', 'ii.ife_id = ic.ife_id AND ii.model = ic.model')
                 ->join('chain_info AS ci', 'ic.chain_id = ci.chain_id AND ui.pdb_id = ci.pdb_id AND ui.chain = ci.chain_name')
                 ->where('ui.pdb_id', $pdb_id)
                 ->where('chain_index is NOT NULL', NULL, FALSE)
                 ->groupBy('ui.pdb_id, ui.model, ui.chain, ui.number, ui.unit, ui.alt_id')
                 ->groupBy('ui.ins_code, ui.chain_index')
                 ->orderBy('ui.chain', 'asc')
                 ->orderBy('ui.number', 'asc');
        $query = $query->get()->getResult();
        $chain_data = array();
        foreach($query as $row) {
            $chain = $row->chain;
            if ( !array_key_exists($chain, $chain_data) ){
              $chain_data[$chain] = array('id' => 'chain-' + $chain,
                                          'nts' => array());
            }

            $chain_data[$chain]['nts'][] = array('id' => $row->id,
                                                 'sequence' => $row->sequence);
        }
        return array_values($chain_data);
    }
    function get_airport($pdb_id)
    {
        $new_result = '';
        $table = 'pdb_airport';
        if (! $this->db->table_exists($table)) {
            return false;
        }
        //
        //  Adding a wrapper around the ss_unit_positions code (2017-06-13).
        //
        //  This block isn't performing well, and the problem is exacerbated
        //    when it is called for a structure which has no presence in the
        //    ss_* hierarchy.
        //
        $query = this->db->table('ss_pdb_mapping')
                 ->select('pdb_id')
                 ->where('pdb_id', $pdb_id)
                 ->get();
        if ($query->getNumRows() > 0) {
            // process ss_unit_positions
            //  Revision:  performance of view ss_unit_positions is horrible, but
            //    the underlying query appears to perform better.
            $query = this->db->table('ss_pdb_mapping AS SPM')
                     ->select('UI.unit_id, SPM.pdb_id, UI.model, SPM.chain_name AS chain, UI.number')
                     ->select('UI.unit, UI.alt_id, UI.ins_code, UI.sym_op, UI.chain_index')
                     ->select('UI.unit_type_id, SP.index, SP.ss_id, SP.x_coordinate')
                     ->select('SP.y_coordinate')
                     ->select("IF(UI.unit_id IS NOT NULL, 1, 0) AS 'is_resolved'",false)
                     ->join('ss_exp_seq_position_mapping AS ESPM', 'ESPM.ss_exp_seq_mapping_id = SPM.ss_exp_seq_mapping_id')
                     ->join('ss_positions AS SP', 'SP.ss_position_id = ESPM.ss_position_id','left')
                     ->join('exp_seq_unit_mapping AS ESUM','ESUM.exp_seq_position_id = ESPM.exp_seq_position_id','left')
                     ->join('unit_info AS UI', 'UI.unit_id = ESUM.unit_id', 'left')
                     ->where('ISNULL(UI.pdb_id)')
                     ->orWhere('UI.pdb_id = SPM.pdb_id')
                     ->where('SPM.pdb_id', $pdb_id)
                     ->groupBy('SP.ss_position_id');
            $query = $query->get()->getResult();
            $nts_data = array();
            $new_json = array();
            $model = '';
            $create = 0;
            foreach ($query as $row) {
                $create = 1;
                if ($row->unit_id){
                    $rowArr = array(
                        'y' => $row->y_coordinate,
                        'x' => $row->x_coordinate,
                        'id' => $row->unit_id,
                        'sequence' => $row->unit
                    );
                    $nts_data[] = $rowArr;
                }
                $model = !($model) ? $row->model : $model;
            }
            if ($create == 1) {
                $new_json = array(
                    'nts'  => $nts_data,
                    'id'   => $row->pdb_id . '|' . $model . '|' . $row->chain,
                    'name' => 'Chain ' . $row->chain
                );
                $json = '[' . json_encode($new_json, JSON_NUMERIC_CHECK) . ']';
            }
        } else {
            $query = this->db->table($table)
                     ->select('json_structure')
                     ->where('pdb_id', $pdb_id);
            $result = $query->get()->getRow();
            $json = ($result) ? $result->json_structure : "";
        }
        return ($json) ? $json : false;
    }

    function get_longrange_bp($pdb)
    {
        // retrieve all basepairs with f_lwbp > 3
        $query = $this->db->table('unit_pairs_interactions_2024 AS upi')
                 ->select('U1.unit_id as nt1')
                 ->select('U2.unit_id as nt2')
                 ->select('upi.f_lwbp as family')
                 ->select('upi.f_crossing as crossing')
                 ->join('unit_info as U1', 'U1.unit_id = upi.unit_id_1')
                 ->join('unit_info as U2', 'U2.unit_id = upi.unit_id_2')
                 ->where('upi.pdb_id', $pdb)
                 ->where('f_crossing > 3')
                 ->where('f_lwbp is not null');
        $query = $query->get()->getResult();

        if ($query === null) {
            return array();
        }

        $longrange = array();
        foreach($query as $row) {
            $longrange[] = $row;
        }
        return $longrange;
    }
}
/* End of file pdb_model.php */
/* Location: ./application/model/pdb_model.php */
