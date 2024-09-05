<?php

use App\Controllers\display3D;
$routes->get('display3D/unitid/(:segment)', [display3D::class, 'unitid']);

use App\Controllers\rest;
$routes->get('rest/getCoordinates', [rest::class, 'getCoordinates'], ['filter' => 'disableDebug']);
$routes->get('rest/getCoordinatesMotifAtlas', [rest::class, 'getCoordinatesMotifAtlas'], ['filter' => 'disableDebug']);
$routes->get('rest/getRSR', [rest::class, 'getRSR'], ['filter' => 'disableDebug']);
$routes->get('rest/getRSRZ', [rest::class, 'getRSRZ'], ['filter' => 'disableDebug']);
$routes->get('rest/SeqtoUnitMapping', [rest::class, 'SeqtoUnitMapping'], ['filter' => 'disableDebug']);

use App\Controllers\home;
$routes->get('/', [home::class, 'index']);

use App\Controllers\motifs;
$routes->get('motifs', [motifs::class, 'index']);
// $routes->get('motifs/index/(:segment)', [motifs::class, 'index']);
$routes->get('motifs/release/(:segment)/(:segment)', [motifs::class, 'release']);

use App\Controllers\motif;
$routes->get('motif/view/(:segment)', [motif::class, 'view2023']);

use App\Controllers\unitid;
$routes->get('unitid', [unitid::class, 'index']);
$routes->get('unitid/describe/(:segment)', [unitid::class, 'describe']);

use App\Controllers\loops;
$routes->get('loops/view/(:segment)', [loops::class, 'view']);

use App\Controllers\pdb;
$routes->get('pdb', [pdb::class, 'index']);
$routes->get('pdb/(:segment)', [pdb::class, 'general_info']);
$routes->get('pdb/(:segment)/motifs', [pdb::class, 'motifs']);
$routes->get('pdb/(:segment)/interactions/(fr3d)/(:segment)', [pdb::class, 'interactions']);
$routes->get('pdb/(:segment)/2d', [pdb::class, 'two_d']);
// get('pdb/(:alphanum)/interactions/fr3d/(.+)', 'Pdb::interactions/$1/fr3d/$2');
// https://rnanew.bgsu.edu/rna3dhub/pdb/8GLP/interactions/fr3d/basepairs

use App\Controllers\search;
$routes->get('search', [search::class, 'index']);


// From https://www.codeigniter.com/user_guide/tutorial/news_section.html#display-the-news
// use App\Controllers\News; // Add this line
// $routes->get('news', [News::class, 'index']);           // Add this line
// $routes->get('news/(:segment)', [News::class, 'show']); // Add this line

// From https://www.codeigniter.com/user_guide/tutorial/static_pages.html
// use App\Controllers\Pages;
// $routes->get('pages', [Pages::class, 'index']);
// $routes->get('(:segment)', [Pages::class, 'view']);

