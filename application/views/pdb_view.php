<body>
    <div class="container pdb_view">

      <div class="content">

        <div class="hero-unit">

            <h1>RNA Structure Atlas</h1>
            <p>

            </p>

            <p>
              - provides annotations of base-pairing, -stacking, and -backbone 
              interactions computed by 
              <a href="<?=$this->config->item('fr3d_url')?>">FR3D</a>
            </p>
            <p>
              - extracts internal, hairpin and 3-way junction loops and 
              annotates them with motifs from the 
              <a href="<?=$baseurl?>motifs?">RNA 3D Motif Atlas</a>
            </p>
            <p>
              - organizes redundant structures into 
              <a href="<?=$baseurl?>nrlist">representative sets</a> of 
              equivalence classes
            </p>
            <p>
              We are <a href="http://rna.bgsu.edu/main/upgrading-rna-3d-hub/">upgrading BGSU RNA Site</a>
              to include new RNA 3D structures distributed in 
              <a href="http://wwpdb.org/news/news.php?year=2014#10-December-2014">mmCIF format</a>.
              Follow us on 
              <a href="https://twitter.com/rna3dhub">Twitter</a> 
              to hear when the updated version becomes available.
            </p>

            <select data-placeholder="Choose one of <?php echo count($pdbs); ?> RNA-containing 3D structures" tabindex="1" id="chosen" style="width:400px">
             <option value=""></option>
            <?php foreach ($pdbs as $pdb): ?>
              <option value="<?=$pdb?>"><?=$pdb?></option>
            <?php endforeach; ?>
            </select>

        </div>

        <div class="row">

            <div class="span16">
              <h4>Featured Structures</h4>
              <ul class="media-grid">
                <li>
                  <a href="<?=$baseurl?>pdb/1FJG">1FJG
                    <img src="http://cdn.rcsb.org/images/rutgers/fj/1fjg/1fjg.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 1FJG">
                    T. th. 16S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/3V2F">3V2F
                    <img src="http://cdn.rcsb.org/images/rutgers/v2/3v2f/3v2f.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 3V2F">
                    T. th. 23S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/1S72">1S72
                    <img src="http://cdn.rcsb.org/images/rutgers/s7/1s72/1s72.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 1S72">
                    H. m. 23S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/2QBG">2QBG
                    <img src="http://cdn.rcsb.org/images/rutgers/qb/2qbg/2qbg.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 2QBG">
                    E. coli 23S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/2AW7">2AW7
                    <img src="http://cdn.rcsb.org/images/rutgers/aw/2aw7/2aw7.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 2AW7">
                    E. coli 16S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/3U5F">3U5F
                    <img src="http://cdn.rcsb.org/images/rutgers/u5/3u5f/3u5f.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 3U5F">
                    S. c. 40S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/4A1B">4A1B
                    <img src="http://cdn.rcsb.org/images/rutgers/a1/4a1b/4a1b.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit 4A1B">
                    Tetr. th. 26S rRNA
                  </a>
                </li>
              </ul>

            </div>
        </div>

        <div class="row">

            <div class="span16">
              <h4>Recent Structures</h4>
              <ul class="media-grid">
                <?php foreach($recent as $pdb): ?>
                <li>
                  <a href="<?=$baseurl?>pdb/<?=$pdb?>"><?=$pdb?>
                    <img src="http://cdn.rcsb.org/images/rutgers/<?=strtolower(substr($pdb, 1, 2));?>/<?=strtolower($pdb);?>/<?=strtolower($pdb);?>.pdb-250.jpg" class="thumbnail span2" alt="Asymmetric unit <?=$pdb?>">
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