<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */

$headers_cif = array (

    'data_view',
    '#', 
    'loop_',
    '_atom_site.group_PDB', 
    '_atom_site.id', 
    '_atom_site.type_symbol', 
    '_atom_site.label_atom_id', 
    '_atom_site.label_alt_id', 
    '_atom_site.label_comp_id', 
    '_atom_site.label_asym_id', 
    '_atom_site.label_entity_id', 
    '_atom_site.label_seq_id', 
    '_atom_site.pdbx_PDB_ins_code', 
    '_atom_site.Cartn_x', 
    '_atom_site.Cartn_y', 
    '_atom_site.Cartn_z', 
    '_atom_site.occupancy', 
    '_atom_site.B_iso_or_equiv', 
    '_atom_site.Cartn_x_esd', 
    '_atom_site.Cartn_y_esd', 
    '_atom_site.Cartn_z_esd', 
    '_atom_site.occupancy_esd', 
    '_atom_site.B_iso_or_equiv_esd', 
    '_atom_site.pdbx_formal_charge', 
    '_atom_site.auth_seq_id', 
    '_atom_site.auth_comp_id', 
    '_atom_site.auth_asym_id', 
    '_atom_site.auth_atom_id', 
    '_atom_site.pdbx_PDB_model_num' 

);

$footer_cif = array('#');