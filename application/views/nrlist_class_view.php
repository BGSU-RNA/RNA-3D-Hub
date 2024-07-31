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
    /* .flex-container {
      display: flex;
      justify-content: space-between;
      width: 100%;
    }
    .spanjmol, .heatmap_section {
      width: 48%;
    } */

    /* .float-container {
      width: 100%;
      overflow: auto;
    }
    .heatmap_section {
      width: 48%;
      float: left;
      margin: 1%;
    }
    .spanjmol {
      width: 48%;
      float: right;
      margin: 1%;
    } */
</style>













<!-- container -->
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
    </ul>

    <div class="tab-content">
      <!-- members tag -->
      <div class="tab-pane active" id="members">
        <style>
          .tab-pane.active th {
            background-color: rgba(255, 255, 255, 1);
            position: sticky;
            top: 40px;
            z-index: 1;
          }
        </style>
        <div class="span100p">
          <div>
            <?=$members?>
          </div>
        </div>
      </div> <!-- members -->

      <!-- history tag -->
      <div class="tab-pane" id="history">
        <div class="span100p">
          <h3>Release history</h3>
          <div class="horizontal_overflow">
            <?=$releases?>
          </div>
        </div>
        <div class="span100p">
          <h3>Parents</h3>
          <div class="parents maxheight400">
            <?=$parents?>
          </div>
        </div>
        <br>
        <div class="span100p">
          <h3>Children</h3>
          <div class="parents maxheight400">
            <?=$children?>
          </div>
        </div>
      </div> <!-- history -->

      <!-- heatmap tag -->
      <div class="row span100p interactions resizable" id="heatmap">
        <div class="span100p">
          <div>
            <p align="center">Heat map of mutual geometric discrepancy, in Angstroms per nucleotide. Instances are ordered to put similar structures near each other. The colorbar ranges from 0 to the maximum observed discrepancy, up to 0.5</p>
            <div id='chart' style="text-align: center;"></div>
            <span class="muted">
              #S - ordering by similarity (same as in the heat map).
            </span>
            <div>
              <?=$statistics?>
            </div>
            <!-- <div class="float-container"> -->
              <div class = 'heatmap_section' id = 'heatmap_section'>
                <script src="//d3js.org/d3.v4.min.js"></script>
                <script type="text/javascript">
                  var data = <? echo $heatmap_data ?>;
                </script>
                <script type="text/javascript" src="http://rna.bgsu.edu/webfr3d/js/heatmap.js"></script>
              </div>


              <!-- <div class="heatmap_section" id="heatmap_section">
                <div style="background-color: lightgrey; width: 100%; height: 300px; text-align: center; line-height: 300px;">
                  Heatmap Placeholder
                </div>
              </div> -->




              <!-- <div class="spanjmol" id="jmolBlock">
                <div class="block-div_jmolheight">
                  <script>
                    jmol_isReady = function (applet) {
                      $('.jmolInline').jmolTools({
                        showStereoId: 'stereo',
                        showNeighborhoodId: 'neighborhood',
                        showNumbersId: 'showNtNums',
                        showNextId: 'next',
                        showPrevId: 'prev',
                        showAllId: 'all',
                        colorByRSRZ: 'colorRSRZ',
                        colorByRSR: 'colorRSR',
                        colorOption: 'colorOPT',
                        clearId: 'clear',
                        insertionsId: 'insertions'
                      });
                      $('.jmolInline').first().jmolToggle();
                    };

                    var Info = {
                      width: 565,
                      height: 340,
                      debug: false,
                      color: 'white',
                      addSelectionOptions: false,
                      use: 'HTML5',
                      j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
                      readyFunction: jmol_isReady,
                      disableInitialConsole: true
                    };

                    var jmolApplet0 = Jmol.getApplet('jmolApplet0', Info);

                    function jmolCheckbox(script1, script0, text, ischecked) {
                      Jmol.jmolCheckbox(jmolApplet0, script1, script0, text, ischecked)
                    };

                    function jmolButton(script, text) {
                      Jmol.jmolButton(jmolApplet0, script, text)
                    };

                    function jmolHtml(s) {
                      document.write(s)
                    };

                    function jmolBr() {
                      jmolHtml("<br />")
                    };

                    function jmolMenu(a) {
                      Jmol.jmolMenu(jmolApplet0, a)
                    };

                    function jmolScript(cmd) {
                      Jmol.script(jmolApplet0, cmd)
                    };

                    function jmolScriptWait(cmd) {
                      Jmol.scriptWait(jmolApplet0, cmd)
                    };
                  </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <input type='button' id='stereo' class='btn' value="Stereo">
                <input type='button' id='clear' class='btn' value="Clear all">
                <br>
                Coloring options: <select id="colorOPT">
                  <option value="Default" selected>Default</option>
                  <option value="CPK">CPK</option>
                  <option value="RSR">Real Space R (RSR)</option>
                  <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
                <br>
                <br>
              </div> -->


            <!-- </div> -->
          </div>
        </div>
              <div class="spanjmol" id="jmolBlock">
                <div class="block-div_jmolheight">
                  <script>
                    jmol_isReady = function (applet) {
                      $('.jmolInline').jmolTools({
                        showStereoId: 'stereo',
                        showNeighborhoodId: 'neighborhood',
                        showNumbersId: 'showNtNums',
                        showNextId: 'next',
                        showPrevId: 'prev',
                        showAllId: 'all',
                        colorByRSRZ: 'colorRSRZ',
                        colorByRSR: 'colorRSR',
                        colorOption: 'colorOPT',
                        clearId: 'clear',
                        insertionsId: 'insertions'
                      });
                      $('.jmolInline').first().jmolToggle();
                    };

                    var Info = {
                      width: 565,
                      height: 340,
                      debug: false,
                      color: 'white',
                      addSelectionOptions: false,
                      use: 'HTML5',
                      j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
                      readyFunction: jmol_isReady,
                      disableInitialConsole: true
                    };

                    var jmolApplet0 = Jmol.getApplet('jmolApplet0', Info);

                    function jmolCheckbox(script1, script0, text, ischecked) {
                      Jmol.jmolCheckbox(jmolApplet0, script1, script0, text, ischecked)
                    };

                    function jmolButton(script, text) {
                      Jmol.jmolButton(jmolApplet0, script, text)
                    };

                    function jmolHtml(s) {
                      document.write(s)
                    };

                    function jmolBr() {
                      jmolHtml("<br />")
                    };

                    function jmolMenu(a) {
                      Jmol.jmolMenu(jmolApplet0, a)
                    };

                    function jmolScript(cmd) {
                      Jmol.script(jmolApplet0, cmd)
                    };

                    function jmolScriptWait(cmd) {
                      Jmol.scriptWait(jmolApplet0, cmd)
                    };
                  </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <input type='button' id='stereo' class='btn' value="Stereo">
                <input type='button' id='clear' class='btn' value="Clear all">
                <br>
                Coloring options: <select id="colorOPT">
                  <option value="Default" selected>Default</option>
                  <option value="CPK">CPK</option>
                  <option value="RSR">Real Space R (RSR)</option>
                  <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
                <br>
                <br>
              </div>
      </div> <!-- heatmap -->
    </div> <!-- tab-content -->
  </div> <!-- content -->
</div> <!-- container -->

<script>
  $(function () {
    $("#members_table").tablesorter();
    $(".pdb").click(LookUpPDBInfo);
  })
</script>
