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
          <li><a href="#heatmap">Heat map</a></li>
          <li><a href="#annotation">Functional annotation</a></li>
        </ul>

           <div class="tab-content">
              <div class="tab-pane active" id="members">
                  <div class="span100p">
                      <div>
                        <?=$members?>
                      </div>
                  </div>
            </div> <!-- members -->

            <div class="tab-pane" id="history">
              <div class="span100p">
                  <h3>Release history</h3>
                  <div class="horizontal_overflow">
                      <?=$releases?>
                  </div>
              </div>

              <div class="span100p" >
                  <h3>Parents</h3>
                  <div class="parents maxheight400">
                      <?=$parents?>
                  </div>
              </div>
              <br>

              <div class="span100p" >
                  <h3>Children</h3>
                  <div class="parents maxheight400">
                      <?=$children?>
                  </div>
              </div>
            </div> <!-- history -->

            <div class="row span100p interactions resizable" id="heatmap">
                <div class="span100p">
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
            </div> <!-- heatmap -->

            <div class="tab-pane" id="annotation">
                  <div class="span100p">
                      <div>
                        <?=$annotation?>
                      </div>
                  </div>
            </div> <!-- members -->

      </div> <!-- content -->
    </div> <!-- container -->

      <script>
        $(function () {
          $("#members_table").tablesorter();
          $(".pdb").click(LookUpPDBInfo);
        })
      </script>
