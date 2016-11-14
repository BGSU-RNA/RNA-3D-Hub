    <div class="container motifs_compare_results_view">
      <div class="content">

        <div class="page-header">
          <h1>Motif Atlas Releases <?php echo $rel1;?> and <?php echo $rel2;?></h1>
        </div>

        <div class="well">
          <div class="row">
            <div class="span8">

              <h3>Changes in PDB files used for clustering</h3>

            <p>
              <?php echo anchor("motifs/release/$motif_type/$rel1", "Release $rel1");?>
              has <strong><?=$pdbs1?></strong> PDB files,
              <?php echo anchor("motifs/release/$motif_type/$rel2", "release $rel2");?>
              has <strong><?=$pdbs2?></strong> PDB files,
              <strong><?php echo count($pdbs_identical); ?></strong> of them are identical.
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

            <p class="help">
              <span class="label notice">Help</span>
              Every Motif Atlas release is based on
              <a href="<?=$this->config->item('home_url')?>/rna3dhub/nrlist" target="_blank">a representative set of PDB files</a>.
              When comparing two releases, it's important to know how
              the input data changed.
            </p>

            <p class="help">
              Representative sets are quite stable in time, but
              <strong>PDB files from the older releases</strong>
              can be <em>replaced</em> by newer PDB files or <em>removed</em> 
              from the set altogether if the structure is obsoleted by PDB without replacement.
            </p>

            <p class="help">
              <strong>PDB files from the newer release</strong> either <em>replace</em>
              representatives in the existing equivalence classes or
              come from <em>new</em> equivalence classes.
            </p>
          </div>

          </div> <!-- end row -->
        </div> <!-- end well -->

        <div class="row">

          <div class="span8">
            <h3>Redistribution of motif instances</h3>

            <div id="chart" class="sankey_chart">
            <span id="placeholder">Loading the Sankey diagram...</span>
            </div>
          </div>

          <div class="span8">
            <div class="well">
            <h3>Changes in motif groups</h3>

            <p>
              <?php echo anchor("motifs/release/$motif_type/$rel1", "Release $rel1");?>
              has <strong><?=$num_motifs_release1?></strong> motif groups,
              <?php echo anchor("motifs/release/$motif_type/$rel1", "release $rel2");?>
              has <strong><?=$num_motifs_release2?></strong> motif groups,
              <strong><?=$num_same_groups?></strong> of them are identical.
            </p>

            <p>
              <strong><?=$num_updated_groups?></strong> motif groups were updated,
              <strong><?=$num_removed_groups?></strong> groups are present only in release <?=$rel1?>,
              <strong><?=$num_added_groups?></strong> groups are only in release <?=$rel2?>.
            </p>

            </div>

            <div class="well">

            <p class="help">
              <span class="label notice">Help</span>
              The Sankey diagram shows redistribution of motif instances (loops)
              between two releases. The rectangles represent the motif groups and
              are colored as follows:
            </p>

            <p class="help">
              <strong>Added motif groups:</strong> green
              <br>
              <strong>Removed motif groups:</strong> grey
              <br>
              <strong>Updated motif groups:</strong> orange
              <br>
              <strong>Added motif instances:</strong> green
              <br>
              <strong>Removed motif instances:</strong> grey
            </p>

            <p class="help">
              Hover over the links connecting the motif groups to see which motif
              instances were exchanged.
            </p>

            </div>
          </div>
        </div> <!-- end row -->

        <hr>

        <div class="row">
          <div class="span8">
            <h3>Identical motif groups</h3>
            <div class="motif-summary-table">
            <?=$same_groups?>
            </div>
          </div>

          <div class="span8">
            <h3>Updated motif groups</h3>
            <div class="motif-summary-table">
            <?=$updated_groups?>
            </div>
          </div>
        </div> <!-- end row -->

        <br>

        <div class="row">
          <div class="span8">
            <h3>Removed motif groups</h3>
            <div class="motif-summary-table">
            <?=$removed_groups?>
            </div>
          </div>

          <div class="span8">
            <h3>Added motif groups</h3>
            <div class="motif-summary-table">
            <?=$added_groups?>
            </div>
          </div>
        </div> <!-- end row -->

    </div>


<script src="<?=$baseurl?>/js/d3.js"></script>
<script src="<?=$baseurl?>/js/sankey.js"></script>

<script>

$('.pdb').click(LookUpPDBInfo);

// Sankey diagram
var margin = {top: 1, right: 1, bottom: 6, left: 1},
    width = 400 - margin.left - margin.right,
    height = <?php echo 500 * ($num_updated_groups + $num_removed_groups + $num_updated_groups)/20; ?> - margin.top - margin.bottom;

var formatNumber = d3.format(",.0f"),
    format = function(d) { return formatNumber(d); },
    color = d3.scale.category20();

var svg = d3.select("#chart").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var sankey = d3.sankey()
    .nodeWidth(15)
    .nodePadding(10)
    .size([width, height]);

var path = sankey.link();

d3.json("<?=$baseurl?>rest/getMotifFlowJSON/il/<?=$rel1?>/<?=$rel2?>", function(energy) {

  $('#placeholder').remove();

  sankey
      .nodes(energy.nodes)
      .links(energy.links)
      .layout(32);

  var link = svg.append("g").selectAll(".link")
      .data(energy.links)
    .enter().append("path")
      .attr("class", "link")
      .attr("d", path)
      .style("stroke-width", function(d) { return Math.max(1, d.dy); })
      .sort(function(a, b) { return b.dy - a.dy; });

  link.append("title")
      .text(function(d) { return d.source.name + " → " + d.target.name + "\n" + d.loops; });

  var node = svg.append("g").selectAll(".node")
      .data(energy.nodes)
    .enter().append("g")
      .attr("class", "node")
      .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
    .call(d3.behavior.drag()
      .origin(function(d) { return d; })
      .on("dragstart", function() { this.parentNode.appendChild(this); })
      .on("drag", dragmove));

  node.append("rect")
      .attr("height", function(d) { return d.dy; })
      .attr("width", sankey.nodeWidth())
      .style("fill", function(d) { return d.color = getcolor(d); })
      .style("stroke", function(d) { return d3.rgb(d.color).darker(2); })
    .append("title")
      .text(function(d) { return d.name; });

  node.append("text")
      .attr("x", -6)
      .attr("y", function(d) { return d.dy / 2; })
      .attr("dy", ".35em")
      .attr("text-anchor", "end")
      .attr("transform", null)
      .text(function(d) { return d.name; })
    .filter(function(d) { return d.x < width / 2; })
      .attr("x", 6 + sankey.nodeWidth())
      .attr("text-anchor", "start");

  function dragmove(d) {
    d3.select(this).attr("transform", "translate(" + d.x + "," + (d.y = Math.max(0, Math.min(height - d.dy, d3.event.y))) + ")");
    sankey.relayout();
    link.attr("d", path);
  }

  function getcolor(d) {
    if (d.type == 'new') {
        return 'green';
    } else if (d.type == 'old') {
        return 'grey';
    } else if (d.type == 'added') {
        return 'green';
    } else if (d.type == 'removed') {
        return 'grey';
    } else if (d.type == 'updated') {
        return 'orange';
    }
  }
});

</script>
