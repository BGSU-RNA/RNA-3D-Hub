    <div class="topbar" data-dropdown="dropdown">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo $baseurl;?>">RNA 3D Hub</a>
          <ul class="nav">

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">RNA 3D Structures</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>pdb">View all PDBs</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>loops/sfdata">Sfcheck and Mapman: Loops</a></li>
                    <li><a href="<?php echo $baseurl;?>loops/sfjmol">Sfcheck and Mapman: PDBs</a></li>
                    <li><a href="<?php echo $baseurl;?>loops/graphs">Sfcheck and Mapman: Graphs</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">RNA Loops</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>loops">Weekly stats</a></li>
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
                <a href="#" class="dropdown-toggle">Non-redundant Lists</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>nrlist">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release_history">Release history</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release/current">View current release</a></li>
                </ul>
            </li>

            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>
      </div>
    </div>