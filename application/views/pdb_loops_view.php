    <style>
        #grad1 {
            height: 15px;
            width: 300px;
            background: linear-gradient(to right, red , blue);
        }
    </style>


    <div class="container pdb_loops_view">

      <div class="content">
        <div class="page-header">
          <h1><?=strtoupper($pdb_id)?>
          <small>Internal, hairpin and 3-way junction loop motifs</small>
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
          <div class="span9">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li class="active"><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Motifs</a></li>
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
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/2d">2D Diagram</a></li>
            </ul>
          </div>
          <a class="btn primary pull-right" href="<?=$baseurl?>loops/download/<?=$pdb_id?>">Download loops</a>
        </div>
        <!-- end navigation -->


        <div class="row">

        <?php if ($valid): ?>

          <!-- motifs -->
          <div class="span8 well">

            <ul class="pills" data-tabs="tabs">
                <li class="active"><a href="#ils"><?=$counts['IL']?> Internal loops</a></li>
                <li><a href="#hls"><?=$counts['HL']?> Hairpin loops</a></li>
                <li><a href="#jls"><?=$counts['J3']?> Junction loops</a></li>
            </ul>

            <div class="tab-content" id="my-tab-content">
                <div class="tab-pane active" id="ils">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['IL']['valid']?>
                    </div>
                    <h3>Disqualified loops</h3>
                    <div class="modified block">
                        <?=$loops['IL']['invalid']?>
                    </div>
                </div>

                <div class="tab-pane" id="hls">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['HL']['valid']?>
                    </div>
                    <h3>Disqualified loops</h3>
                    <div class="modified block">
                        <?=$loops['HL']['invalid']?>
                    </div>
                </div>

                <div class="tab-pane" id="jls">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['J3']['valid']?>
                    </div>
                    <h3>Disqualified loops</h3>
                    <div class="modified block">
                        <?=$loops['J3']['invalid']?>
                    </div>
                </div>

            </div> <!-- end of tab-content -->

          </div>
          <!-- end of motifs -->

          <?php if ($counts['IL'] != 0 or $counts['HL'] != 0 or $counts['J3'] != 0): ?>

          <div class="span6 well" id="jmol" >
<script>
    jmol_isReady = function(applet) {
        // initialize the plugin
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            colorByRSRZ: 'colorRSRZ',
            colorByRSR: 'colorRSR',
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
                    <option value="RSR" >Real Space R (RSR)</option>
                    <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
                <br>
                <br>
                <br>
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
                <!--RSR scale (from 0 to 1)
                <div id="grad1" align = 'centre'></div>--> 
                
                <!--<div>
                    <br><br><input type="radio" id="colorRSRZ" name="color"> <label for="colorByRSRZ">Colour by RSRZ</label>

                    <br><br><input type="radio" id="colorRSR" name="color"> <label for="colorbyRSR">Colour by RSR</label><br>

                </div>-->

           <?php endif; ?>

        <?php else: ?>
          <pre class="span8"><?=$message?></pre>
        <?php endif; ?>
        </div> <!-- end of row -->

      </div>



    <script>
        $(function () {
            $('.tabs').tabs()
        })

        $('.twipsy').twipsy();

        $('table').tablesorter();

        $('#chosen').chosen().change(function(){
            window.location.href = "<?=$baseurl?>pdb/" + $(this).val() + '/motifs';
        });

    </script>
