
   <!-- RNA2D -->
    <script type="text/javascript" src="<?=$baseurl?>js/sizzle.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/d3.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/jquery.rna2d.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d-controls.js"></script>

    <div class="container-large pdb-2d-view">

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
          <div class="span8">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Loops</a></li>
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
        </div>        
              <label><input type="checkbox" id="Chains">Lsu</label>
              <label><input type="checkbox" id="Chains">SSU</label>
              <label><input type="checkbox" id="Chains">mRNA</label>
              <label><input type="checkbox" id="Chains">tRNA</label>
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
                <div class="row spanjmol">
                    <div id="jmol" class="well spanjmol ">
<script>
    var Info = {
        width: 400,
        height: 300,
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
                        <br>
                        <div class='showRSR' style="display:none">
                    <svg height="30" width="340">
                        <defs>
                            <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="10%" style="stop-color:#0d0887; stop-opacity:1" />
                                <stop offset="20%" style="stop-color:#6603a5; stop-opacity:1" />
                                <stop offset="30%" style="stop-color:#8a0da2; stop-opacity:1" />
                                <stop offset="40%" style="stop-color:#ac2693; stop-opacity:1" />
                                <stop offset="50%" style="stop-color:#ca457a; stop-opacity:1" />
                                <stop offset="60%" style="stop-color:#df6264; stop-opacity:1" />
                                <stop offset="70%" style="stop-color:#f0824d; stop-opacity:1" />
                                <stop offset="80%" style="stop-color:#faa638; stop-opacity:1" />
                                <stop offset="90%" style="stop-color:#fbcc27; stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#f0f921; stop-opacity:1" />
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

                <div class="row spanjmol">
                    <div id="about-selection" class="alert-message block-message info hide spanjmol width-500-px"></div>
                </div>

                <div class="row spanjmol">
                    <div id="related-structures" class="alert-message block-message info spanjmol width-500-px">
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
        
  <!--  <script type='text/javascript'>
           console.log("Hello World");
           $chain.forEach(function($chain)) {
           console.log($chain);
          } 
  -->
 </script>
<script type='text/javascript'>
    NTS = <?=$nts?>;
    console.log("Hello World After NTS");
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