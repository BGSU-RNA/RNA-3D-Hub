<body>
    <div class="container pdb_view">

      <div class="content">

        <div class="hero-unit">

            <h1>RNA Structure Atlas</h1>
            <p>
              Provides annotations of base-pairing, base stacking, and base-backbone
              interactions computed by
              <a href="<?=$this->config->item('fr3d_url')?>">FR3D</a>
            </p>
            <p>
              Extracts internal, hairpin and 3-way junction loops and
              annotates representative structures with motifs from the
              <a href="<?=$baseurl?>motifs?">RNA 3D Motif Atlas</a>
            </p>
            <p>
              All RNA-containing 3D structures are downloaded and annotated each week, including
              large structures in mmCIF format.
            </p>
            <p>
              Follow us on
              <a href="https://twitter.com/rna3dhub">Twitter</a>
            </p>

            <select data-placeholder="Choose one of <?php echo count($pdbs); ?> RNA-containing 3D structures" tabindex="1" id="chosen" style="width:350px">
             <option value=""></option>
            <?php foreach ($pdbs as $pdb): ?>
              <option value="<?=$pdb?>"><?=$pdb?></option>
            <?php endforeach; ?>
            </select>

        </div>

        <div class="row">

            <div class="span">
              <h4>Featured Structures</h4>
              <ul class="media-grid">
                <li>
                  <a href="<?=$baseurl?>pdb/6ZMI">6ZMI
                    <img src="https://cdn.rcsb.org/images/structures/zm/6zmi/6zmi_assembly-1.jpeg" class="thumbnail span2" alt="6ZMI assembly">
                    Human ribosome
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/4Y4O">4Y4O
                    <img src="https://cdn.rcsb.org/images/structures/y4/4y4o/4y4o_assembly-1.jpeg" class="thumbnail span2" alt="4Y4O assembly">
                    T. th. ribosome
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/7K00">7K00
                    <img src="https://cdn.rcsb.org/images/structures/k0/7k00/7k00_assembly-1.jpeg" class="thumbnail span2" alt="7K00 assembly">
                    E. coli ribosome
                  </a>
                </li>

              </ul>

            </div>
        </div>

        <div class="row">

            <div class="span">
              <h4>Recent Structures</h4>
              <ul class="media-grid">
                <?php foreach($recent as $pdb): ?>
                <li>
                  <a href="<?=$baseurl?>pdb/<?=$pdb?>"><?=$pdb?>
                    <img src="https://cdn.rcsb.org/images/structures/<?=strtolower(substr($pdb, 1, 2));?>/<?=strtolower($pdb);?>/<?=strtolower($pdb);?>_assembly-1.jpeg" class="thumbnail span2" alt="<?=$pdb?> assembly">
                  </a>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>

        </div>

      </div>

      <script>
        $('#chosen').chosen().change(function(){
            window.location.href = "<?=$baseurl?>pdb/" + $(this).val();
        });
      </script>