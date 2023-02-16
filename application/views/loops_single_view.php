    <div class="container-large loops_single_view">

      <div class="content">
        <div class="page-header">
          <h1><?=$id?></h1>
        </div>
        <div class="row">

            <div class="loop-tab">
                <ul class="tabs" data-tabs="tabs">

                <?php if ($show_similar): ?>
                    <li><a href="<?php echo str_replace('similar', '', current_url());?>">General information</a></li>
                    <li class="active"><a href="#">Similar loops</a></li>
                <?php else: ?>
                    <li class="active"><a href="#general">General information</a></li>
		    <!--
                    <li><a href="#history">Motif history</a></li>
                    <li><a href="#rnastar">RNASTAR</a></li>
		    -->
                <?php endif; ?>

                </ul>
            </div>

            <div class="tab-content span50p" id='left_content'>

                <?php if ($show_similar): ?>

                <div class="tab-pane" id='general'>
                </div>

                <?php else: ?>
                <div class="tab-pane active" id='general'>
                    <div class="row">
                        <div class="span3 well">
                            <h4>3D structure</h4>
                            <dl>
                                <dt>PDB id</dt>
                                <dd><?=$pdb?> (<i>explore in</i> <?=$rna3dhub_link?> <i>or</i> <?=$pdb_link?>)</dd>
                                <dt>Description</dt>
                                <dd><?=$pdb_desc?></dd>
                                <dt>Experimental method</dt>
                                <dd><?=$pdb_exptechnique?></dd>
                                <dt>Resolution</dt>
                                <dd><?=$pdb_resolution?></dd>
                                <!-- <dt>Representative set classes</dt>
                                <dd><?=$nr_classes?></dd> remove representative set classes section-->
                            </dl>
                        </div>
                        <div class="span4 well">
                            <h4>Loop</h4>
                            <dl>
                                <dt>Sequence</dt>
                                <dd><?=$sequence?></dd>
                                <dt>Length</dt>
                                <dd><?=$length?> nucleotides</dd>
                                <dt>Bulged bases</dt>
                                <dd><?=$bulges?></dd>
                                <dt>QA status</dt>
                                <dd><?=$qa?></dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="span3 well">
                            <dl>
                                <dt>Detailed Annotation</dt>
                                <dd><?=$annotation_1?></dd>
                                <dt>Broad Annotation</dt>
                                <dd><?=$annotation_2?></dd>
                                <dt>Motif group</dt>
                                <dd><?=$motif_url?></dd>
                                <dt>Basepair signature</dt>
                                <dd><?=$bp_signature?></dd>
                                <dt>Number of instances in this motif group</dt>
                                <dd><?=$motif_instances?></dd>
                            </dl>
                        </div>
                        <div class="span4 well">
                            <h4><a href="https://www.bgsu.edu/research/rna/help/rna-3d-hub-help/unit-ids.html" target="_blank">Unit IDs</a></h4>
                            <p><?=$unit_ids?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="span4 well">
                            <h4>Nearby chains</h4>
                            <?php
                                if (count($proteins) > 0) {
                                    echo '<dl>';
                                    foreach($proteins as $chain => $desc) {
                                        echo "<dt>Chain $chain</dt>";
                                        echo "<dd>{$desc['description']}</dd>";
                                        //echo "<dd>{$desc['description']} (Uniprot: {$desc['uniprot']})</dd>";
                                    }
                                    echo '</dl>';
                                } else {
                                    echo "No other chains within 10&Aring;";
                                }
                                
                                #Consensus naming
                                if (count($rna_chains) > 0) {
                                    echo '<dl>';
                                    echo "Test data is present.";
                                    foreach($rna_chains as $chain => $prop) {
                                        echo "<dt>Chain $chain</dt>";
                                        echo "<dd>{$prop['property']}</dd>";
                                        //echo "<dd>{$desc['description']} (Uniprot: {$desc['uniprot']})</dd>";
                                    }
                                    echo '</dl>';
                                } else {
                                    echo "No other chains within 10&Aring;";
                                }
                            ?>
                        </div>
                    </div>

                </div>
                <?php endif; ?>


                <?php if ($show_similar): ?>
                <div class="tab-pane active" id='similar'>
                    <?=$table?>
                </div>
                <?php else: ?>
                <div class="tab-pane" id='similar'>
                </div>
                <?php endif; ?>


		<!--HERE-->
                <div class="tab-pane" id='history'>
                    Coming soon
                </div>

                <div class="tab-pane" id='rnastar'>
                    Coming soon
                </div> <!--HERE-->
 

            </div>

            <div class="spanjmol well" id="jmol" >
<script>
    jmol_isReady = function(applet) {
        // initialize the plugin
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            colorOption: 'colorOPT',
            showNumbersId: 'showNtNums',
        });
        // run the plugin
        $('.jmolInline').first().jmolToggle();
    };

    var Info = {
        width: 565,
        height: 340,
        debug: false,
        color: '#f5f5f5',
        addSelectionOptions: false,
        use: 'HTML5',
        j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
        readyFunction: jmol_isReady,
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
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <button type="button" id="stereo" class="btn">Stereo</button>
                <br>
                Coloring options: <select id="colorOPT"> 
                    <option value="Default" selected>Default</option>
                    <option value="CPK" >CPK</option> 
                    <option value="RSR" >Real Space R (RSR)</option>
                    <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
                <br>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
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
                <div id='checkboxes'>
                    <label><input type='checkbox' class='jmolInline' data-coord='<?=$id?>' data-quality='<?=$id?>'><?=$id?></label>
                </div>
           </div>

        </div>
      </div>


      <script>
        $('#sortable').tablesorter();

        var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','absolute')
                  .css('left',offset_left)
                  .css('top',offset_top);


        $('.jmolTools-loop-pairs').click(function(){

            // get two loops that need to be shown
            $this = $(this);
            var loop_ids = $this.data('coord').split(':');
            // clear jmol window
            jmolScript('zap;');
            // destroy all previously created temporary elements
            var tempClass = 'jmolTools-temp-elems';
            $('#checkboxes').children().remove();
            $.jmolTools.numModels = 0;
            // create new temporary elements

            // get loop1 from the alignment of loop1 and loop2
            // if there is no alignment of loop1 and loop2, then
            // get loop1 from the alignment of loop2 and loop1
            $('#checkboxes').append($('<input type="checkbox">')
                .data('coord', '@'+loop_ids[0]+':'+loop_ids[1])
                .attr('id', 'l0')
                .addClass(tempClass)
            ).append('<label for="l0">' + loop_ids[0] + '</label>')
             .append('<br>')
              // get loop2 from the alignment of loop1 and loop2
             .append($('<input type="checkbox">')
                .data('coord', loop_ids[0]+':@'+loop_ids[1])
                .attr('id', 'l1')
                .addClass(tempClass)
            ).append('<label for="l1">' + loop_ids[1] + ' (colored black)</label>');

            // reset the state of the system
            $.jmolTools.numModels = 0;
            $.jmolTools.stereo = false;
            $.jmolTools.neighborhood = false;
            $.jmolTools.models = {};

            // unbind all events
            $('#stereo').unbind();
            $('#neighborhood').unbind();
            $('#showNtNums').unbind();

            // initialize the plugin on the temporary elements
            $('.'+tempClass).jmolTools({
                showStereoId: 'stereo',
                showNeighborhoodId: 'neighborhood',
                showNumbersId: 'showNtNums'
            });

            // show the loops
            $('#l0').ajaxStop(function() {
                $('#l1').ajaxStop(function() {
                    jmolScript('center 1.0;zoom {1.1} 0;select 2.1;color black;');
                });
                $('#l1').jmolToggle();
                $('#l0').unbind('ajaxStop');
            });
            $('#l0').jmolToggle();

        });

    </script>
