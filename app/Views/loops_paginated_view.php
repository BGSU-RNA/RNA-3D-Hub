    <div class="container loops_paginated_view">

      <div class="content">
        <div class="page-header">
            <div class="row">
                <div class="span8">
                <h1><?php echo $title;?></h1>
                </div>
            <div class="span5">
            <ul class="tabs" data-tabs="tabs">

                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Quality Assurance</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?=$baseurl?>loops/view_all/valid/<?=$motif_type?>">Valid</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/missing/<?=$motif_type?>">Missing</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/modified/<?=$motif_type?>">Modified</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/incomplete/<?=$motif_type?>">Incomplete</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/abnormal/<?=$motif_type?>">Composite</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/complementary/<?=$motif_type?>">Self-complementary</a></li>
                    </ul>
                </li>

                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Loop types</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?=$baseurl?>loops/view_all/<?=$type?>/IL">Internal loops</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/<?=$type?>/HL">Hairpin loops</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/<?=$type?>/J3">Junction loops</a></li>
                    </ul>
                </li>
            </ul>
            </div>
            </div>
        </div>
        <div class="row">

          <div class="span9" id='left_content'>
            <?php echo $table;?>
            <?php echo $this->pagination->create_links(); ?>
          </div>
        
          <div class="span6" id="jmol">
              
            <!--
            <div class="block jmolheight">
                  <script type="text/javascript">
                    jmolInitialize(" /jmol");
                    jmolSetAppletColor("#ffffff");
                    jmolApplet(340, "javascript appletLoaded()");
                  </script>
            </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
              <br><br>
            -->  
            <script>
            jmol_isReady = function(applet) {
                // initialize the plugin
                $('.jmolInline').jmolTools({
                    showStereoId: 'stereo',
                    showNeighborhoodId: 'neighborhood',
                    showNumbersId: 'showNtNums',
                    colorOption: 'colorOPT',
                    showNextId: 'next',
                    showPrevId: 'prev'
                });
                // run the plugin
                $('.jmolInline').first().jmolToggle();
            };

            var Info = {
                width: 340,
                height: 340,
                debug: false,
                color: '#f5f5f5',
                addSelectionOptions: false,
                use: 'HTML5',
                j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
                readyFunction: jmol_isReady,
                disableInitialConsole: true,
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
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                Coloring options: <select id="colorOPT">
                    <option value="Default" selected>Default</option>
                    <option value="CPK">CPK</option>
                    <option value="RSR">Real Space R (RSR)</option>
                    <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>&nbsp
                <button type="button" id="stereo" class="btn">Stereo</button>
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
              </br>
              <?php if ($type == 'modified'): ?>
               <p>
               Modified nucleotides are shown in pink when the 'Show neighborhood' button is clicked
               </p>
              <?php elseif ($type == 'missing'): ?>
               <p>
               Some nucleotides may either be completely missing in some 3D structures, or only
               their phosphate backbone is described in the PDB file. RNA 3D motifs with such nucleotides
               are excluded from further analysis.
               </p>
              <?php elseif ($type == 'complementary'): ?>
               <p>
                Some internal loops are self-complementary, which may indicate that they are, in fact,
                simply normal watson-crick helices and shouldn't be considered as internal loops.
               </p>
              <?php endif; ?>
          
            </div>

        </div>
      </div>

      <script>

      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: '<?=$baseurl?>ajax/get_loop_coordinates',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums'
	    		});
			}, 200);
      	}

        $(".pdb").click(LookUpPDBInfo);

        $('#jmol').css('position','fixed');
        var offset_left = $('#left_content').offset().left + 560; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('left',offset_left);
        $('#jmol').css('top', offset_top);

      </script>
