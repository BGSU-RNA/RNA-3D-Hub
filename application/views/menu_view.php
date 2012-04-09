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

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Loop Atlas</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>loops">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>pdb">Browse by PDB</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>loops/benchmark/IL">Loop extraction benchmark</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Motif Atlas</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>motifs">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/release_history">Release history</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>motifs/release/il/current">View current ILs</a></li>
                    <li><a href="<?php echo $baseurl;?>motifs/release/hl/current">View current HLs</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">NR Lists</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>nrlist">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release_history">Release history</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release/current">View current release</a></li>
                </ul>
            </li>

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

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Apps</a>
                <ul class="dropdown-menu">
                    <li><a href="http://rna.bgsu.edu/WebFR3D">WebFR3D</a></li>
                    <li><a href="http://rna.bgsu.edu/WebFR3D">R3DAlign</a></li>
                    <li><a href="http://rna.bgsu.edu/research/JAR3D">JAR3D (private &szlig;)</a></li>
                    <li class="divider"></li>
                    <li><a href="http://rna.bgsu.edu/FR3D">FR3D</a></li>
                    <li class="divider"></li>
                    <li><a href="http://rna.bgsu.edu">RNA BGSU Home</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Links</a>
                <ul class="dropdown-menu">
                    <li><a href="https://twitter.com/#!/RNA3DHub">Twitter updates</a></li>
                    <li><a href="https://github.com/AntonPetrov/RNA-3D-Hub">GitHub</a></li>
                </ul>
            </li>



<!--
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
 -->
          </ul>
            <ul class="nav secondary-nav">
            <?php
                if ( ! $this->session->userdata('username') ) { //! isset($_SESSION['username'])) {
                    echo "<li class='pull-left'><a href='{$baseurl}admin'>Login</a></li>";
                } else {
                    echo "<li class='pull-left'><a href='{$baseurl}logout'>Logout {$this->session->userdata('username')}</a></li>";
//                     echo "<li><a href='{$baseurl}logout'>Logout {$_SESSION['username']}</a></li>";
                }
            ?>
          </ul>

        </div>
      </div>
    </div>