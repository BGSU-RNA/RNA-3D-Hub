<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>jmol/jmol.js"></script>
    <script src="http://rna.bgsu.edu/Motifs/jmolInlineLoader/jmolInlineLoader.js"></script>

    <script src="<?php echo $baseurl;?>js/bootstrap-twipsy.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>js/bootstrap-popover.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>js/bootstrap-dropdown.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>js/bootstrap-tabs.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>js/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>js/dragtable.js" type="text/javascript"></script>

    <script src="<?php echo $baseurl;?>js/MotifAtlas.js" type="text/javascript"></script>
    <script src="<?php echo $baseurl;?>js/rcsbPdbImageLib.min.js" type="text/javascript"> </script>

    <!-- fancybox -->
    <link rel="stylesheet" href="<?php echo $baseurl;?>js/fancybox/jquery.fancybox.css?v=2.0.3" type="text/css" media="screen" />
    <script type="text/javascript" src="<?php echo $baseurl;?>js/fancybox/jquery.fancybox.pack.js?v=2.0.3"></script>

    <!-- cytoscapeweb -->
    <!-- JSON support for IE (needed to use JS API) -->
    <script type="text/javascript" src="<?php echo $baseurl;?>cytoscapeweb/js/min/json2.min.js"></script>
    <!-- Flash embedding utility (needed to embed Cytoscape Web) -->
    <script type="text/javascript" src="<?php echo $baseurl;?>cytoscapeweb/js/min/AC_OETags.min.js"></script>
    <!-- Cytoscape Web JS API (needed to reference org.cytoscapeweb.Visualization) -->
    <script type="text/javascript" src="<?php echo $baseurl;?>cytoscapeweb/js/min/cytoscapeweb.min.js"></script>


    <!-- Le styles -->
    <link href="<?php echo $baseurl;?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $baseurl;?>css/motifatlas.css" rel="stylesheet">

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>

<body>