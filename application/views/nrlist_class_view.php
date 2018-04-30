  <style>
    rect.bordered {
      stroke: #E6E6E6;
      stroke-width: 10px;
    } 

    text.mono {
      font-size: 9pt;
      font-family: Consolas, courier;
      fill: #aaa;
    }

    text.axis-workweek {
      fill: #000;
    }

    text.axis-worktime {
      fill: #000;
    }

    .axis path,
    .axis tick,
    .axis line {
      fill: none;
      stroke: none;
    } 
  </style> 

    <div class="container nrlist_class_view"> 
      <div class="content">
        <div class="page-header">
          <h1>Equivalence class <?=$title?>
            <small><?=$status?></small>
          </h1>
        </div> <!-- page-header -->

        <ul class="tabs" data-tabs="tabs">
          <li class="active"><a href="#members">Members (<?=$num_members?>)</a></li>
          <li><a href="#history">History</a></li>
          <li><a href="#statistics">Statistics</a></li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane active" id="members">
            <div class="span16">
              <div>
                <?=$members?>
              </div>
            </div>
          </div> <!-- members -->
                
                 
          <div class="tab-pane" id="history">
            <div class="span16">
              <h3>Release history</h3>
              <div class="horizontal_overflow">
                <?=$releases?>
              </div>
            </div>
            <br> 

            <div class="span16">
              <h3>Parents</h3>
              <div class="parents maxheight400">
                <?=$parents?>
              </div>
            </div> 

            <div class="span16">
              <h3>Children</h3>
              <div class="parents maxheight400">
                <?=$children?>
              </div>
            </div>
          </div>

          <div class="row span16 interactions resizable" id="statistics">
            <div class="span16">
              <div>
                <p align="center">Heat map of mutual geometric discrepancy, in Angstroms per nucleotide.  Instances are ordered to put similar structures near each other. The colorbar ranges from 0 to the maximum observed discrepancy, up to 0.5</p>
                <!-- Not sure what this is -->
                <div id ='chart' style="text-align: center;"></div>

                <span class="muted">
                 #S - ordering by similarity (same as in the heat map).
                </span>
                <div>
                  <?=$statistics?> 
                </div>
                <script src="//d3js.org/d3.v4.min.js"></script>
                <script type="text/javascript">
                          var data = <? echo $heatmap_data; ?>;
                </script>
                <script type="text/javascript" src="<?=$baseurl?>js/heatmap.js"></script>
              </div>
            </div>
          </div> <!-- statistics -->
        </div> <!-- tab-content -->
      </div> <!-- content -->

      <script>
        $(function () {
          $("#members_table").tablesorter();
          $(".pdb").click(LookUpPDBInfo);
        })
      </script>
