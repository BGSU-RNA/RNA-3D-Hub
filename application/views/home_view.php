<body>
    <div class="container home">

      <div class="content">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">
        <h1>RNA 3D Hub</h1>
        <p>
        <a href="<?=$this->config->item('home_url')?>/main/introduction-to-rna-3d-hub/" class="btn success large">Introduction</a>
        Follow us on <a href="https://twitter.com/rna3dhub">Twitter</a>.
        </p>
        <p>
          Every week we download and annotate all new RNA-containing 3D structures from PDB.
          Annotations of individual 3D structures are available in the RNA Structure Atlas.
          The Representative Sets of RNA structures organize 3D structures by molecule type (such as large ribosomal subunit) and by species, by order of structure quality measures.
          The RNA 3D Motif Atlas organizes hairpin and internal loops by their geometry.
        </p>
        <p>
          The Resources link above takes you to WebFR3D, to search within RNA-containing 3D structures,
          and JAR3D, to search hairpin and internal loops by sequence.
        </p>
      </div>

      <div class="row">

        <div class="span30p">
          <h2>RNA Structure Atlas</h2>
          Structural annotations of all RNA-containing 3D structures including:
          <ul>
             <li>Pairwise interactions produced by <a href="<?=$this->config->item('fr3d_url')?>">FR3D</a></li>
             <li>Hairpin, internal, and three-way junction loops</li>
             <li>Motif annotations from the <a href="<?=$this->config->item('home_url')?>/rna3dhub/motifs">Motif Atlas</a></li>
             <li>Similar structures from the <a href="<?=$this->config->item('home_url')?>/rna3dhub/nrlist">Representative Sets</a></li>
          </ul>
          <p><a class="btn primary" href="<?php echo $baseurl;?>pdb">Explore</a></p>
        </div>

        <div class="span30p">
          <h2>RNA 3D Motif Atlas</h2>
          <p>A representative collection of recurrent RNA 3D internal and hairpin loop motifs extracted from a representative set of structures.</p>
          <p>Automatic updates <a href="http://rna.bgsu.edu/main/upgrading-rna-3d-hub/">will resume soon</a></p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>motifs">Explore</a></p>
        </div>

        <div class="span30p">
          <h2>Representative Sets</h2>
          <p>RNA&DNA 3D structures grouped into equivalence classes by molecule type, organism, and resolution.
          Taking the structure with the best structure quality measures from each equivalence class gives a Representative Set of RNA/DNA 3D structures.
          Updated each week since 2011.</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>nrlist">Explore RNA</a></p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>nrlist/dna">Explore DNA</a></p>
       </div>

      </div>

    </div>
