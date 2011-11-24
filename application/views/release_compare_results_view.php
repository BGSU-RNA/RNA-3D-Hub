<body class="release_compare_results_view">
    <div class="container">
      <div class="content">
      
        <div class="page-header">
          <h1>Releases <?php echo $rel1;?> and <?php echo $rel2;?></h1>
        </div>
        

          <div class="span16 block">
            <div class="row">
            
            <div class="span16">
            <h3>Motifs summary</h3>
            <?php echo "{$num_motifs1} motifs in release $rel1, {$num_motifs2} motifs in release $rel2"; ?>
            </div>
            <br>
            <div class="span3 comparison">
                <h3>Identical <small>(<?php echo count($intersection);?>)</small></h3> 
                <?php echo $ul_intersection;?>
            </div>
            <div class="span4 comparison"> 
                <h3>Only in <?php echo $rel1;?> 
                <small>(<?php echo count($diff['only_in_1']) . '/' . $num_motifs1;?>)</small>
                </h3>
                <?php echo $ul_only_in_1;?>
            </div>
            <div class="span4 comparison">
                <h3>Only in <?php echo $rel2;?>
                <small>(<?php echo count($diff['only_in_2']) . '/' . $num_motifs2;?>)</small>
                </h3>
                <?php echo $ul_only_in_2;?>
            </div>
            <div class="span3 comparison">
                <h3>Updated
                <small>(<?php echo count($updated);?>)</small>            
                </h3>            
                <?php echo $ul_updated;?>
            </div>
            </div>
          </div>

        <br>
        
          <div class="span16 block">
            <div class="row">
            
            <div class="span16">
            <h3>Loops summary</h3>
            <?php echo "{$num_motifs1} motifs in release $rel1, {$num_motifs2} motifs in release $rel2"; ?>
            </div>
            <br>
            <div class="span3 comparison">
                <h3>Same <small>(<?php echo count($loops['intersection']);?>)</small></h3> 
                <?php echo $ul_loops_intersection;?>
            </div>
            <div class="span4 comparison"> 
                <h3>Only in <?php echo $rel1;?> 
                <small>(<?php echo count($loops['only_in_1']) . '/' . $num_motifs1;?>)</small>
                </h3>
                <?php echo $ul_loops_only_in_1;?>
            </div>
            <div class="span4 comparison">
                <h3>Only in <?php echo $rel2;?>
                <small>(<?php echo count($loops['only_in_2']) . '/' . $num_motifs2;?>)</small>
                </h3>
                <?php echo $ul_loops_only_in_2;?>
            </div>
            </div>
          </div>


      </div>