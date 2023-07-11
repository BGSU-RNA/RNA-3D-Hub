    <div class="container-large pdb_interactions_view">

      <div class="content">
        <div class="page-header">
          <h1><?=strtoupper($pdb_id)?>
          <small><?=strtoupper($method)?> <?=$interaction_type?> Pairwise Interaction Annotations</small>
          <small><a class="btn pull-right success" href="<?=$current_url?>/csv">Download</a></small>
          </h1>
        </div>

        <!-- navigation -->
        <div class="row">
          <div class="span100p">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Loops</a></li>
                <li class="dropdown active" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Interactions</a>
                  <ul class="dropdown-menu">
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basepairs">Base-pair</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/stacking">Base stacking</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basephosphate">Base-phosphate</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/baseribose">Base-ribose</a></li>
                    <!-- not quite ready yet
						        <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/oxygenStacking">Oxygen stacking</a></li>
                    -->
                    <!-- Commented out since we don't have aa-nt interactions yet
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/baseaa">Base-amino acids</a></li> -->
                    <li class="divider"></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/all">All interactions</a></li>
                  </ul>
                </li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/2d">2D Diagram</a></li>
            </ul>
          </div>
        </div>
        <!-- end navigation -->

        <div class="row">

          <!-- annotations -->
          <div class="span8" id="left_content">
            <pre><?=$table?></pre>
          </div>
          <!-- end annotations -->

          <div class="span500px well" id="jmol">
            <script>
              var Info = {
                width: 565,
                height: 340,
                debug: false,
                color: 'white',
                addSelectionOptions: false,
                use: 'HTML5',
                j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
                disableInitialConsole: true,
                readyFunction: function(){
                  $('pre a').first().click();
                }
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
            <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
            <input type="button" class="btn" id="neighborhood" value="Show neighborhood">
            <button type="button" id="stereo" class="btn">Stereo</button>
            <br>
            Coloring options: <select id="colorOPT">
                    <option value="Default" selected>Default</option>
                    <option value="CPK">CPK</option>
                    <option value="RSR" >Real Space R (RSR)</option>
                    <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
                <br>
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
          </div>
        </div>
      </div>

      <script>
        $('pre').on('click', 'span', function(){
          // hide all previously displayed popovers
          $('.popover-displayed').removeClass('popover-displayed')
                                 .popover('hide')
                                 .unbind();
          var a = $(this);
          var unit_id = a.html().trim();

          var content = '<a href="<?=$baseurl?>/unitid/describe/'
                        + unit_id + '">Details</a>' +
                        '&nbsp;&nbsp;<?=anchor("unitid", "Nomenclature")?>';
          a.popover({
            offset: 10,
            content: function(){return content;},
            title: function(){return 'Unit id ' + unit_id;},
            delayOut: 1200,
            html: true,
            animate: false,
            placement:'right'
          });
          a.popover('show');
          a.addClass('popover-displayed');
        });

        $('.jmolInline').click(function(){
          var jmolApp = $('#jmolApplet0');
          var jmolDiv = $('#jmol');
          $this = $(this);

          // clear jmol window
          jmolScript('zap;');

          $('a.current').removeClass('current').addClass('viewed');
          $this.addClass('current');

          // reset the state of the system
          $.jmolTools.numModels = 0;
          $.jmolTools.stereo = false;
          $.jmolTools.neighborhood = false;
          $('#neighborhood').val('Show neighborhood');
          $.jmolTools.models = {};

          // unbind all events
          $('#stereo').unbind();
          $('#neighborhood').unbind();
          $('#showNtNums').unbind();
          $('#colorOPT').unbind();

          var data_coord = $this.prev().html() + ',' + $this.next().html();
          data_coord = data_coord.replace(/\s+/g, '');
          console.log(data_coord);
          $('#tempJmolToolsObj').remove();
          $('body').append("<input type='radio' id='tempJmolToolsObj' data-coord='" + data_coord + "' data-quality='" + data_coord + "'>");
          $('#tempJmolToolsObj').hide();
          $('#tempJmolToolsObj').jmolTools({
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            showStereoId: 'stereo',
            colorOption: 'colorOPT'
          }).jmolToggle();
        });

        // position jmol
        var offset_left = $('#left_content').offset().left + 470; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top);
      </script>
