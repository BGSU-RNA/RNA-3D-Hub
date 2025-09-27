<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class BGSUConfig extends BaseConfig
{
    public $home_url = 'https://rna.bgsu.edu';
    public $base_url = 'https://rna.bgsu.edu/rna3dhub';         // deprecated
    public $hub_url  = 'https://rna.bgsu.edu/rna3dhub';         // use this in Models and Controllers, I guess
    public $img_url  = 'https://rna.bgsu.edu/img/MotifAtlas';

    public $bgsu_url = 'https://www.bgsu.edu/research/rna';
    public $fr3d_url = 'https://www.bgsu.edu/research/rna/software/fr3d.html';
}
