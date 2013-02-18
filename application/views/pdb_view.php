<body>
    <div class="container pdb_view">

      <div class="content">

        <div class="hero-unit">

            <h1>RNA Structure Atlas</h1>
            <p>

            </p>

            <p>
            - provides annotations of base-pairing, -stacking, and -backbone interactions computed by <a href="http://rna.bgsu.edu/FR3D">FR3D</a>
            </p>
            <p>
            - extracts internal, hairpin and 3-way junction loops and annotates them with motifs from the <a href="<?=$baseurl?>motifs?">RNA 3D Motif Atlas</a>
            </p>
            <p>
            - organizes redundant structures into <a href="<?=$baseurl?>nrlist">non-redundant lists</a> of equivalence classes
            </p>
            <p>
              Updated automatically every week.
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
                    <img src="http://www.pdb.org/pdb/images/1FJG_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 1FJG">
                    T. th. 16S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/3V2F">3V2F
                    <img src="http://www.pdb.org/pdb/images/3V2F_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 3V2F">
                    T. th. 23S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/1S72">1S72
                    <img src="http://www.pdb.org/pdb/images/1S72_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 1S72">
                    H. m. 23S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/2QBG">2QBG
                    <img src="http://www.pdb.org/pdb/images/2QBG_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 2QBG">
                    E. coli 23S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/2AW7">2AW7
                    <img src="http://www.pdb.org/pdb/images/2AW7_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 2AW7">
                    E. coli 16S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/3U5F">3U5F
                    <img src="http://www.pdb.org/pdb/images/3U5F_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 3U5F">
                    S. c. 40S rRNA
                  </a>
                </li>
                <li>
                  <a href="<?=$baseurl?>pdb/4A1B">4A1B
                    <img src="http://www.pdb.org/pdb/images/4A1B_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit 4A1B">
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
                    <img src="http://www.pdb.org/pdb/images/<?=$pdb?>_bio1_r_250.jpg" class="thumbnail span2" alt="Asymmetric unit <?=$pdb?>">
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