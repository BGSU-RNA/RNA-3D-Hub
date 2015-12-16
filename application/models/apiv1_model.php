<?php
class Apiv1_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        parent::__construct();
    }

    function validate_nts($pdb_id, $chain, $nts)
    {
        $result = array('valid' => FALSE,
                        'nts' => array(), // expanded blocks
                        'error_msg' => '');

        if ( is_null($pdb_id) ) {
            $result['error_msg'] = 'Please specify pdb id';
            return $result;
        }

        if ( is_null($nts) ) {
            $result['error_msg'] = 'Please specify nucleotides';
            return $result;
        }

        $units = $this->_explode_nt_list($nts);

        $data = $this->_get_singletons_and_ranges($units);
        if ( $data['error_msg'] !== '' ) {
            $result['error_msg'] = $data['error_msg'];
            return $result;
        }

        $error_msg = $this->_check_singletons($data['singletons'], $pdb_id, $chain);
        if ( $error_msg !== '' ) {
            $result['error_msg'] = $error_msg;
            return $result;
        }

        $ranges = $this->_check_ranges($data['ranges'], $pdb_id, $chain);
        if (is_string($ranges)) { // error message
            $result['error_msg'] = $ranges;
            return $result;
        } else { // nucleotides
            $result['nts'] = array_merge($result['nts'], $ranges);
        }

        $result['nts'] = array_unique(array_merge($data['singletons'], $result['nts']));
        $result['valid'] = TRUE;
        return $result;
    }

    function validate_single_nucleotide($nt, $pdb_id, $chain)
    {
        // best guess based on the input data
        $rna_nts = array('A', 'C', 'G', 'U');
        $this->db->select('pdb_coordinates_id')
                 ->from('__pdb_coordinates')
                 ->where('pdb_id', $pdb_id)
                 ->where('model', 1) // look only in the first model
                 ->where('number', intval(preg_replace('/\D/', '', $nt)))
                 ->where('ins_code', preg_replace('/\d/', '', $nt))
                 ->where_in('unit', $rna_nts)
                 ->limit(1);

        if ( $chain ) {
            $this->db->where('chain', $chain);
        }

        $query = $this->db->get();

        if ( $query->num_rows() == 0 ) {
            return FALSE;
        } else {
            return $query->row();
        }
    }

    function _explode_nt_list($nts)
    {
        $nts = preg_replace('/\s+/', ',', $nts); // remove all whitespace
        $nts = preg_replace('/,+/',  ',', $nts); // remove consecutive commas

        return explode(',', $nts);
    }

    function _get_singletons_and_ranges($units)
    {
        $result['ranges'] = array();
        $result['singletons'] = array();
        $result['error_msg'] = '';

        $problems = array();

        // negative nucleotides and insertion codes are OK
        $regexp['nt']    = '/^-*\d+\w*$/';
        $regexp['range'] = '/^-*\d+\w*:-*\d+\w*$/';

        foreach($units as $unit) {
            if (preg_match($regexp['nt'], $unit)) {
                $result['singletons'][] = $unit;
            } elseif (preg_match($regexp['range'], $unit)) {
                $result['ranges'][] = $unit;
            } else {
                $problems[] = $unit;
            }
        }

        if ( count($problems) == 1 ) {
            $result['error_msg'] = 'Please check this fragment: ' . $problems[0];
        } elseif ( count($problems) > 1 ) {
            $result['error_msg'] = 'Please check these fragments: ' . implode(', ', $problems);
        }

        return $result;
    }

    function _check_singletons($nts, $pdb_id, $chain)
    {
        $error_msg = '';
        $errors = array();

        foreach($nts as $nt) {
            $data = $this->validate_single_nucleotide($nt, $pdb_id, $chain);
            if ( $data === FALSE ) {
                $errors[] = $nt;
            }
        }

        if ( count($errors) == 1 ) {
            $error_msg = 'No nucleotide ' . $errors[0];
        } elseif ( count($errors) > 1 ) {
            $error_msg = 'No nucleotides ' . implode(', ', $errors);
        }

//             $problems .= " in chain $ch in $pdb";

        return $error_msg;
    }

    function _check_ranges($ranges, $pdb_id, $chain)
    {
        $expanded = array();

        foreach($ranges as $range) {
            $result = $this->_expand_range($range, $pdb_id, $chain);
            if (is_string($result)) { // error message
                return $result;
            } elseif (is_array($result)) { // contains nucleotides
                $expanded = array_merge($expanded, $result);
            }
        }

        return $expanded;
    }

    function _expand_range($range, $pdb_id, $chain)
    {
        // 100:120
        $flanks = explode(':', $range);

        // validate left flanking value
        $data = $this->validate_single_nucleotide($flanks[0], $pdb_id, $chain);
        if ( $data === FALSE ) {
            return 'Nucleotide ' . $flanks[0] . ' not found';
        } else {
            $start = $data->index;
        }

        // validate right flanking value
        $data = $this->validate_single_nucleotide($flanks[1], $pdb_id, $chain);
        if ( $data === FALSE ) {
            return 'Nucleotide ' . $flanks[1] . ' not found';
        } else {
            $stop = $data->index;
        }

        $block = array($start, $stop);
        sort($block);

        $expanded = array();
        $rna_nts = array('A', 'C', 'G', 'U');

        for ($i = $block[0]; $i <= $block[1]; $i++) {
            $this->db->select('number, ins_code')
                     ->from('__pdb_coordinates')
                     ->where('pdb_id', $pdb_id)
                     ->where('index', $i)
                     ->where_in('unit', $rna_nts) // separate index for nucleotides and heteroatoms
                     ->limit(1);
            $query = $this->db->get();

            $result = $query->row();
            $expanded[] = $result->number . $result->ins_code;
        }

        return $expanded;
    }

}

/* End of file apiv1_model.php */
/* Location: ./application/model/apiv1_model.php */