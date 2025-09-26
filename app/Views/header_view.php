<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?=$title;?></title>

    <?php if (isset($meta)): ?>
    <meta name="description" content="<?=$meta['description']?>">
    <?php endif; ?>

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/jmol/Jmol.js"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/jsmol/JSmol.min.nojq.js"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/jquery.jmolTools.WebFR3D.js"></script>

    <!-- Wondering if this actually does anything anymore
    <script src="<?=config('BGSUConfig')->hub_url?>/js/jmolInlineLoader.js"></script>
    -->

    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-twipsy.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-popover.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-dropdown.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-tabs.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-buttons.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-modal.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/bootstrap-alerts.js" type="text/javascript"></script>
    <script src="<?=config('BGSUConfig')->hub_url?>/js/jquery.tablesorter.min.js" type="text/javascript"></script>

    <script src="<?=config('BGSUConfig')->hub_url?>/js/MotifAtlas.js" type="text/javascript"></script>
    <script src="https://d3js.org/d3.v4.min.js"></script>

    <!--
    https://www.rcsb.org/pdb/widgets/rcsbPdbWidgets.css was failing to load.
    Commenting this out helps, but maybe we lose some important functionality somewhere?
    <script src="<?=config('BGSUConfig')->hub_url?>/js/rcsbPdbImageLib.min.js" type="text/javascript"> </script>
    -->

    <!-- chosen -->
    <script type="text/javascript" src="<?=config('BGSUConfig')->hub_url?>/js/chosen/chosen.jquery.min.js"></script>
    <link rel="stylesheet" href="<?=config('BGSUConfig')->hub_url?>/js/chosen/chosen.css" type="text/css" media="screen" />

    <?php if (strstr(current_url(), 'graph')): ?>
    <!-- cytoscapeweb -->
    <!-- JSON support for IE (needed to use JS API) -->
    <script type="text/javascript" src="<?=config('BGSUConfig')->hub_url?>/cytoscapeweb/js/min/json2.min.js"></script>
    <!-- Flash embedding utility (needed to embed Cytoscape Web) -->
    <script type="text/javascript" src="<?=config('BGSUConfig')->hub_url?>/cytoscapeweb/js/min/AC_OETags.min.js"></script>
    <!-- Cytoscape Web JS API (needed to reference org.cytoscapeweb.Visualization) -->
    <script type="text/javascript" src="<?=config('BGSUConfig')->hub_url?>/cytoscapeweb/js/min/cytoscapeweb.min.js"></script>
    <?php endif; ?>

    <!-- Le styles -->
    <link href="<?=config('BGSUConfig')->hub_url?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=config('BGSUConfig')->hub_url?>/css/motifatlas.css" rel="stylesheet">
    <link href='<?=config('BGSUConfig')->hub_url?>/css/bootstrap-toggle-buttons.css' rel='stylesheet'>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?=$pageicon?>">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>

<body>
