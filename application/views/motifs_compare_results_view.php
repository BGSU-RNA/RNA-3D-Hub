    <div class="container motifs_compare_results_view">
      <div class="content">

        <div class="page-header">
          <h1>Motif Atlas Releases <?php echo $rel1;?> and <?php echo $rel2;?></h1>
        </div>

        <div class="row">

          <div class="span8 well">

            <h3>Changes in PDB files used for clustering</h3>

            <p>
              <?php echo anchor("motifs/release/$motif_type/$rel1", "Release $rel1");?>
              has <?=$pdbs1?> PDB files,
              <?php echo anchor("motifs/release/$motif_type/$rel2", "release $rel2");?>
              has <?=$pdbs2?> PDB files,
              <?php echo count($pdbs_identical); ?> of them are identical.
            </p>

            <p>
            <?php
              $count = count($pdbs_replaced);
              if ( $count > 0 ) {
                  echo "<strong>$count equivalence classes changed representatives: </strong>";
                  foreach($pdbs_replaced as $key => $value) {
                      $replaced[] = "<a href='#' class='pdb'>$key</a> &rarr; " .
                                    "<a href='#' class='pdb'>$value</a>";
                  }
                  echo implode(', ', $replaced) . '.';
              } else {
                  echo 'No equivalence class representatives have been replaced.';
              }
            ?>
            </p>

            <p>
            <?php
              $count = count($pdbs_added);
              if ( $count > 0 ) {
                  echo "<strong>$count new structures in release $rel2: </strong>";
                  sort($pdbs_added);
                  foreach($pdbs_added as $pdb_id) {
                      $added[] = "<a href='#' class='pdb'>$pdb_id</a>";
                  }
                  echo implode(', ', $added) . '.';
              } else {
                  echo 'No new PDB files have been added.';
              }
            ?>
            </p>

            <p>
            <?php
              if ( count($pdbs_removed) > 0 ) {
                  echo "<strong>Obsolete: </strong>";
                  foreach($pdbs_removed as $key => $value) {
                      $removed[] = "<a href='#' class='pdb'>$key</a> &rarr; " .
                                   "<a href='#' class='pdb'>$value</a>";
                  }
                  echo implode(', ', $removed) . '.';
              } else {
                  echo 'No equivalence class representatives have been obsoleted.';
              }
            ?>
            </p>

          </div>

          <div class="span7">
            <p>
            <span class="label notice">Help</span>
            Every Motif Atlas release is based on
            <a href="http://rna.bgsu.edu/rna3dhub/nrlist">a non-redundant set of PDB files</a>.
            When comparing two releases, it's important to know how
            the input data changed.
            </p>

            <p>
            Most PDB files are usually the same.
            <strong>PDB files from the older release</strong>
            can be <em>replaced</em> by newer PDB files or <em>removed</em> from the set altogether
            if the structure is obsoleted by PDB.
            </p>

            <p><strong>PDB files from the newer release</strong> either <em>replace</em>
            representatives in the existing equivalence classes or
            come from <em>new</em> equivalence classes.
            </p>
          </div>

        </div>

        <div class="row">
          <div class="span16 info">
                <?php echo <<<EOD
                <h3>Motif release comparison summary</h3>
                {$uls['num_motifs1']} motifs in release {$rel1_diff}, {$uls['num_motifs2']} motifs in release {$rel2_diff}
                </div>
                <div class="span3 offset1">
                    <h3>Identical <small>({$uls['num_intersection']})</small></h3>
                    <div class="comparison">
                        {$uls['ul_intersection']}
                    </div>
                </div>
                <div class="span3 offset1">
                    <h3>Only in $rel1
                    <small>({$uls['num_only_in_1']}/{$uls['num_motifs1']})</small>
                    </h3>
                    <div class="comparison">
                        {$uls['ul_only_in_1']}
                    </div>
                </div>
                <div class="span3 offset1">
                    <h3>Only in {$rel2}
                    <small>({$uls['num_only_in_2']}/{$uls['num_motifs2']})</small>
                    </h3>
                    <div class="comparison">
                        {$uls['ul_only_in_2']}
                    </div>
                </div>
                <div class="span3 offset1">
                    <h3>Updated
                    <small>({$uls['num_updated']})</small>
                    </h3>
                    <div class="comparison">
                        {$uls['ul_updated']}
                    </div>
                </div>
            </div>
EOD;
?>

        </div>

    <script>
        $('.pdb').click(LookUpPDBInfo);
    </script>
