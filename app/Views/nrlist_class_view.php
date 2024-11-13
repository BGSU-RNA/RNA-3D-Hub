<style>

    /* Force the Molecule entries to wrap, since some are very long sequences */
    table.bordered-table td:nth-child(4) {
        word-wrap: break-word;
        word-break: break-all;
    }

    /* Force the heat map table to start with a vertical scrollbar */
    .scrollable {
        max-height: 400px; /* Set a max height for vertical scroll */
        overflow-y: auto;  /* Enable vertical scrolling */
        max-width: 100%;   /* Ensure it fits within the container */
        overflow-x: auto;  /* Enable horizontal scrolling if needed */
    }


    .table_controls {
    display: flex;
    justify-content: space-between; /* Spread elements evenly */
    align-items: center;            /* Align them vertically */
    width: 100%;
    max-width: 98%;                    /* Limit max width */
    white-space: nowrap; /* Prevent line breaks */
  padding: 5px 10px;                   /* Reduced padding to make it shorter */
}

.table_controls .entries-info {
    margin-right: auto; /* Move the text to the far left */
    white-space: nowrap; /* Prevent line breaks */
    overflow: hidden;    /* Ensure floats stay within container */
}

.table_controls .filter-box {
    margin-left: auto; /* Ensure the filter box stays on the far right */
    white-space: nowrap; /* Prevent line breaks */
    overflow: hidden;    /* Ensure floats stay within container */
}

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
      <div class="row span100p interactions" id="heatmap">
        <div class="span100p">
          <div>
            <div id='chart' style="text-align: center;"></div>
            <p>
              Instances are ordered to put similar structures near each other.
              Select one instance to see its 3D structure.
              Selecting two or more instances will show their superposition,
              but only chains with identical numbers of observed nucleotides
              will superpose well.
              Large structures are slow to display; this tool is not designed for that.
            </p>
            <div class="row span98p resizable scrollable" id="statistics_table">
            <?=$statistics?>
            </div>
            <p>
              Heat map of mutual geometric discrepancy, in Angstroms per nucleotide.
              The ordering in the heat map is the same as in the table.
              The colorbar ranges from 0 to the maximum observed discrepancy.
              Click above the diagonal to select a range of structures, below the
              diagonal to select two structures.
            </p>
            <!-- <div class="float-container"> -->
              <div class = 'heatmap_section' id = 'heatmap_section'>
                <script src="//d3js.org/d3.v4.min.js"></script>
                <script type="text/javascript">
                  var data = <? echo $heatmap_data ?>;
                </script>
                <script type="text/javascript" src="<?=config('BGSUConfig')->hub_url?>/js/heatmap_2024.js"></script>
              </div>
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

<script type="text/javascript" src="<?=$baseurl?>js/jquery.dataTables.min.js"></script>

<script>
    // $(document).ready(function() {
    $(function() {
        $('table.bordered-table').DataTable({
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "sDom": '<"table_controls well"fi>t'
        });
    });

    $(".pdb").click(LookUpPDBInfo);
  </script>