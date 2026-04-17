<?php

use App\Controllers\search;
$routes->get('search', [search::class, 'index']);

use App\Controllers\nrlist;
// list of release numbers, dates, number of IFEs
$routes->get('nrlist', [nrlist::class, 'index']);
$routes->addRedirect('nrlist/rna', 'nrlist');
$routes->get('nrlist/dna', [nrlist::class, 'dna']);
// representative set pages that list all members of equivalence classes
// example https://rna.bgsu.edu/rna3dhub/nrlist/release/rna/3.387/3.5A
$routes->get('nrlist/release/(:segment)/(:segment)/(:segment)', [nrlist::class, 'release']);
$routes->get('nrlist/release/(:segment)/(:segment)', [nrlist::class, 'release']);
$routes->get('nrlist/release/(:segment)', [nrlist::class, 'release']);
// representative set downloads, including tsv/full and other formats
$routes->get('nrlist/download/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', [nrlist::class, 'download']);
$routes->get('nrlist/download/(:segment)/(:segment)/(:segment)/(:segment)', [nrlist::class, 'download']);
$routes->get('nrlist/download/(:segment)/(:segment)/(:segment)', [nrlist::class, 'download']);
$routes->get('nrlist/download/(:segment)/(:segment)', [nrlist::class, 'download']);
$routes->get('nrlist/download/(:segment)', [nrlist::class, 'download']);
// non-redundant lists by rfam clan and maybe other techniques
// example https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/3.387/3.5A/clan/4      display on the screen
// example https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/3.387/3.5A/clan/4/tsv  download
// example https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/3.387/3.5A/clan/4/csv  download
// example https://rna.bgsu.edu/rna3dhub/nrlist/nonredundant/rna/3.387/3.5A/clan/4/json  download
$routes->get('nrlist/nonredundant/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', [nrlist::class, 'nonredundant']);
$routes->get('nrlist/nonredundant/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', [nrlist::class, 'nonredundant']);
$routes->get('nrlist/nonredundant/(:segment)/(:segment)/(:segment)/(:segment)', [nrlist::class, 'nonredundant']);
$routes->get('nrlist/nonredundant/(:segment)/(:segment)/(:segment)', [nrlist::class, 'nonredundant']);
$routes->get('nrlist/nonredundant/(:segment)/(:segment)', [nrlist::class, 'nonredundant']);
$routes->get('nrlist/nonredundant/(:segment)', [nrlist::class, 'nonredundant']);
$routes->get('nrlist/nonredundant', [nrlist::class, 'nonredundant']);
// equivalence class views
$routes->get('nrlist/view/(:segment)', [nrlist::class, 'view']);
$routes->get('nrlist/view_debug/(:segment)', [nrlist::class, 'view_debug']);
// older and might not work so well
$routes->get('nrlist/compare_releases', [nrlist::class, 'compare_releases']);
$routes->get('nrlist/compare/(:segment)', [nrlist::class, 'compare']);
$routes->post('nrlist/compare/', [nrlist::class, 'compare']);
$routes->get('nrlist/release_history', [nrlist::class, 'release_history']);

use App\Controllers\display3D;
$routes->get('display3D/unitid/(:segment)', [display3D::class, 'unitid']);
$routes->get('display3D/chain/(:segment)', [display3D::class, 'chain']);
$routes->get('display3D/multiple/(:segment)', [display3D::class, 'multiple']);
$routes->get('display3D/pdb_chain_range/(:segment)', [display3D::class, 'pdb_chain_range']);

use App\Controllers\rest;
$routes->match(['GET','POST'],'rest/getAssemblies', [rest::class, 'getAssemblies']);
$routes->match(['GET','POST'],'rest/getCenters', [rest::class, 'getCenters']);
$routes->match(['GET','POST'],'rest/getCoordinates', [rest::class, 'getCoordinates']);
$routes->match(['GET','POST'],'rest/getCoordinatesMotifAtlas', [rest::class, 'getCoordinatesMotifAtlas']);
$routes->match(['GET','POST'],'rest/getNeighbors', [rest::class, 'getNeighbors']);
$routes->match(['GET','POST'],'rest/getRSR', [rest::class, 'getRSR']);
$routes->match(['GET','POST'],'rest/getRSRZ', [rest::class, 'getRSRZ']);
$routes->get('rest/SeqtoUnitMapping', [rest::class, 'SeqtoUnitMapping']);
$routes->match(['GET','POST'],'rest/getPdbInfo', [rest::class, 'getPdbInfo']);
$routes->match(['GET','POST'],'rest/getChainInfo', [rest::class, 'getChainInfo']);
$routes->match(['GET','POST'],'rest/getSequenceBasePairs', [rest::class, 'getSequenceBasePairs']);
$routes->match(['GET','POST'],'rest/getChainSequenceBasePairs', [rest::class, 'getChainSequenceBasePairs']);

use App\Controllers\home;
$routes->get('/', [home::class, 'index']);

use App\Controllers\pages;
$routes->get('pages/(:segment)', [pages::class,'page']);

use App\Controllers\motifs;
$routes->get('motifs', [motifs::class, 'index']);
// $routes->get('motifs/index/(:segment)', [motifs::class, 'index']);
$routes->get('motifs/release/(:segment)/(:segment)/(:segment)', [motifs::class, 'release']);
$routes->get('motifs/release/(:segment)/(:segment)', [motifs::class, 'release']);
$routes->get('motifs/release_history', [motifs::class, 'release_history']);
$routes->get('motifs/compare_releases', [motifs::class, 'compare_releases']);
$routes->get('motifs/polymorphs/(:segment)/(:segment)', [motifs::class, 'polymorphs']);

use App\Controllers\motif;
$routes->get('motif/view/(:segment)', [motif::class, 'view']);
$routes->get('motif/view/(:segment)/(:segment)', [motif::class, 'view']);

use App\Controllers\unitid;
$routes->get('unitid', [unitid::class, 'index']);
$routes->get('unitid/describe', [unitid::class, 'index']);
$routes->get('unitid/describe/(:segment)', [unitid::class, 'describe']);

use App\Controllers\loops;
$routes->get('loops/view/(:segment)', [loops::class, 'view']);
$routes->get('loops/download/(:segment)', [loops::class, 'download']);
$routes->get('loops/download_with_breaks/(:segment)', [loops::class, 'download_with_breaks']);

use App\Controllers\pdb;
$routes->get('pdb', [pdb::class, 'index']);
$routes->get('pdb/data', [pdb::class, 'data']);
$routes->get('pdb/(:segment)', [pdb::class, 'general_info']);
$routes->get('pdb/(:segment)/motifs', [pdb::class, 'motifs']);
$routes->get('pdb/(:segment)/interactions/(:segment)/(:segment)/(:segment)', [pdb::class, 'interactions']);
$routes->get('pdb/(:segment)/interactions/(:segment)/(:segment)', [pdb::class, 'interactions']);
$routes->get('pdb/(:segment)/interactions/(:segment)', [pdb::class, 'interactions']);
$routes->get('pdb/(:segment)/interactions', [pdb::class, 'interactions']);
$routes->get('pdb/(:segment)/2d', [pdb::class, 'two_d']);
// get('pdb/(:alphanum)/interactions/fr3d/(.+)', 'Pdb::interactions/$1/fr3d/$2');
// https://rnanew.bgsu.edu/rna3dhub/pdb/8GLP/interactions/fr3d/basepairs


// From https://www.codeigniter.com/user_guide/tutorial/news_section.html#display-the-news
// use App\Controllers\News; // Add this line
// $routes->get('news', [News::class, 'index']);           // Add this line
// $routes->get('news/(:segment)', [News::class, 'show']); // Add this line

// From https://www.codeigniter.com/user_guide/tutorial/static_pages.html
// use App\Controllers\Pages;
// $routes->get('pages', [Pages::class, 'index']);
// $routes->get('(:segment)', [Pages::class, 'view']);

