<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\unitid_model;

class Unitid extends BaseController {

	public function index()
	{
        return redirect()->to(config('BGSUConfig')->bgsu_url . '/help/rna-3d-hub-help/unit-ids.html');
    }

    public function describe($unit_id)
    {
        $unit_id = urldecode($unit_id);

        $model = model(unitid_model::class);
        $data = $model->get_unit_id_info($unit_id);
        if ( !$data ) {
            throw new PageNotFoundException('Cannot find the unit ID: ' . $unit_id);
        }

        $data['title']   = $unit_id;
        $data['baseurl'] = base_url();
        $data['unit_id'] = $unit_id;
        $data['pageicon'] = base_url() . 'icons/U_icon.png';

        return view('header_view', $data)
            . view('menu_view', $data)
            . view('unit_id_view', $data)
            . view('footer');
    }
}
