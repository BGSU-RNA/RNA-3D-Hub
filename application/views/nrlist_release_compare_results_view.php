<body class="nrlist_release_compare_results_view">
    <div class="container">
      <div class="content">

        <div class="page-header">
          <h1>Releases <?php echo $rel1;?> and <?php echo $rel2;?></h1>
        </div>


          <div class="span16 block">

            <ul class="tabs" data-tabs="tabs">
                <li><a href="#1_5A">1.5A</a></li>
                <li><a href="#2_0A">2.0A</a></li>
                <li><a href="#2_5A">2.5A</a></li>
                <li><a href="#3_0A">3.0A</a></li>
                <li><a href="#3_5A">3.5A</a></li>
                <li class="active"><a href="#4_0A">4.0A</a></li>
                <li><a href="#20_0A">20.0A</a></li>
                <li><a href="#all">All</a></li>
            </ul>

            <div class="tab-content" id="my-tab-content">
            <?php
            $labels = array('1_5A','2_0A','2_5A','3_0A','3_5A','4_0A','20_0A','all');
            foreach ($labels as $label) {
                if ($label == '4_0A') {
                    echo "<div class='tab-pane active' id='$label'>";
                } else {
                    echo "<div class='tab-pane' id='$label'>";
                }

                echo <<< EOD
                    <div class="row">
                        <div class="span16 info">
                        <h3>Equivalence classes summary</h3>
                        {$uls[$label]['num_motifs1']} equivalence classes in release {$rel1}, {$uls[$label]['num_motifs2']} equivalence classes in release {$rel2}
                        </div>
                        <div class="span3 offset1">
                            <h3>Identical <small>({$uls[$label]['num_intersection']})</small></h3>
                            <div class="comparison">
                                {$uls[$label]['ul_intersection']}
                            </div>
                        </div>
                        <div class="span3 offset1">
                            <h3>Only in $rel1
                            <small>({$uls[$label]['num_only_in_1']}/{$uls[$label]['num_motifs1']})</small>
                            </h3>
                            <div class="comparison">
                                {$uls[$label]['ul_only_in_1']}
                            </div>
                        </div>
                        <div class="span3 offset1">
                            <h3>Only in {$rel2}
                            <small>({$uls[$label]['num_only_in_2']}/{$uls[$label]['num_motifs2']})</small>
                            </h3>
                            <div class="comparison">
                                {$uls[$label]['ul_only_in_2']}
                            </div>
                        </div>
                        <div class="span3 offset1">
                            <h3>Updated
                            <small>({$uls[$label]['num_updated']})</small>
                            </h3>
                            <div class="comparison">
                                {$uls[$label]['ul_updated']}
                            </div>
                        </div>
                    </div>
                </div>
EOD;
            }
            ?>
            </div>



          </div>

        <br>

      </div>