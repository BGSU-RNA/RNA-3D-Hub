<?php

use App\Controllers\unitid;

// From https://www.codeigniter.com/user_guide/tutorial/news_section.html#display-the-news
use App\Controllers\News; // Add this line

// From https://www.codeigniter.com/user_guide/tutorial/static_pages.html
use App\Controllers\Pages;

$routes->get('unitid', [unitid::class, 'index']);
$routes->get('unitid/describe/(:segment)', [unitid::class, 'describe']);

$routes->get('news', [News::class, 'index']);           // Add this line
$routes->get('news/(:segment)', [News::class, 'show']); // Add this line

$routes->get('pages', [Pages::class, 'index']);
$routes->get('(:segment)', [Pages::class, 'view']);
