<?php
    if(!isset($_SESSION)) {
        session_start();
    }
?>
    <div class="topbar" data-dropdown="dropdown">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo $baseurl;?>">RNA 3D Hub</a>
          <ul class="nav">

            <li><a href="<?php echo $baseurl;?>pdb">RNA Structure Atlas</a></li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">RNA 3D Motif Atlas</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>motifs">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/release_history">Release history</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/compare_releases">Compare releases</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/2ds">2D diagrams</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>motifs/release/il/current">View current ILs</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/release/hl/current">View current HLs</a></li>
                </ul>
            </li>

<!--
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Loop Atlas</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>loops">Home</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>loops/benchmark/IL">Loop extraction benchmark</a></li>
                </ul>
            </li>
 -->

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Non-redundant Lists</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>nrlist">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release_history">Release history</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release/current">View current release</a></li>
                </ul>
            </li>
<!--
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">RNA PDBs</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>pdb">View all PDBs</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>loops/sfdata">Sfcheck and Mapman: Loops</a></li>
                    <li><a href="<?php echo $baseurl;?>loops/sfjmol">Sfcheck and Mapman: PDBs</a></li>
                    <li><a href="<?php echo $baseurl;?>loops/graphs">Sfcheck and Mapman: Graphs</a></li>
                </ul>
            </li>
 -->

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Resources</a>
                <ul class="dropdown-menu">
                    <li><a href="<?=$this->config->item('home_url')?>/WebFR3D">WebFR3D</a></li>
                    <li><a href="<?=$this->config->item('home_url')?>/JAR3D">JAR3D &szlig;</a></li>
                    <li><a href="<?=$this->config->item('home_url')?>/r3dalign/">R3DAlign</a></li>
                    <li><a href="<?=$this->config->item('fr3d_url')?>">FR3D</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>loops">Loop Atlas</a></li>
                    <li><a href="<?=$this->config->item('home_url')?>/FR3D/basepairs/">RNA Basepair Catalog</a></li>
                    <li><a href="<?=$this->config->item('home_url')?>/Triples/">RNA Base Triple Database</a></li>
                    <li><a href="<?=$this->config->item('home_url')?>/FR3D/BasePhosphates/">RNA Base Phosphate Catalog</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Links</a>
                <ul class="dropdown-menu">
                    <li><a href="<?=$this->config->item('home_url')?>">RNA BGSU Home</a></li>
                    <li><a href="<?=$this->config->item('home_url')?>/main/rna-3d-hub-help/">Help</a></li>
                    <?php
                        $current = current_url();
                        if ( !strstr($current, 'rna3dhub_dev') ){
                            $link = str_replace('/rna3dhub/', '/rna3dhub_dev/', $current);
                            echo '<li>' . anchor($link, 'Dev site') . '</li>';
                        } else {
                            $link = str_replace('/rna3dhub_dev/', '/rna3dhub/', $current);
                            echo '<li>' . anchor($link, 'Production') . '</li>';
                        }
                    ?>
                    <li class="divider"></li>
                    <li><a href="http://pdb.org">PDB</a></li>
                    <li><a href="http://ndbserver.rutgers.edu">NDB</a></li>
                    <li class="divider"></li>
                    <li><a href="https://github.com/BGSU-RNA/RNA-3D-Hub">GitHub</a></li>
                    <li><a href="https://twitter.com/#!/RNA3DHub">Twitter updates</a></li>
                </ul>
            </li>

            <li><a href="<?php echo $baseurl?>search">Search</a></li>

          </ul>
            <ul class="nav secondary-nav">
            <?php
                if ( ! $this->session->userdata('username') ) { //! isset($_SESSION['username'])) {
                    echo "<li class='pull-left'><a href='{$baseurl}admin'>Login</a></li>";
                } else {
                    echo "<li class='pull-left'><a href='{$baseurl}logout'>Logout</a></li>";
//                     echo "<li><a href='{$baseurl}logout'>Logout {$_SESSION['username']}</a></li>";
                }
            ?>
          </ul>

        </div>
      </div>
    </div>
