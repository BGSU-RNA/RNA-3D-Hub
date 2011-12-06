    <div class="container motifs_compare_results_view">
      <div class="content">

        <div class="page-header">
          <h1>Releases <?php echo $rel1;?> and <?php echo $rel2;?></h1>
        </div>


<!--           <div class="span16 block"> -->
            <div class="row">
                <div class="span16 info">
                <?php echo <<<EOD
                <h3>Motif release comparison summary</h3>
                {$uls['num_motifs1']} motifs in release {$rel1}, {$uls['num_motifs2']} motifs in release {$rel2}
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
<!--       </div> -->