<?php

namespace App\Models;
use CodeIgniter\Model;
use App\Models\ajax_model;
class loops_model extends Model {

    // avoid deprecation error by introducing these here
    public array $qa_status = [];
    public ajax_model $Ajax_model;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        // $CI = & get_instance();

        $this->qa_status = array(NULL,'valid','missing','modified','abnormal','incomplete','complementary');


        $this->Ajax_model = new ajax_model();
    }

    function is_valid_loop_id($id)
    {
        $builder = $this->db->table('loop_info');
        $query = $builder->select('loop_id')
                 ->where('loop_id', $id);
        $query = $query->get()->getResult();
        if ( count($query) > 0 ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function get_loop_list($pdb_id)
    {
        $query = $this->db->table('loop_info')
                 ->select('loop_info.loop_id, group_concat(unit_id order by position_2023) as unit_ids')
                 ->join('loop_positions', 'loop_info.loop_id = loop_positions.loop_id')
                 ->where('loop_info.pdb_id', $pdb_id)
                 ->groupby('loop_info.loop_id')
                 ->get();

        if ($query->getNumRows() > 0) {
            $data = array();
            foreach($query->getResult() as $row) {
                $data[] = '"' . implode('","', array($row->loop_id, $row->unit_ids)) . '"';
            }
            $table = implode("\n", $data);
        } else {
            $table = 'No loops found';
        }

        return $table;

    }


    function get_loop_list_with_breaks($pdb_id)
    {
        // Query for all loops in this pdb file
        // Include deprecated loops, because those are sometimes present in motif atlas releases
        // Concatenate unit_ids and borders into a single string
        // Note that if that string is too long, it will be truncated
        // The default length is 1024, but we set it to a higher number below
        // A loop with 64 nucleotides has length around 1030; 5000 should cover every conceivable loop, right?
        // 2025-03-10 filter out deprecated loops
        $this->db->query("SET SESSION group_concat_max_len = 5000");
        $query = $this->db->table('loop_info AS LI')
                 ->select('group_concat(unit_id order by position_2023) AS unit_ids, group_concat(border order by position_2023) AS borders, LI.loop_id, LI.deprecate', FALSE)
                 ->join('loop_positions AS LP', 'LI.loop_id = LP.loop_id')
                 ->where('LI.pdb_id', $pdb_id)
                 ->groupby('LI.loop_id')
                 ->get();

        if ( $query->getNumRows() > 0 ) {
            $data = array();
            foreach($query->getResult() as $row) {
                if ($row->deprecate == 0) {
                    $deprecated = 'ok';
                } else {
                    $deprecated = 'deprecated';
                }
                $data[] = '"' . implode('","', array($row->loop_id, $row->unit_ids, $row->borders, $deprecated)) . '"';
            }
            $table = implode("\n", $data);
        } else {
            $table = 'No loops found';
        }

        return $table;
    }

    function get_loop_info($id)
    {
        // get information about an individual loop
        $result = array();

        // get Unit ID, sequence, bulges
        $query = $this->db->table('loop_positions')
                    ->select('unit_id, border, bulge')
                    ->orderby('position_2023','asc')
                    ->where('loop_id',$id)
                    ->get()
                    ->getResult();

        $sequence = array();
        $unit_ids = array();
        $bulges = array();
        $count_border = 0;
        $length_sequence = 0;
        $modified_nucleotides = array();

        foreach ($query as $row) {
            $parts = explode('|', $row->unit_id);
            // $sequence[] = $parts[3];

            if (strlen($parts[3]) == 1) {
                $new_base = $parts[3];
            } elseif (in_array($parts[3], ['DA','DC','DG','DT'])) {
                $new_base = $parts[3][1];
            } else {
                $new_base = '(' . $parts[3] . ')';
                $modified_nucleotides[] = $parts[3];
            }

            if ($row->border == 1) {
                $count_border++;
                if ($count_border > 1 and $count_border % 2 == 1) {
                    $sequence[] = '*' . $new_base;
                    $unit_ids[] = '* <br>' . $row->unit_id . '<br>';
                } else {
                    $sequence[] = $new_base;
                    $unit_ids[] = $row->unit_id . '<br>';
                }
            } else {
                $sequence[] = $new_base;
                $unit_ids[] = $row->unit_id . '<br>';
            }
            $length_sequence += 1;
            if ($row->bulge == 1) {
                $bulges[] = $row->unit_id;
            }
        }

        $result['length'] = $length_sequence;
        $result['sequence'] = implode('', $sequence);

        $unique_modifications = array_values(array_unique($modified_nucleotides));
        $modifications = implode(', ', $unique_modifications);

        $result['unit_ids'] = implode('  ', $unit_ids);

        if (count($bulges) > 0) {
            $result['bulges'] = implode(', ', $bulges);
        } else {
            $result['bulges'] = "None detected";
        }

        // qa info
        $builder = $this->db->table('loop_qa');
        $query = $builder->select('status, modifications, nt_signature, complementary')
                 ->where('loop_id',$id)
                 ->limit(1);
        $query = $query->get()->getRow();

        if ($query != NULL) {
            $loop_qa = $query;
            $result['qa'] = 'Unknown status';
            switch ($loop_qa->status) {
                case 1:
                    $result['qa'] = 'Valid loop';
                    break;
                case 2:
                    $result['qa'] = 'Missing nucleotides';
                    break;
                case 3:
                    $result['qa'] = 'Modified nucleotides: ' . $modifications;
                    break;
                case 4:
                    $result['qa'] = 'Abnormal chains';
                    break;
                case 5:
                    $result['qa'] = 'Incomplete nucleotides: ' . $loop_qa->nt_signature;
                    break;
                case 6:
                    $result['qa'] = 'Self-complementary: ' . $loop_qa->complementary;
                    break;
            }
        } else {
            $result['qa'] = 'QA data not found';
        }

        return $result;
    }

    function get_pdb_info($id)
    {
        $result = array();
        //general pdb info
        $result['pdb'] = substr($id,3,4);
        $result['rna3dhub_link'] = anchor_popup('pdb/' . $result['pdb'] . '/motifs', 'RNA 3D Hub');
        $result['pdb_link'] = anchor_popup('https://www.rcsb.org/structure/' . $result['pdb'], 'PDB');
        $result['NAKB_link'] = anchor_popup('https://www.nakb.org/atlas=' . $result['pdb'], 'NAKB');

        $builder = $this->db->table('pdb_info');
        $query = $builder->select('title, experimental_technique, resolution')
                 ->where('pdb_id',$result['pdb'])
                 ->limit(1);
        $query = $query->get()->getRow();

        if ($query!=NULL) {
            $pdb_info = $query;
            $result['pdb_desc'] = $pdb_info->title;
            $result['pdb_exptechnique'] = $pdb_info->experimental_technique;

            if ($pdb_info->resolution == NULL) {
                $result['pdb_resolution'] = '';
            } else {
                $result['pdb_resolution'] = $pdb_info->resolution . ' &Aring;';
            }
        } else {
            $result['pdb_desc'] = '';
            $result['pdb_exptechnique'] = '';
            $result['pdb_resolution'] = '';
        }

        // representative set equivalence class info
        // get latest Representative Set release id
        $builder = $this->db->table('nr_releases');
        $query = $builder->select('nr_release_id')
                 ->orderby('date','desc')
                 ->limit(1);
        $release = $query->get()->getRow();

        // get equivalence classes
        $builder = $this->db->table('nr_pdbs');
        $query = $builder->select('nr_class_name')
                 ->where('pdb_id',$result['pdb'])
                 ->where('nr_release_id', $release->nr_release_id);
        $query = $query->get()->getResult();

        $nr_classes = array();
        foreach ($query as $row) {
            $nr_classes[] = anchor_popup('nrlist/view/' . $row->nr_class_name, $row->nr_class_name);
        }
        $result['nr_classes'] = implode(', ', $nr_classes);

        return $result;
    }


    function get_most_recent_motif_assignment($loop_id)
    {
        // This technique works properly

        $loop_type = substr($loop_id, 0, 2);
        $builder = $this->db->table('ml_loops AS ML');
        $query = $builder->select('ML.motif_id as motif_id, MR.ml_release_id as release_id')
                 ->join('ml_releases AS MR', 'MR.ml_release_id=ML.ml_release_id')
                 ->where('ML.loop_id', $loop_id)
                 ->where('MR.type', $loop_type)
                 ->orderby('MR.date', 'desc');
        $query = $query->get()->getResult();
        if ( count($query) == 0 ) {
            return NULL;
        } else {
            $result = $query;
            return array('motif_id'   => $result[0]->motif_id,
                         'release_id' => $result[0]->release_id);
        }
    }

    function get_motif_info($id)
    {
        $result = array();
        $motif = $this->get_most_recent_motif_assignment($id);

        // get motif annotations
        $builder = $this->db->table('loop_annotations');
        $query = $builder->select()
                        ->where('loop_id', $id);
        $query = $query->get()->getRow();
        $result['annotation_1'] = 'No text annotation';
        $result['annotation_2'] = 'No text annotation';
        if ($query!=NULL) {
            $annotation_1 = $query->annotation_1;
            if ($annotation_1 != Null and $annotation_1 != 'NULL') {
                $result['annotation_1'] = $annotation_1;
            }
            // get annotation 2
            $annotation_2 = $query->annotation_2;
            if ($annotation_2 != Null and $annotation_2 != 'NULL') {
                $result['annotation_2'] = $annotation_2;
            }
        }
        if ($motif != NULL) {
            $result['motif_id'] = $motif['motif_id'];
            $result['motif_url'] = anchor_popup('motif/view/' . $motif['motif_id'], $motif['motif_id']);

            // get basepair signature
            $builder = $this->db->table('ml_motif_annotations');
            $query = $builder->select()
                     ->where('motif_id', $result['motif_id']);
            $query = $query->get()->getResult();
            $annotation = $query;

            if ($query != NULL) {
                $result['bp_signature'] = $annotation[0]->bp_signature;
            } else {
                $result['bp_signature'] = 'Not available';
            }

            // get number of motif instances
            $builder = $this->db->table('ml_loops');
            $query = $builder->select()
                     ->where('ml_release_id',$motif['release_id'])
                     ->where('motif_id', $motif['motif_id']);
            $query = $query->get()->getResult();
            $result['motif_instances'] = count($query);
        } else {
            $result['motif_id'] = "Not in a motif group";
            $result['motif_url'] = "Not in a motif group";
            $result['bp_signature'] = 'Not available';
            $result['motif_instances'] = 0;
        }

        return $result;
    }

    function get_nearby_chains($loop_id,$distance=10)
    {
        $result = array();
        $result['proteins'] = array();
        // $result['rna_chains'] = array(); # standard name array edit

        $unit_ids = $this->Ajax_model->get_loop_units($loop_id);

        if ($unit_ids == False) {
            return $result;
        }

        $neighbor_units = $this->Ajax_model->get_neighboring_units($unit_ids,$distance);

        if (count($neighbor_units) > 0) {

            $known_chains = array();
            foreach ($unit_ids as $ui) {
                $fields = explode('|',$ui);
                $pdb_id = $fields[0];
                $known_chains[] = $fields[2];
            }

            $new_chains = array();
            foreach ($neighbor_units as $nu) {
                $fields = explode('|',$nu);
                $chain = $fields[2];

                if (!in_array($chain,$known_chains)) {
                    $known_chains[] = $chain;
                    $new_chains[] = $chain;
                }
            }

            if (count($new_chains) > 0) {
                $builder = $this->db->table('chain_info');
                $query = $builder->select('chain_name, compound')
                         ->where('pdb_id', $pdb_id)
                         ->wherein('chain_name', $new_chains);
                $query = $query->get()->getResult();

                foreach ($query as $row) {
                    $result['proteins'][$row->chain_name]['description'] = $row->compound;
                }


                // Possibly replace with standardized. #
                $builder = $this->db->table('chain_property_value');
                $query = $builder->select('chain, value')
                            ->where('pdb_id', $pdb_id)
                            ->where('property', 'standardized_name')
                            ->wherein('chain', $new_chains);
                $query = $query->get()->getResult();

                foreach ($query as $row) {
                    $result['proteins'][$row->chain]['description'] = $row->value;
                }
            }
        }

        return $result;
    }


    function get_current_chains($loop_id)
    {
        $result = array();
        $result['current_chains'] = array();
        // $result['rna_chains'] = array(); # standard name array edit

        $unit_ids = $this->Ajax_model->get_loop_units($loop_id);

        // deprecated structures like 4CUX don't have entries in loop_positions table
        if (count($unit_ids) == 0) {
            return $result;
        }

        $current_chains = array();
        foreach ($unit_ids as $ui) {
            $fields = explode('|',$ui);
            $pdb_id = $fields[0];
            $current_chains[] = $fields[2];
        }

        $builder = $this->db->table('chain_info');
        $query = $builder->select('chain_name, compound')
                 ->where('pdb_id', $pdb_id)
                 ->wherein('chain_name', $current_chains);
        $query = $query->get()->getResult();

        foreach ($query as $row) {
            $result['current_chains'][$row->chain_name]['description'] = $row->compound;
        }


        // Possibly replace with standardized. #
        $builder = $this->db->table('chain_property_value');
        $query = $builder->select('chain, value')
                    ->where('pdb_id', $pdb_id)
                    ->where('property', 'standardized_name')
                    ->wherein('chain', $current_chains);
        $query = $query->get()->getResult();

        foreach ($query as $row) {
            $result['proteins'][$row->chain]['description'] = $row->value;
        }

        return $result;
    }

    function get_current_motif_release($motif_type)
    {
        $query = $this->db->table('ml_releases')
                 ->select('ml_release_id')
                 ->where('type', $motif_type)
                 ->orderby('date', 'desc')
                 ->limit(1);
        $query = $query->get()->getResult();
        $row = array_shift($query);
        return $row->ml_release_id;
    }

    function get_similar_loops($id)
    {
        $where = "ls.disc > 0 AND (ls.loop_id_1='$id' OR ls.loop_id_2='$id')";
        $query = $this->db->table('loop_searches AS ls')
                 ->select('ls.loop_id_1, ls.loop_id_2, ls.disc, lq.status, lq.message')
                 ->join('loop_search_qa AS lq','ls.loop_id_1 = lq.loop_id_1 AND ls.loop_id_2 = lq.loop_id_2','left')
                 ->where($where, NULL, FALSE)
                 ->orderby('ls.disc', 'asc');
        $query = $query->get()->getResult();

        $matches = array();
        $table = array();
        $count = 0;
        $motif = $this->get_current_motif_id_from_loop_id($id);
        $ml_release_id = $this->get_current_motif_release(substr($id, 0, 2));

        foreach ($query as $row) {
            // establish what loop is the match of $id
            if ($row->loop_id_1 == $id) {
                $match = $row->loop_id_2;
            } else {
                $match = $row->loop_id_1;
            }

            // exclude rows with reversed orientation of loop_id_1 and loop_id_2
            if ( array_key_exists($match, $matches) ) {
                continue;
            } else {
                $matches[$match]='';
            }

            $count++;

            // find a release id for this loop?
            $q = $this->db->table('ml_loops')
                    ->select()
                     ->where('loop_id',$match)
                     ->where('ml_release_id',$ml_release_id)
                     ->get();

            if ($q->getNumRows() > 0) {
                $result = $q->getRow();
            } else {
                $result = '';
            }

            // compose the message
            if ($row->status == 4) {
                $message = 'Unmatched basepair: ' . $row->message;
            } elseif ($row->status == 5) {
                $message = 'Unmatched near pair: ' . $row->message;
            } elseif ($row->status == 6) {
                $message = 'Unmatched stacking: ' . $row->message;
            } elseif ($row->status == 7) {
                $message = 'Basepair mismatch: ' . $row->message;
            } elseif ($row->status == 7) {
                $message = 'Basepair-basestacking mismatch: ' . $row->message;
            } else {
                $message = '';
            }

            $radiobutton = "<input type='radio'
                                   name='g'
                                   id='s{$count}'
                                   class='jmolTools-loop-pairs'
                                   data-coord='{$id}:{$match}'>
                            <label for='s{$count}'>$match</label>
                            <span class='loop_link'>" . anchor_popup("loops/view/$match", '&#10140;') . "</span>";

            if ( $result == '' ) {
                $motif_link = 'not annotated yet';
            } elseif ($result->motif_id == $motif['motif_id'] ) {
                $motif_link = '<span class="label success">' .
                              anchor_popup('motif/view/' . $result->motif_id, $result->motif_id)
                              . '</span>';
            } else {
                $motif_link = anchor_popup('motif/view/' . $result->motif_id, $result->motif_id);
            }

            $table[] = array($count,
                             array('data' => $radiobutton, 'class' => 'loop'),
                             number_format($row->disc, 4),
                             $motif_link,
                             $message);
        }

        return $table;
    }

    function get_mapped_loop($id){
        $builder = $this->db->table('loop_mapping AS lm');
        $query = $builder->select('lm.loop_id')
                ->select('lm.query_loop_id AS mapped_loop')
                ->select('lm.discrepancy')
                ->select('lm.match_type')
                // ->join('loop_annotations AS la', 'lm.query_loop_id = la.loop_id', 'right')
                ->where('lm.loop_id', $id)
                ->orderby('lm.loop_mapping_id', 'desc') // use (...,'desc') if opposite order
                ->limit(1);
        $query = $query->get()->getResult();

        foreach ($query as $row){
            // $loop_mapping = $row;
            return($row);
        }

        // return $loop_mapping;
    }

    public function get_loop_stats()
    {
        // get loop counts group by loop type
        $builder = $this->db->table('loop_qa');
        $query = $builder->select('status, count(status) as counts, SUBSTRING(loop_id, 1, 2) as loop_type')
                        ->groupBy(['status', 'loop_type']);
        $query_result = $query->get()->getResult();

        $results = [];
        foreach ($query_result as $row) {
            $results[$row->loop_type][$row->status] = $row->counts;
        }

        $tables = [];
        foreach (array_keys($results) as $loop_type) {
            $tables[$loop_type][] = [
                array_sum(array_values($results[$loop_type])),
                $this->make_view_loops_link($results, $loop_type, 1),
                $this->make_view_loops_link($results, $loop_type, 2),
                $this->make_view_loops_link($results, $loop_type, 3),
                $this->make_view_loops_link($results, $loop_type, 4),
                $this->make_view_loops_link($results, $loop_type, 5),
                $this->make_view_loops_link($results, $loop_type, 6)
            ];
        }

        return $tables;
    }

    function make_view_loops_link($counts,$motif_type,$status)
    {
        if (!array_key_exists($status,$counts[$motif_type])) {
            return '0';
        }

        $type = $this->qa_status[$status];
        if ($type == 'complementary' and $motif_type != 'IL') {
            return 'N/A';
        }
        else {
            return anchor(
                          base_url(array('loops','view_all',$type,$motif_type)),
                          $counts[$motif_type][$status]
                          );
        }
    }

    function make_ligand_link($s)
    {
        // http://www.pdb.org/pdb/images/UR3_300.png
        $parts = explode(',',$s);
        $links = '';
        foreach ($parts as $part) {
            $part = trim($part);
            $links .= "<a href='http://www.rcsb.org/pdb/ligand/ligandsummary.do?hetId={$part}' target='_blank'>$part</a> ";
        }
        return $links;
    }

    function get_loops($type,$motif_type,$num,$offset)
    {
        $verbose = $type;
        $type = array_search($type, $this->qa_status);
        $query = $this->db->table('loop_qa AS qa')
                ->select('qa.loop_id, qa.modifications, qa.nt_signature, qa.complementary, li.seq')
                 ->join('loop_info AS li','qa.loop_id = li.loop_id')
                 ->where('status',$type)
                 ->where('type',$motif_type)
                 ->orderby('li.loop_id')
                 ->limit($num,$offset);
        $query = $query->get()->getResult();

        $table = new \CodeIgniter\View\Table();

        $i = 1;
        foreach ($query as $row) {
            if ($verbose == 'modified') {
                $table->setHeading('#','id','PDB','Modifications');
                $info = $this->make_ligand_link($row->modifications);
            } elseif ($verbose == 'complementary') {
                $table->setHeading('#','id','PDB','Info');
                $info = $row->complementary;
            } elseif ($verbose != 'valid') {
                $table->setHeading('#','id','PDB','Info');
                $info = $row->nt_signature;
            } elseif ($verbose == 'valid') {
                $table->setHeading('#','id','PDB','Info');
                $info = $row->seq;
            } else {
                $table->setHeading('#','id','PDB','Info');
                $info = '';
            }

            $table->add_row($offset + $i,
                    $this->make_radio_button($row->loop_id),
                    '<a class="pdb">' . substr($row->loop_id,3,4) . '</a>',
                    $info);

            $i++;
        }

        if ($query->getNumRows() == 0) {
            $data['table'] = "No $type $motif_type loops were found";
        } else {
            $tmpl = array( 'table_open'  => "<table class='condensed-table zebra-striped bordered-table'>" );
            $table->setTemplate($tmpl);
        }

        return $table->generate();
    }

    function make_radio_button($id) {
        $loop_link = anchor_popup("loops/view/$id", '&#10140;');
        return array('data' => "<label><input type='radio' class='jmolInline' data-coord='{$id}'
                id={$id} data-quality='{$id}' name='l'><span>{$id}</span>
                <span class='loop_link'>{$loop_link}</span></label>", 'class' => 'loop');
    }

    function get_loops_count($type,$motif_type)
    {
        $type = array_search($type, $this->qa_status);
        $builder = $this->db->table('loop_qa')
                    ->where('status', $type)
                    ->like('loop_id', $motif_type, 'after');

        return $builder->countAllResults();
    }

    function initialize_sfdata()
    {
        $this->q   = $this->query_dcc();
        $this->avg = $this->get_averages();
        $this->low_is_good = array('mapman_Biso_mean','mapman_real_space_R','sfcheck_B_iso_main_chain',
        'sfcheck_B_iso_side_chain','sfcheck_connect','sfcheck_density_index_side_chain','sfcheck_density_index_main_chain',
        'sfcheck_real_space_R','sfcheck_real_space_R_side_chain','sfcheck_shift','sfcheck_shift_side_chain');

        $this->high_is_good = array('mapman_correlation','mapman_occupancy_mean','sfcheck_correlation',
        'sfcheck_correlation_side_chain');
    }

    function get_graphs()
    {
        $url = 'http://rna.bgsu.edu/img/MotifAtlas/dcc_loops';
        if ($handle = opendir('/Servers/rna.bgsu.edu/img/MotifAtlas/dcc_loops')) {
            /* This is the correct way to loop over the directory. */
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != ".DS_Store") {
                    $graphs[] = $entry;
                }
            }
            closedir($handle);
        }
        $text = '';
        foreach ($graphs as $graph) {
            $text .= <<<EOT
              <li>
                <a href="{$url}/{$graph}" rel='g'>
                  <img class="thumbnail span4" src="$url/{$graph}" alt="">
                  $graph
                </a>
              </li>
EOT;
        }
        return $text;
    }

    function query_dcc()
    {
        $query = $this->db->table('ml_loop_positions')
                 ->select()
                 ->join('__dcc_residues','nt_id = __dcc_residues.dcc_residues_id')
                 ->join('loop_info','loop_id=loop_info.loop_id')
                 ->join('ml_loops AS ML','loop_id=ML.loop_id','left')
                 ->where('ml_loop_positions.release_id','0.5')
                 ->where('ML.ml_release_id','0.5')
                 ->groupby('loop_id') // NB! comment out or leave in?
                 ->orderby('ML.motif_id','asc')
                 ->orderby('loop_id','asc');
        $query = $query->get()->getResult();

        return $query;
    }

    function get_heading()
    {
        $heading = array('#','loop id','nt id','motif');
        $i = 1;
        foreach ($this->avg as $key => $value) {
            if (preg_match('/mapman|sfcheck/',$key)) {
                $heading[] = "<a href='#' class='twipsy' title='{$key}. Avg {$value}'>$i</a>";
                $i++;
            }
        }
        return $heading;
    }


    function get_averages()
    {
        $cum = array();
        $i = 0;
        // q variable is the result of a query, saved
        foreach ($this->q as $row) {

            if ($i == 0) {
                $fields = get_object_vars($row);
                foreach ($fields as $key => $value) {
                    if (!array_key_exists($key,$cum)) {
                        $cum[$key] = 0;
                    }
                }
                $i = 1;
            }

            foreach ($fields as $key => $value) {
                $cum[$key] += $value;
            }
        }

        $total = count($this->q);
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

            if (array_key_exists($key,$this->low_is_good)) {
                if ($value > $this->avg[$key]) {
                    $extreme_case = true;
                    break;
                }
            } else {
                if ($value < $this->avg[$key]) {
                    $extreme_case = true;
                    break;
                }

            }


            // high b values are bad, so we highlight them
//             $pos = strpos($key,'iso');
//             if ( $pos != false and $value > $this->avg[$key] ) {
//                 $extreme_case = true;
//                 break;
//             }
//             if ( array_key_exists($key,$this->avg) and $value < $this->avg[$key] ) {
//                 $extreme_case = true;
//                 break;
//             }
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
        if (in_array($key,$this->low_is_good)) {
            if ($value > $this->avg[$key]) {
                return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            } else {
                return "<span class='label success twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            }
        } else {
            if ($value < $this->avg[$key]) {
                return "<span class='label important twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            } else {
                return "<span class='label success twipsy' title='{$key}. Avg {$this->avg[$key]}'>$value</label>";
            }
        }
    }

    function make_checkbox($loop,$nts)
    {
        return "<label class='narrow'><input type='radio' name='loops' id='{$loop}' class='jmolInline' data-nt='$nts'><span>$loop</span></label>";
    }

    function get_sfdata_table()
    {
        $i = 1;
        foreach ($this->q as $row) {
            $row_array = $this->analyze_nucleotide($row,$i);
            if (count($row_array) > 0) {
                $table[] = $row_array;
                $i++;
            }
        }
        return $table;
    }

    function get_fields_array()
    {
        return array('sfcheck_correlation','sfcheck_correlation_side_chain',
                     'sfcheck_real_space_R','sfcheck_real_space_R_side_chain',
                     'sfcheck_connect',
                     'sfcheck_shift','sfcheck_shift_side_chain',
                     'sfcheck_density_index_main_chain',
                     'sfcheck_density_index_side_chain',
                     'sfcheck_B_iso_main_chain','sfcheck_B_iso_side_chain',
                     'mapman_correlation','mapman_real_space_R',
                     'mapman_Biso_mean','mapman_occupancy_mean');
    }

    function get_min($pdb)
    {
        $query = $this->db->table('__dcc_residues')
                 ->selectMin('sfcheck_correlation')
                 ->selectMin('sfcheck_correlation_side_chain')
                 ->selectMin('sfcheck_real_space_R')
                 ->selectMin('sfcheck_real_space_R_side_chain')
                 ->selectMin('sfcheck_connect')
                 ->selectMin('sfcheck_shift')
                 ->selectMin('sfcheck_shift_side_chain')
                 ->selectMin('sfcheck_density_index_main_chain')
                 ->selectMin('sfcheck_density_index_side_chain')
                 ->selectMin('sfcheck_B_iso_main_chain')
                 ->selectMin('sfcheck_B_iso_side_chain')
                 ->selectMin('mapman_correlation')
                 ->selectMin('mapman_real_space_R')
                 ->selectMin('mapman_Biso_mean')
                 ->selectMin('mapman_occupancy_mean')
                 ->like('dcc_residues_id',strtoupper($pdb),'after')
                 ->get()
                 ->getResult();

        return $query[0];
    }

    function get_max($pdb)
    {
        $query = $this->db->table('__dcc_residues')
                 ->selectMax('sfcheck_correlation')
                 ->selectMax('sfcheck_correlation_side_chain')
                 ->selectMax('sfcheck_real_space_R')
                 ->selectMax('sfcheck_real_space_R_side_chain')
                 ->selectMax('sfcheck_connect')
                 ->selectMax('sfcheck_shift')
                 ->selectMax('sfcheck_shift_side_chain')
                 ->selectMax('sfcheck_density_index_main_chain')
                 ->selectMax('sfcheck_density_index_side_chain')
                 ->selectMax('sfcheck_B_iso_main_chain')
                 ->selectMax('sfcheck_B_iso_side_chain')
                 ->selectMax('mapman_correlation')
                 ->selectMax('mapman_real_space_R')
                 ->selectMax('mapman_Biso_mean')
                 ->selectMax('mapman_occupancy_mean')
                 ->like('dcc_residues_id',strtoupper($pdb),'after')
                 ->get()
                 ->getResult();

        return $query[0];
    }

    function get_dcc_pdbs()
    {
        $query = $this->db->select('DISTINCT(substr(dcc_residues_id,1,4)) as pdb FROM __dcc_residues;',false);
        $query = $query->get()->getResult();
        foreach($query as $row) {
            $result[] = anchor(base_url(array('loops','sfjmol',$row->pdb)),$row->pdb);
        }
        return $result;
    }

}

/* End of file loops_model.php */
/* Location: ./application/model/loops_model.php */
