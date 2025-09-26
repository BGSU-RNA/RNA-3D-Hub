<?php
    $config = config('BGSUConfig');
?>

    <div class="topbar" data-dropdown="dropdown">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo config('BGSUConfig')->hub_url;?>">RNA 3D Hub</a>
          <ul class="nav">

            <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/pdb">RNA Structure Atlas</a></li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Representative Sets</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/nrlist">Home</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/nrlist/release_history">Release history</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/nrlist/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/nrlist/release/current">View current release</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">RNA 3D Motif Atlas</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs">Home</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release_history">Release history</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/hl/current">View current HL</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/il/current">View current IL</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j3/current">View current J3</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j4/current">View current J4</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j5/current">View current J5</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j6/current">View current J6</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j7/current">View current J7</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j8/current">View current J8</a></li>
                    <li><a href="<?php echo config('BGSUConfig')->hub_url;?>/motifs/release/j9/current">View current J9</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Resources</a>
                <ul class="dropdown-menu">
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/fr3d">WebFR3D motif search</a></li>
                    <li><a href="<?= config('BGSUConfig')->bgsu_url ?>/web-applications/webfr3d.html">WebFR3D description</a></li>
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/jar3d">JAR3D map sequences to motifs</a></li>
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/correspondence">R3DMCS Motif Comparison Server</a></li>
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/correspondence/SVS">R3DSVS Sequence Variabilty Server</a></li>
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/fr3d/r3dcid">R3DCID Circular Interaction Diagrams</a></li>
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/correspondence/r3daid">R3DAID Alignment Interaction Diagrams</a></li>
                    <li><a href="<?= config('BGSUConfig')->bgsu_url ?>/APIs.html">API page</a></li>
                    <li><a href="https://github.com/BGSU-RNA/">GitHub for BGSU-RNA</a></li>
                    <li class="divider"></li>
                    <li><a href="https://www.nakb.org/ndbmodule/bp-catalog/">RNA Basepair Catalog at NAKB</a></li>
                    <li><a href="https://docs.google.com/document/d/1EHnh0jnHwXYI0JZBfOtK7r7ABz54TCemdyAEoXm7WI0/pub">RNA Base Triple Database</a></li>
                    <li><a href="<?= config('BGSUConfig')->home_url ?>/FR3D/BasePhosphates/">RNA Base Phosphate Catalog</a></li>
                    <li><a href="https://www.nakb.org/modifiednt.html">Modified nucleotides at NAKB</a></li>
                    <li class="divider"></li>
                    <li><a href="https://www.tinyurl.com/RNA3DStructureCourse">RNA 3D Structure Course</a></li>
                    <li><a href="<?= config('BGSUConfig')->bgsu_url ?>/help/rna-3d-hub-help/">Help</a></li>
                    <li><a href="<?= config('BGSUConfig')->bgsu_url ?>/contact-us.html">Contact us</a></li>
                    <li class="divider"></li>
                    <li><a href="https://www.rcsb.org/">RCSB PDB</a></li>
                    <li><a href="https://www.nakb.org/">NAKB</a></li>
                    <li class="divider"></li>
                    <!-- <li><a href="https://twitter.com/#!/RNA3DHub">Twitter updates</a></li> -->
                </ul>
            </li>

            <li><a href="<?php echo config('BGSUConfig')->hub_url?>/search">Search</a></li>

          </ul>
//            <ul class="nav secondary-nav">
          </ul>

        </div>
      </div>
    </div>
