
    <!-- RNA2D -->
    <script type="text/javascript" src="<?=$baseurl?>js/sizzle.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/d3.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/jquery.rna2d.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d-controls.js"></script>

    <div class="container pdb-2d-view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?=strtoupper($pdb_id)?>
            <small>2D representation</small>
            <small class="pull-right">
            <select data-placeholder="Choose a structure" id="chosen">
              <option value=""></option>
                <?php foreach ($pdbs as $pdb): ?>
                  <option value="<?=$pdb?>"><?=$pdb?></option>
                <?php endforeach; ?>
            </select>
          </small>
          </h1>
        </div>

        <!-- navigation -->
        <div class="row">
          <div class="span16">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Motifs</a></li>
                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Interactions</a>
                  <ul class="dropdown-menu">
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basepairs">Base-pair</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/stacking">Base-stacking</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basephosphate">Base-phosphate</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/baseribose">Base-ribose</a></li>
                    <li class="divider"></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/all">All interactions</a></li>
                  </ul>
                </li>
                <li class="active"><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/2d">2D Diagram</a></li>
            </ul>
          </div>
        </div>
        <!-- end navigation -->

        <div class="row">
            <div class="span3 offset4">
                <div id='view-buttons' class='btn-group' data-toggle='buttons-radio'>
                    <?php if ($has_airport): ?>
                    <button id='airport-view' class="btn view-control" data-view='airport'>
                      Airport
                    </button>
                    <?php else: ?>
                    <button id='airport-view' disabled="disabled" 
                        class="btn hasTooltip disabled view-control" data-view='airport'
                        title="No airport diagram is available yet">
                      Airport
                    </button>
                    <?php endif; ?>
                    <button id='circular-view' class="btn active view-control" data-view='circular'>
                      Circular
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div id='controls' class='span1 block-controls'>

              <button data-controls-modal="help-modal" data-backdrop="true" 
                data-keyboard="true" class="btn primary btn-block">Help</button>

              <div id="motif-controls">
                  <button id='il-toggle' type="button" class="btn btn-block
                        motif-toggle active" disabled='disabled' data-toggle='button' data-toggable='IL'>IL</button>
                    <button id='hl-toggle' type="button" class="btn btn-block
                        motif-toggle active" disabled='disabled' data-toggle='button' data-toggable='HL'>HL</button>
                    <button id='j3-toggle' type="button" class="btn btn-block
                        motif-toggle active" disabled='disabled' data-toggle='button' data-toggable='J3'>J3</button>
              </div>

              <div id='nucleotide-controls'>
                <button type='button' id='rsr-controls' class='btn btn-block nt-color' 
                  data-toggle='button' data-attr='real_space_r'>RsR</button>
              </div>

              <div id="control-groups">
                <div id="interaction-controls">
                    <button type="button" id="all-toggle" class="btn btn-block
                      toggle-control" data-toggle='button' 
                      data-toggable='tWW,cWS,tWS,cWH,tWH,cSH,tSH,cSS,tSS,cHH,tHH'
                      data-activate='cWW'>
                      All
                    </button>

                    <button type="button" id='cWW-toggle' class="btn btn-block
                      cWW toggle-control active" data-toggle='button' data-toggable='cWW'>cWW</button>

                    <button type="button" id='tWW-toggle' class="btn btn-block
                      tWW toggle-control" data-toggle='button' data-toggable='tWW'>tWW</button>

                    <button type="button" id="cWS-toggle" class="btn btn-block
                      cWS toggle-control" data-toggle='button' data-toggable='cWS'>cWS</button>

                    <button type="button" id="tWS-toggle" class="btn btn-block
                      tWS toggle-control" data-toggle='button' data-toggable='tWS'>tWS</button>

                    <button type="button" id="cWH-toggle" class="btn btn-block
                      cWH toggle-control" data-toggle='button' data-toggable='cWH'>cWH</button>

                    <button type="button" id="tWH-toggle" class="btn btn-block
                      tWH toggle-control" data-toggle='button' data-toggable='tWH'>tWH</button>

                    <button type="button" id="cSH-toggle" class="btn btn-block
                      cSH toggle-control" data-toggle='button' data-toggable='cSH'>cSH</button>

                    <button type="button" id="tSH-toggle" class="btn btn-block
                      tSH toggle-control" data-toggle='button' data-toggable='tSH'>tSH</button>

                    <button type="button" id="cSS-toggle" class="btn btn-block
                      cSS toggle-control" data-toggle='button' data-toggable='cSS'>cSS</button>

                    <button type="button" id="tSS-toggle" class="btn btn-block
                      tSS toggle-control" data-toggle='button' data-toggable='tSS'>tSS</button>

                    <button type="button" id="cHH-toggle" class="btn btn-block
                      cHH toggle-control" data-toggle='button' data-toggable='cHH'>cHH</button>

                    <button type="button" id="tHH-toggle" class="btn btn-block
                      tHH toggle-control" data-toggle='button' data-toggable='tHH'>tHH</button>

                    <button type="button" id="lr-toggle" class="btn btn-block
                      toggle-control" data-toggle='button'
                      data-toggable='LR'>
                      LR
                    </button>

                </div>

              </div>

            </div>

          <div id='rna-2d' class='rna2d span8' data-pdb='<?=$pdb_id?>'>
            &nbsp;
          </div>

            <div class="right-side row span6">

              <div class="row span6">
                <div id="error-message" class="alert-message error hide fade in" data-alert='alert'>
                   <a class="close" href="#">×</a>
                </div> 


<!--                 <div class="row span6">
                    <form class="form-search">
                        <fieldset>
                            <input id='nt-selection-box' type="text" placeholder="Nucleotide selection...">
                            <button id="nt-selection-button" type="button" class="btn">Display</button>
                        </fieldset>
                    </form>
                </div>
 -->
                <div class="row span6">
                    <div id="jmol" class="well span6 width-320-px">
<script>
    var Info = {
        width: 320,
        height: 320,
        debug: false,
        color: 'white',
        addSelectionOptions: false,
        use: 'HTML5',
        j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
        disableInitialConsole: true
    };

    var jmolApplet0 = Jmol.getApplet('jmolApplet0', Info);

    // these are conveniences that mimic behavior of Jmol.js
    function jmolCheckbox(script1, script0,text,ischecked) {Jmol.jmolCheckbox(jmolApplet0,script1, script0, text, ischecked)};
    function jmolButton(script, text) {Jmol.jmolButton(jmolApplet0, script,text)};
    function jmolHtml(s) { document.write(s) };
    function jmolBr() { jmolHtml("<br />") };
    function jmolMenu(a) {Jmol.jmolMenu(jmolApplet0, a)};
    function jmolScript(cmd) {Jmol.script(jmolApplet0, cmd)};
    function jmolScriptWait(cmd) {Jmol.scriptWait(jmolApplet0, cmd)};
</script>

                        <button type="button" id="neighborhood" class="btn">Show neighborhood</button>
                        <button type="button" id="stereo" class="btn">Stereo</button>
                        <br>
                        Coloring options: <select id="colorOPT"> 
                            <option value="Default" selected>Default</option> 
                            <option value="CPK">CPK</option> 
                            <option value="RSR">Real Space R (RSR)</option>
                            <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                        </select>
                        <div class='showRSR' style="display:none">
                    <svg height="30" width="340">
                        <defs>
                            <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset=" 1%" style= " stop-color: #0d0887; stop-opacity:1 " />
                                <stop offset=" 2%" style= " stop-color: #110889; stop-opacity:1 " />
                                <stop offset=" 3%" style= " stop-color: #17078b; stop-opacity:1 " />
                                <stop offset=" 4%" style= " stop-color: #1b078d; stop-opacity:1 " />
                                <stop offset=" 5%" style= " stop-color: #20068f; stop-opacity:1 " />
                                <stop offset=" 6%" style= " stop-color: #240691; stop-opacity:1 " />
                                <stop offset=" 7%" style= " stop-color: #2a0693; stop-opacity:1 " />
                                <stop offset=" 8%" style= " stop-color: #300596; stop-opacity:1 " />
                                <stop offset=" 9%" style= " stop-color: #340597; stop-opacity:1 " />
                                <stop offset=" 10%" style= " stop-color: #3a049a; stop-opacity:1 " />
                                <stop offset=" 11%" style= " stop-color: #3d049b; stop-opacity:1 " />
                                <stop offset=" 12%" style= " stop-color: #43049e; stop-opacity:1 " />
                                <stop offset=" 13%" style= " stop-color: #4903a0; stop-opacity:1 " />
                                <stop offset=" 14%" style= " stop-color: #4b03a1; stop-opacity:1 " />
                                <stop offset=" 15%" style= " stop-color: #5003a2; stop-opacity:1 " />
                                <stop offset=" 16%" style= " stop-color: #5303a2; stop-opacity:1 " />
                                <stop offset=" 17%" style= " stop-color: #5803a3; stop-opacity:1 " />
                                <stop offset=" 18%" style= " stop-color: #5c03a3; stop-opacity:1 " />
                                <stop offset=" 19%" style= " stop-color: #6103a4; stop-opacity:1 " />
                                <stop offset=" 20%" style= " stop-color: #6603a5; stop-opacity:1 " />
                                <stop offset=" 21%" style= " stop-color: #6903a5; stop-opacity:1 " />
                                <stop offset=" 22%" style= " stop-color: #6e03a6; stop-opacity:1 " />
                                <stop offset=" 23%" style= " stop-color: #7103a6; stop-opacity:1 " />
                                <stop offset=" 24%" style= " stop-color: #7603a7; stop-opacity:1 " />
                                <stop offset=" 25%" style= " stop-color: #7b03a8; stop-opacity:1 " />
                                <stop offset=" 26%" style= " stop-color: #7d03a8; stop-opacity:1 " />
                                <stop offset=" 27%" style= " stop-color: #8106a6; stop-opacity:1 " />
                                <stop offset=" 28%" style= " stop-color: #8408a5; stop-opacity:1 " />
                                <stop offset=" 29%" style= " stop-color: #880ba4; stop-opacity:1 " />
                                <stop offset=" 30%" style= " stop-color: #8a0da2; stop-opacity:1 " />
                                <stop offset=" 31%" style= " stop-color: #8e10a1; stop-opacity:1 " />
                                <stop offset=" 32%" style= " stop-color: #93139f; stop-opacity:1 " />
                                <stop offset=" 33%" style= " stop-color: #95149e; stop-opacity:1 " />
                                <stop offset=" 34%" style= " stop-color: #99179c; stop-opacity:1 " />
                                <stop offset=" 35%" style= " stop-color: #9c199b; stop-opacity:1 " />
                                <stop offset=" 36%" style= " stop-color: #a01c99; stop-opacity:1 " />
                                <stop offset=" 37%" style= " stop-color: #a41f98; stop-opacity:1 " />
                                <stop offset=" 38%" style= " stop-color: #a72197; stop-opacity:1 " />
                                <stop offset=" 39%" style= " stop-color: #a92395; stop-opacity:1 " />
                                <stop offset=" 40%" style= " stop-color: #ac2693; stop-opacity:1 " />
                                <stop offset=" 41%" style= " stop-color: #af2990; stop-opacity:1 " />
                                <stop offset=" 42%" style= " stop-color: #b32d8d; stop-opacity:1 " />
                                <stop offset=" 43%" style= " stop-color: #b52f8b; stop-opacity:1 " />
                                <stop offset=" 44%" style= " stop-color: #b83388; stop-opacity:1 " />
                                <stop offset=" 45%" style= " stop-color: #bb3587; stop-opacity:1 " />
                                <stop offset=" 46%" style= " stop-color: #be3984; stop-opacity:1 " />
                                <stop offset=" 47%" style= " stop-color: #c13b82; stop-opacity:1 " />
                                <stop offset=" 48%" style= " stop-color: #c43f7f; stop-opacity:1 " />
                                <stop offset=" 49%" style= " stop-color: #c8427c; stop-opacity:1 " />
                                <stop offset=" 50%" style= " stop-color: #ca457a; stop-opacity:1 " />
                                <stop offset=" 51%" style= " stop-color: #cc4778; stop-opacity:1 " />
                                <stop offset=" 52%" style= " stop-color: #cd4976; stop-opacity:1 " />
                                <stop offset=" 53%" style= " stop-color: #d04d74; stop-opacity:1 " />
                                <stop offset=" 54%" style= " stop-color: #d25071; stop-opacity:1 " />
                                <stop offset=" 55%" style= " stop-color: #d4536f; stop-opacity:1 " />
                                <stop offset=" 56%" style= " stop-color: #d6566d; stop-opacity:1 " />
                                <stop offset=" 57%" style= " stop-color: #d8596b; stop-opacity:1 " />
                                <stop offset=" 58%" style= " stop-color: #da5c68; stop-opacity:1 " />
                                <stop offset=" 59%" style= " stop-color: #dc5e67; stop-opacity:1 " />
                                <stop offset=" 60%" style= " stop-color: #df6264; stop-opacity:1 " />
                                <stop offset=" 61%" style= " stop-color: #e16561; stop-opacity:1 " />
                                <stop offset=" 62%" style= " stop-color: #e36860; stop-opacity:1 " />
                                <stop offset=" 63%" style= " stop-color: #e56b5d; stop-opacity:1 " />
                                <stop offset=" 64%" style= " stop-color: #e66c5c; stop-opacity:1 " />
                                <stop offset=" 65%" style= " stop-color: #e87059; stop-opacity:1 " />
                                <stop offset=" 66%" style= " stop-color: #e97556; stop-opacity:1 " />
                                <stop offset=" 67%" style= " stop-color: #eb7755; stop-opacity:1 " />
                                <stop offset=" 68%" style= " stop-color: #ed7b52; stop-opacity:1 " />
                                <stop offset=" 69%" style= " stop-color: #ee7e50; stop-opacity:1 " />
                                <stop offset=" 70%" style= " stop-color: #f0824d; stop-opacity:1 " />
                                <stop offset=" 71%" style= " stop-color: #f2864a; stop-opacity:1 " />
                                <stop offset=" 72%" style= " stop-color: #f38948; stop-opacity:1 " />
                                <stop offset=" 73%" style= " stop-color: #f58d46; stop-opacity:1 " />
                                <stop offset=" 74%" style= " stop-color: #f69044; stop-opacity:1 " />
                                <stop offset=" 75%" style= " stop-color: #f89441; stop-opacity:1 " />
                                <stop offset=" 76%" style= " stop-color: #f89540; stop-opacity:1 " />
                                <stop offset=" 77%" style= " stop-color: #f99a3e; stop-opacity:1 " />
                                <stop offset=" 78%" style= " stop-color: #f99e3c; stop-opacity:1 " />
                                <stop offset=" 79%" style= " stop-color: #f9a13a; stop-opacity:1 " />
                                <stop offset=" 80%" style= " stop-color: #faa638; stop-opacity:1 " />
                                <stop offset=" 81%" style= " stop-color: #faa936; stop-opacity:1 " />
                                <stop offset=" 82%" style= " stop-color: #fbad34; stop-opacity:1 " />
                                <stop offset=" 83%" style= " stop-color: #fbb131; stop-opacity:1 " />
                                <stop offset=" 84%" style= " stop-color: #fbb430; stop-opacity:1 " />
                                <stop offset=" 85%" style= " stop-color: #fcb92d; stop-opacity:1 " />
                                <stop offset=" 86%" style= " stop-color: #fcbc2c; stop-opacity:1 " />
                                <stop offset=" 87%" style= " stop-color: #fdc02a; stop-opacity:1 " />
                                <stop offset=" 88%" style= " stop-color: #fdc328; stop-opacity:1 " />
                                <stop offset=" 89%" style= " stop-color: #fcc728; stop-opacity:1 " />
                                <stop offset=" 90%" style= " stop-color: #fbcc27; stop-opacity:1 " />
                                <stop offset=" 91%" style= " stop-color: #fad026; stop-opacity:1 " />
                                <stop offset=" 92%" style= " stop-color: #f9d526; stop-opacity:1 " />
                                <stop offset=" 93%" style= " stop-color: #f8d925; stop-opacity:1 " />
                                <stop offset=" 94%" style= " stop-color: #f7de25; stop-opacity:1 " />
                                <stop offset=" 95%" style= " stop-color: #f5e324; stop-opacity:1 " />
                                <stop offset=" 96%" style= " stop-color: #f4e723; stop-opacity:1 " />
                                <stop offset=" 97%" style= " stop-color: #f3ec23; stop-opacity:1 " />
                                <stop offset=" 98%" style= " stop-color: #f2f022; stop-opacity:1 " />
                                <stop offset=" 99%" style= " stop-color: #f1f521; stop-opacity:1 " />
                                <stop offset=" 100%" style= " stop-color: #f0f921; stop-opacity:1 " />
                            </linearGradient>
                        </defs>
                    <rect x="0" y="0" width="300" height="15" fill="url(#grad3)"  />
                    <text x="0" y="30" font-family="sans-serif" font-size="12px" fill="black">0.0</text>
                    <text x="70" y="30" font-family="sans-serif" font-size="12px" fill="black">RSR Scale truncated at 0.5</text>
                    <text x="285" y="30" font-family="sans-serif" font-size="12px" fill="black">0.5</text>
                    </svg>
                </div>

                <div class='showRSRZ' style="display:none">
                    <svg height="45" width="340">
                        <defs>
                        <text x="120" y="0" font-family="sans-serif" font-size="12px" fill="black">RSRZ Scale</text>
                            <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="25%" style="stop-color:green;stop-opacity:1" />
                                <stop offset="25%" style="stop-color:yellow;stop-opacity:1" />
                                <stop offset="50%" style="stop-color:yellow;stop-opacity:1" />
                                <stop offset="50%" style="stop-color:orange;stop-opacity:1" />
                                <stop offset="75%" style="stop-color:orange;stop-opacity:1" />
                                <stop offset="75%" style="stop-color:red;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:red;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    <rect x="0" y="0" width="300" height="15" fill="url(#grad2)"  />
                    <text x="72" y="30" font-family="sans-serif" font-size="12px" fill="black">1</text>
                    <text x="147" y="30" font-family="sans-serif" font-size="12px" fill="black">2</text>
                    <text x="223" y="30" font-family="sans-serif" font-size="12px" fill="black">3</text>
                    <text x="120" y="45" font-family="sans-serif" font-size="12px" fill="black">RSRZ Scale</text>
                    </svg>
                </div>
                        <label><input type="checkbox" id="showNtNums">Show numbers</label>
                    </div>
                </div>

                <div class="row span6">
                    <div id="about-selection" class="alert-message block-message info hide span6 width-320-px"></div>
                </div>

                <div class="row span6">
                    <div id="related-structures" class="alert-message block-message info span6 width-320-px">
                        <h4>Related 2D Diagrams</h4>
                        <?php if (count($related_pdbs) == 0): ?>
                          <strong>None found</strong>
                        <?php else: ?>
                          <?php foreach($related_pdbs as $pdb): ?>
                            <a href="<?=$baseurl?>pdb/<?=$pdb?>/2d"><?=$pdb?></a>
                          <?php endforeach; ?>
                          <br>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

          </div>

        </div>
      </div>

        <div id="help-modal" class='modal hide fade' tabindex="-1" role="dialog">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="help-modal" aria-hidden="true">×</button>
            <h3>2D structures</h3>
          </div>

          <div class="modal-body">

            <p>
              Shown here is a <strong>circular diagram</strong> generated using the 
              <a href="<?=$this->config->item('home_url')?>/main/software/fr3d/">FR3D</a> annotations 
              for <?=$pdb_id?>. The black circle represents the annotated chains. For 
              some structures an <strong>airport diagram</strong> is provided. To draw it click the 
              airport button. Structures without one have a disabled button.
            </p>

            <h4>Interactions</h4>
            <p>
              Interactions are displayed as arcs connecting nucleotides,
              by default only cWW interactions are displayed. The 
              <strong>dotted arcs</strong>
              are long range interactions, there include things like 
              pseudoknots. To display other interactions use the interaction 
              controls to the right. Clicking on a interaction will toggle 
              displaying all interactions of that family and ones near that 
              family. So clicking on tWW shows all tWW and ntWW. 
            </p>

            <h4>Motifs</h4>
            <p>
                In airport mode motifs are displayed by default. To hide them click the motif button.
                Internal loops are shown in a green box, hairpins in blue and 3-way junctions in yellow.
                Currently we only extract 3-way junctions, in the future this may change.
            </p>

            <h4>Modes</h4>
            <p>
              In the default <strong>select mode</strong>, click and drag to create a selection box. 
              All nucleotides within the selection box will be displayed in a jmol 
              window to the right. The selection box is dragable and resizeable. 
            </p>

            <p>
              In <strong>click mode</strong>, click on a interaction or nucleotide to display it
              in 3D. In addition, some information about the clicked element will 
              be displayed below the jmol window. To switch to the click mode use 
              the selection mode control. Hovering over an interaction will 
              highlight it and the nucleotides that form it. Hovering over a 
              nucleotide will highlight it as well as all intereracations it forms.
            </p>

            <a class="btn primary" href="<?=$this->config->item('home_url')?>/main/interacting-with-2d-structures" target="_blank">More details</a>
          </div>
        </div>

<script type='text/javascript'>
    NTS = <?=$nts?>;
    LONG = <?=$long_range?>;
    INTERACTION_URL = "<?=$this->config->item('home_url')?>/rna3dhub/pdb/<?=$pdb_id?>/interactions/fr3d/basepairs/csv";
    LOOP_URL = "<?=$this->config->item('home_url')?>/rna3dhub/loops/download/<?=$pdb_id?>";

    $('#chosen').chosen().change(function(){
        window.location.href = "<?=$baseurl?>pdb/" + $(this).val();
    });

    if (!NTS[0].nts.length) {
        $("#rna-2d").append("<h3 align='center'>Could not generate 2D diagram. " +
            "Check back later</h3>");
    }
</script>
