<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

use App\Models\ajax_model;  // use lowercase like the filename
use App\Models\motifs_model;

class Rest extends ResourceController {

    public $messages;
    public $exploded_nts;
    public $Ajax_model;
    public $Motifs_model;

    public function __construct()
    {
        $this->messages = array(
                                'invalid'  => 'Invalid input',
                                'notfound' => 'Not found',
                                'error'    => 'Internal error'
                                );
        // store exploded nts
        // $this->exploded_nts = array();

        $this->Ajax_model = new ajax_model();
        $this->Motifs_model = new motifs_model();

        // parent::__construct();
    }

    public function index()
    {
        echo 'Instructions page under construction';
    }

    public function getCoordinates() {
        // unit ids
        // https://rna.bgsu.edu/rna3dhub/rest/getCoordinates?coord=2QBG|1|A|G|69,2QBG|1|A|G|107
        // https://rna.bgsu.edu/rna3dhub/rest/getCoordinates?coord=3LA5_AU_1_A_34_U_,3LA5_AU_1_A_65_A_
        // https://rna.bgsu.edu/rna3dhub/rest/getCoordinates?coord=3RG5_AU_1_A_47_A_I,3RG5_AU_1_A_47_C_J,3RG5_AU_1_A_47_A_K,3RG5_AU_1_A_47_U_,3RG5_AU_1_A_47_G_A,3RG5_AU_1_A_47_U_B
        // chain or chains
        // https://rna.bgsu.edu/rna3dhub/rest/getCoordinates?coord=7M2V|1|T
        // https://rna.bgsu.edu/rna3dhub/rest/getCoordinates?coord=7M2V|1|T,7M2V|1|k

        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id
        // and loop pairs (returns the first loop of the two)
        // Also allow pdb_chain_range1_range2 ... with _ or : or other separators

        // search POST, then GET
        // $query = $this->request->getGet('coord');
        // $query = $this->request->getGet('coord');
        $coord = $this->request->getVar('coord');

        $query_type = $this->_parseInput($coord);

        if ( $query_type ) {
            $data['csv'] = $this->_database_lookup($coord, $query_type);

            // bypass the view system to try to avoid debugging comments
            $response = service('response');
            $response->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                     ->setBody($data['csv']);
            return $response;

        } else {
            echo $this->messages['invalid'];
        }
    }

    public function getNeighbors() {
        // unit ids and center to center distance
        // https://rna.bgsu.edu/rna3dhub/rest/getNeighbors?coord=2QBG|1|A|G|69,2QBG|1|A|G|107,dist=8

        // search POST, then GET
        // $query = $this->request->getGet('coord');
        // $query = $this->request->getGet('coord');
        $coord = $this->request->getVar('coord');
        $dist  = $this->request->getVar('dist');

        $query_type = $this->_parseInput($coord);

        if ( $query_type == 'unit_id' ) {
            $model = model(Ajax_model::class);
            $unit_ids = explode(",",$coord);
            $data = $model->get_neighboring_units($unit_ids,$dist);

            $text = implode("\n",$data);

            // bypass the view system to try to avoid debugging comments
            $response = service('response');
            $response->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                     ->setBody($text);
            return $response;

        } else {
            echo $this->messages['invalid'];
        }
    }

    public function getCenters()
    {
        // unit ids
        // https://rna.bgsu.edu/rna3dhub/rest/getCenters?coord=2QBG|1|A|G|69,2QBG|1|A|G|107
        // chain or chains
        // https://rna.bgsu.edu/rna3dhub/rest/getCenters?coord=7M2V|1|T
        // https://rna.bgsu.edu/rna3dhub/rest/getCenters?coord=7M2V|1|T,7M2V|1|k

        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id
        // and loop pairs (returns the first loop of the two)
        // Also allow pdb_chain_range1_range2 ... with _ or : or other separators

        // search POST, then GET
        // $query = $this->request->getGet('coord');
        // $query = $this->request->getGet('coord');
        $coord = $this->request->getVar('coord');

        $query_type = $this->_parseInput($coord);

        if ( $query_type ) {
            $data['csv'] = $this->_database_lookup_centers($coord, $query_type);

            // bypass the view system to try to avoid debugging comments
            $response = service('response');
            $response->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                     ->setBody($data['csv']);
            return $response;

        } else {
            echo $this->messages['invalid'];
        }
    }

    public function getCoordinatesMotifAtlas()
    {
        // search POST, then GET
        // this must get loop id, motif id, and motif release
        $query = $this->request->getGet('coord_ma');

        // if $query is empty, try another thing
        if ( ! $query ) {
            return $this->getCoordinates();
        }

        $model = model(Ajax_model::class);
        $data['csv'] = $model->get_motif_coordinates($query);

        // bypass the view system to avoid debugging comments
        $response = service('response');
        $response->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                 ->setBody($data['csv']);
        return $response;
    }

    public function getCoordinatesRelative()
    {
        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id
        // and loop pairs (returns the first loop of the two)

        // search POST, then GET
        $query = $this->request->getGet('core');

        // $this->output->enable_profiler(TRUE);

        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $data['csv'] = $this->_database_lookup_relative($query, $query_type);
            $response = service('response');
            $response->setHeader('Access-Control-Allow-Origin', '*')
                        ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                        ->setContentType('text/csv');
            $response->setBody($data['csv']);
            return $response;
        } else {
            echo $this->messages['invalid'];
        }
    }

    public function getPdbInfo()
    {
        // https://rna.bgsu.edu/rna3dhub/rest/getPdbInfo?pdb=1FJG&cla=1&res=1
        // https://rna.bgsu.edu/rna3dhub/rest/getPdbInfo?pdb=5L4O|1|A
        // https://rna.bgsu.edu/rna3dhub/rest/getPdbInfo?pdb=2PYO&cla=1&res=1
        // https://rna.bgsu.edu/rna3dhub/rest/getPdbInfo?pdb=2PYO
        $pdb = $this->request->getVar('pdb');
        $cla = $this->request->getVar('cla');
        $res = $this->request->getVar('res');
        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                 ->setBody($this->Ajax_model->get_pdb_info($pdb,$cla,$res));
        return $response;
    }

    public function getAssemblies()
    {
        // https://rna.bgsu.edu/rna3dhub/rest/getAssemblies?pdb=1FJG
        $pdb = $this->request->getVar('pdb');

        $model = model(Ajax_model::class);

        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader("Access-Control-Expose-Headers", "Access-Control-Allow-Origin");
        $data['json'] = $model->get_assembly_info($pdb);
        return view('json_view', $data);
    }

    public function getChainInfo()
    {
        // set a variable to indicate skipping the debug toolbar
        // $config = config('Toolbar');
        // $config->collectors = [];
        // service('toolbar')->setConfig($config); // Disabling by setting an empty collectors list

        // $this->app->setEnvironment('production');
        // service('toolbar')->disable();

        // $toolbar = Services::toolbar();
        // $toolbar->setEnabled(false); // Disable the toolbar

        // https://rna.bgsu.edu/rna3dhub/rest/getChainInfo?pdb=7K00
        $pdb = $this->request->getVar('pdb');
        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                 ->setBody($this->Ajax_model->get_chain_info($pdb));
        return $response;
    }


    public function getSequenceBasePairs()
    // https://rna.bgsu.edu/rna3dhub/rest/getSequenceBasePairs?pdb_id=4v9d&chain=CA
    // https://rna.bgsu.edu/rna3dhub/rest/getSequenceBasePairs?pdb_id=4v9d&chain=CA&only_nested=True
    {

        $pdb = $this->request->getVar('pdb_id');
        $chain = $this->request->getVar('chain');
        $nested = $this->request->getVar('only_nested');

        // $this->output->enable_profiler(TRUE);

        $model = model(Ajax_model::class);

        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader("Access-Control-Expose-Headers", "Access-Control-Allow-Origin");
        $data['json'] = $model->get_sequence_basepairs($pdb, $chain, $nested);
        return view('json_view', $data);
    }


    public function getChainSequenceBasePairs()
    {
        // https://rna.bgsu.edu/rna3dhub/rest/getChainSequenceBasePairs?pdb_id=1FJG&chain=A&only_nested=True
        // For one RNA or DNA chain, return the Leontis-Westhof basepairs
        // When only_nested is 'True', return those with crossing number 0
        // When only_nested is 'False', return all

        $pdb = $this->request->getVar('pdb_id');
        $chain = $this->request->getVar('chain');
        $nested = $this->request->getVar('only_nested');

        $data['json'] = $this->Ajax_model->get_chain_sequence_basepairs($pdb, $chain, $nested);

        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Expose-Headers', 'Access-Control-Allow-Origin')
                 ->setContentType('application/json')
                 ->setBody($data['json']);
        return $response;
    }


    public function SeqtoUnitMapping()
    {

        // search POST, then GET
        $query = $this->request->getGet('ife');

        // $this->output->enable_profiler(TRUE);

        if ($query) {
            $model = model(Ajax_model::class);

            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader("Access-Control-Expose-Headers", "Access-Control-Allow-Origin");
            $data['json'] = $model->get_seq_unit_mapping($query);
            return view('json_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }


    public function getRSR()
    {
        // should be able to accept loop_id and unit_id

        // search POST, then GET
        $query = $this->request->getGet('quality');

        //$this->output->enable_profiler(TRUE);

        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader("Access-Control-Expose-Headers", "Access-Control-Allow-Origin");
            $data['json'] = $this->_database_lookup_RSR($query, $query_type);
            return view('json_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }

    public function getRSRZ()
    {
        // should be able to accept loop_id and unit_id

        // search POST, then GET
        $query = $this->request->getGet('quality');
        // $query = $this->request->getGet('coord');


        // $this->output->enable_profiler(TRUE);

        $query_type = $this->_parseInput($query);
        // echo $query_type;

        if ( $query_type ) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader("Access-Control-Expose-Headers", "Access-Control-Allow-Origin");
            $data['json'] = $this->_database_lookup_RSRZ($query, $query_type);
            // return view('json_view', $data);
            return view('json_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }

    private function _database_lookup($query, $query_type, $distance=10)
    {
        // Retrieve coordinates from the database.
        // Accept a variety of input types.
        // Return the requested nucleotides in model 1
        // Return neighboring nucleotides in model 2 up to a distance of 10

        $model = model(Ajax_model::class);

        // don't load the database until the input was validated
        switch ($query_type) :
            case 'loop_id':
                return $model->get_loop_coordinates($query,$distance);

            case 'chain_id':
                return $model->get_chain_coordinates($query,$distance);

            case 'loop_pair':
                return $model->get_loop_pair_coordinates($query);

            case 'nt_list':
                $units = explode(',', $query);
                // convert old style units to new style
                // 3LA5_AU_1_A_34_U_,3LA5_AU_1_A_65_A_ becomes 3LA5|1|A|U|34,3LA5|1|A|A|65
                // 3RG5_AU_1_A_47_A_I becomes 3RG5|1|A|A|47
                $new_units = array();
                foreach ($units as $unit) {
                    $fields = explode('_', $unit);
                    if (count($fields) == 7 and $fields[6] == '') {
                        $new_unit = $fields[0] . '|' . $fields[2] . '|' . $fields[3] . '|' . $fields[5] . '|' . $fields[4];
                        $new_units[] = $new_unit;
                    } elseif (count($fields) == 7) {
                        $new_unit = $fields[0] . '|' . $fields[2] . '|' . $fields[3] . '|' . $fields[5] . '|' . $fields[4] . '|||' . $fields[6];
                        $new_units[] = $new_unit;
                    }
                }
                $new_units = implode(',', $new_units);
                return $model->get_unit_and_neighbor_coordinates($new_units,$distance);

            case 'unit_id':
                return $model->get_unit_and_neighbor_coordinates($query,$distance);

            case 'motif_id':
                return $model->get_exemplar_coordinates($query);

            case 'pdb_chain_range':
                return $model->get_pdb_chain_range_coordinates($query,$distance);

            default: return $this->messages['error'];
        endswitch;

    }


    private function _database_lookup_centers($query, $query_type)
    {
        // Retrieve unit centers from the database.
        // Accept a variety of input types.

        $model = model(Ajax_model::class);

        // don't load the database until the input was validated
        switch ($query_type) :
            // case 'loop_id':
            //     return $model->get_loop_coordinates($query,$distance);

            case 'chain_id':
                return $model->get_chain_centers($query);

            // case 'loop_pair':
            //     return $model->get_loop_pair_coordinates($query);

            // case 'nt_list':
            //     $units = explode(',', $query);
            //     // convert old style units to new style
            //     // 3LA5_AU_1_A_34_U_,3LA5_AU_1_A_65_A_ becomes 3LA5|1|A|U|34,3LA5|1|A|A|65
            //     // 3RG5_AU_1_A_47_A_I becomes 3RG5|1|A|A|47
            //     $new_units = array();
            //     foreach ($units as $unit) {
            //         $fields = explode('_', $unit);
            //         if (count($fields) == 7 and $fields[6] == '') {
            //             $new_unit = $fields[0] . '|' . $fields[2] . '|' . $fields[3] . '|' . $fields[5] . '|' . $fields[4];
            //             $new_units[] = $new_unit;
            //         } elseif (count($fields) == 7) {
            //             $new_unit = $fields[0] . '|' . $fields[2] . '|' . $fields[3] . '|' . $fields[5] . '|' . $fields[4] . '|||' . $fields[6];
            //             $new_units[] = $new_unit;
            //         }
            //     }
            //     $new_units = implode(',', $new_units);
            //     return $model->get_unit_and_neighbor_coordinates($new_units,$distance);

            // case 'unit_id':
            //     return $model->get_unit_and_neighbor_coordinates($query,$distance);

            // case 'motif_id':
            //     return $model->get_exemplar_coordinates($query);

            // case 'pdb_chain_range':
            //     return $model->get_pdb_chain_range_coordinates($query,$distance);

            default: return $this->messages['error'];
        endswitch;

    }


    private function _database_lookup_relative($query, $query_type)
    {
        // don't load the database until the input was validated
        $model = model(Ajax_model::class);

        // $this->output->enable_profiler(TRUE);

        switch ($query_type) :
            case 'unit_id':
                return $model->get_coord_relative($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_RSR($query, $query_type)
    {
        // don't load the database until the input was validated
        $model = model(Ajax_model::class);

        // $this->output->enable_profiler(TRUE);

        switch ($query_type) :
            case 'loop_id':
                return $model->get_loop_RSR($query);
            case 'chain_id':
                return $model->get_chain_RSR($query);
            case 'motif_id':
                return $model->get_exemplar_RSR($query);
            case 'unit_id':
                return $model->get_unit_id_RSR($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_RSRZ($query, $query_type)
    {
        // don't load the database until the input was validated
        $model = model(Ajax_model::class);

        // $this->output->enable_profiler(TRUE);

        switch ($query_type) :
            case 'loop_id':
                return $model->get_loop_RSRZ($query);
            case 'chain_id':
                return $model->get_chain_RSRZ($query);
            case 'motif_id':
                return $model->get_exemplar_RSRZ($query);
            case 'unit_id':
                return $model->get_unit_id_RSRZ($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _parseInput($query)
    {
        // if get_post returned FALSE, then
        if ( $query ) {

            if ( $this->_is_loop_id($query) ) {
                return 'loop_id';
            } elseif ( $this->_is_motif_id($query) ) {
                return 'motif_id';
            } elseif ( $this->_is_nt_list($query) ) {
                return 'nt_list';
            } elseif ( $this->_is_loop_pair($query) ) {
                return 'loop_pair';
            } elseif ( $this->_is_short_nt_list($query) ) {
                return 'short_nt_list';
            } elseif ( $this->_is_unit_id($query) ) {
                return 'unit_id';
            } elseif ( $this->_is_chain_id($query) ) {
                return 'chain_id';
            } elseif ( $this->_is_pdb_chain_range($query)) {
                return 'pdb_chain_range';
            } else {
                return FALSE;
            }

        } else {
            return FALSE;
        }
    }

    private function _is_unit_id()
    {
        // 1S72|1|0|U|10, 3BNT|2|A|C|22||||4_665
        foreach ($this->exploded_nts as $nt) {
            $parts = explode('|', $nt);
            $separators = count($parts);
            if ( $separators >= 4 and $separators <= 9 and
                 $parts[1] != 'AU' and $parts[1] != 'BA1' ) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function _is_chain_id($query)
    {
        // 1FJG|1|A
        // 7M2V|1|T,7M2V|1|k
        // 1ABC|1|X||||||4_255
        $is_id = TRUE;
        $query = str_replace("+",",",$query);
        $chains = explode(",", $query);
        foreach ($chains as $chain) {
            $parts = explode('|', $chain);
            if ( count($parts) == 3 ) {
                if ($parts[1] != 'AU' and $parts[1] != 'BA1' ) {
                } else {
                    $is_id = FALSE;
                }
            } elseif ( count($parts) == 9) {
                // chain with symmetry operator
            } else {
                $is_id = FALSE;
            }

        }
        return $is_id;
    }

    private function _is_loop_id($query)
    {
        // IL_1J5E_001
        if ( preg_match('/^(IL|HL|J3|J4|J5|J6|J7|J8|J9|J10|J11|J12|J13|J14|J15)_[0-9A-Z]{4}_\d{3}$/i', $query) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_loop_pair($query)
    {
        // @IL_1J5E_001:IL_1S72_001 or IL_1J5E_001:@IL_1S72_001
        // "@" marks the loop for which the coordinates should be returned
        if ( preg_match('/^@?(IL|HL|J3)_[0-9A-Z]{4}_\d{3}:@?(IL|HL|J3)_[0-9A-Z]{4}_\d{3}$/i', $query) and
             substr_count($query, '@') == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_motif_id($query)
    {
        // IL_12345.89
        if ( preg_match('/^(IL|HL|J3|J4|J5|J6|J7|J8|J9|J10|J11|J12|J13|J14|J15)_\d{5}\.\d+$/i', $query) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_nt_list($query)
    {
        // 1EKA_AU_1_A_1_G_,1EKA_AU_1_A_2_A_
        $this->exploded_nts = explode(',', $query);
        $pattern = '/^[A-Z0-9]{4}_[A-Z0-9]{2,3}_\d+_[A-Z0-9]{1}_-?\d{1,5}_[A-Z0-9]_[A-Z0-9]{0,1}$/i';

        foreach ($this->exploded_nts as $nt) {
            if ( ! preg_match($pattern, $nt) ) {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function _is_pdb_chain_range($query)
    {
        // if $query contains | return false
        if (str_contains($query, '|')) {
            return FALSE;
        }

        // replace - : , ; with _ and then split on _
        $new_query = preg_replace('/[-:;,. ]/', '_', $query);
        $fields = explode('_', $new_query);

        if (count($fields) >= 4) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_short_nt_list($query)
    {
        // 1S72_1_0_1095
        // talk with Blake, implement later
        return FALSE;
    }

    function getMotifFlowJSON($motif_type, $release_id1, $release_id2)
    {
        $model = model(Motifs_model::class);
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader("Access-Control-Expose-Headers", "Access-Control-Allow-Origin");
        echo $model->getSankeyDataJSON($release_id1, $release_id2, $motif_type);
    }

}
