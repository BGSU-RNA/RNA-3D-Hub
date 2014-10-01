<body>
    <div class="container home">

      <div class="content">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">
        <h1>RNA 3D Hub</h1>
        <br>
        <a href="http://rna.bgsu.edu/main/introduction-to-rna-3d-hub/" class="btn success large">Introduction</a>

      </div>

      <div class="row">

        <div class="span6">
          <h2>RNA Structure Atlas</h2>
          Structural annotations of all RNA-containing 3D structures including:
          <ul>
             <li>Pairwise interactions produced by <a href="http://rna.bgsu.edu/FR3D">FR3D</a></li>
             <li>Hairpin, internal, and three-way junction loops</li>
             <li>Motif annotations from the <a href="http://rna.bgsu.edu/rna3dhub/motifs">Motif Atlas</a></li>
             <li>Similar structures from the <a href="http://rna.bgsu.edu/rna3dhub/nrlist">Non-redundant Lists</a></li>
          </ul>
          <p>Automatic weekly updates.</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>pdb">Explore</a></p>
        </div>

        <div class="span5">
          <h2>RNA 3D Motif Atlas</h2>
          <p>A representative collection of recurrent RNA 3D internal and hairpin loop motifs extracted from a non-redundant set of structures.</p>
          <p>Automatic updates every 4 weeks.</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>motifs">Explore</a></p>
        </div>

        <div class="span5">
          <h2>Non-redundant Lists</h2>
          <p>PDB files grouped into equivalence classes by molecule type, organism, and resolution.</p>
          <p>Automatic weekly updates.</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>nrlist">Explore</a></p>
       </div>

      </div>

    </div>
