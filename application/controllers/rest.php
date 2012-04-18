<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rest extends MY_Controller {

    public function __construct()
    {
        $this->messages = array(
                                'invalid'  => 'Invalid input',
                                'notfound' => 'Not found',
                                'error'    => 'Internal error'
                                );
        // store exploded nts
        $this->exploded_nts = array();

        parent::__construct();
    }

    public function index()
    {
        echo 'Instructions page under construction';
    }

    public function getCoordinates()
    {
        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id

        // search POST, then GET
        $query = $this->input->get_post('coord');

        $query_type = $this->_parseInput($query);

        if ( $query_type ) {

            echo $this->_database_lookup($query, $query_type);

        } else {
            echo $this->messages['invalid'];
        }

    }

    private function _database_lookup($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

        switch ($query_type) :
            case 'loop_id':
                return $this->Ajax_model->get_loop_coordinates($query);
            case 'motif_id':
                return $this->Ajax_model->get_exemplar_coordinates($query);
            case 'nt_list':
                return $this->Ajax_model->get_coordinates($query);
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
            } elseif ( $this->_is_short_nt_list($query) ) {
                return 'short_nt_list';
            } else {
                return FALSE;
            }

        } else {
            return FALSE;
        }
    }

    private function _is_loop_id($query)
    {
        // IL_1J5E_001
        if ( preg_match('/^(IL|HL|J3)_[0-9A-Z]{4}_\d{3}$/i', $query) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_motif_id($query)
    {
        // IL_12345.89
        if ( preg_match('/^(IL|HL|J3)_\d{5}\.\d+$/i', $query) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_nt_list($query)
    {
        // 1EKA_AU_1_A_1_G_,1EKA_AU_1_A_2_A_
        $this->exploded_nts = explode(',', $query);
        $pattern = '/^[A-Z0-9]{4}_[A-Z0-9]{2,3}_\d+_[A-Z0-9]{1}_\d{1,5}_[A-Z0-9]_[A-Z0-9]{0,1}$/i';

        foreach ($this->exploded_nts as $nt) {
            if ( ! preg_match($pattern, $nt) ) {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function _is_short_nt_list($query)
    {
        // 1S72_1_0_1095
        // talk with Blake, implement later
        return FALSE;
    }

}