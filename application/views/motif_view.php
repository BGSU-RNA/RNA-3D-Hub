    <div class="container-large motifview">

      <div class="content">

        <div class="page-header">
          <div class="row">
            <div class="span16">
              <h1>Motif <?=$title?>
                <small>Version <?=$title?> of this group appears in releases <?=$release_id?> to <?=$last_release_id?></small>
                <small class="pull-right">
                  <ul class="tabs">
                  <li class="dropdown" data-dropdown="dropdown">
                    <a href="#" class="dropdown-toggle">Download</a>
                    <ul class="dropdown-menu">
                      <li><a href="<?=$current_url?>/csv" download="<?=$title?>.csv">Csv</a></li>
                      <li><a href="<?=$current_url?>/json" download="<?=$title?>.json">Json</a></li>
                    </ul>
                  </li>
                  </ul>
                </small>
              </h1>
            </div>
          </div>
        </div> <!-- end of page-header -->

    <!--    <div class="row">-->
            <div class="row span">
                <div class="row span1140 interactions resizable" id="interactions">

                    <ul class="tabs" data-tabs="tabs">
                        <li class="active"><a href="#int">Pairwise interactions</a></li>
                        <li><a href="#variants">Sequence variants</a></li>
                        <!-- <li><a href="#similar">Similar motifs</a></li> -->
                        <li><a href="#history">History</a></li>
                    </ul>

                    <div class="tab-content">

                        <div class="tab-pane active" id='int'>
                          <span class="muted">
                              #D - ordering by discrepancy relative to the exemplar,
                              #S - ordering by similarity (same as in the heat map).
                              <a href="<?=$this->config->item('home_url')?>/main/rna-3d-hub-help/" target="_blank">More</a>
                          </span>

                            <?php echo $table;?>
                        </div>

                        <div class="tab-pane" id='variants'>
                            <h4>3D structures</h4>
                            <div class="row span100p">
                                <div class="span45p">
                                    <h5>Complete motif including flanking bases</h5>
                                    <?=$sequence_variation['complete']?>
                                </div>
                                <div class="span45p">
                                    <h5>Non-Watson-Crick part of the motif</h5>
                                    <?=$sequence_variation['nwc']?>
                                </div>
                            </div>

<?/*
                            <h4>Sequence databases</h4>
                            <div class="row block-div span11">
                                Coming soon.
                            </div>
*/?>
                        </div>

<?/*
                        <div class="tab-pane" id='similar'>
                            <div>
                                <?=$similar_motifs?>
                            </div>
                        </div>
*/?>

                        <div class="tab-pane" id='history'>
                            <h4>Release history</h4>
                            <div>
                                <?=$motif_release_history?>
                            </div>
                            <h4>Parent motifs</h4>
                            <div>
                                <?=$history['parents']?>
                            </div>
                            <h4>Children motifs</h4>
                                <?=$history['children']?>
                        </div>

                    </div>
                </div>
            <!--</div>-->
	</div>
            <div class="row motiftop">
            	<div class="span4">
                	<div class="block-div resizable" id="annotation-panel">
                        	<dl>
                        	    <dt>Annotations</dt>
                            	    <dd id="common_name2" class="edit"><?
                            	    if ($annotation_test) { foreach($annotation_test as $key => $value) {
                                	echo "<li>" . $key . " ". "(". $value . ")". "</li>";
                                  	}
                            	    }
                            	    ?></dd>
                          	    <!--<dt>Description:</dt>
                            		<#?php if ($annotation['common_name']): ?>
                           		 <dd id="common_name" class="edit"><?=$annotation['common_name']?></dd>
                          		  <#?php else: ?>
                          		  <dd id="common_name" class="edit">No description added yet.</dd>
                      		          <#?php endif; ?>-->
                        		  <dt>Basepair signature</dt>
                            		  <dd id="bp_signature" class="edit"><?=$annotation['bp_signature']?></dd>
                     		          <!--<dt>Free text annotation:</dt>
                     		          <#?php if ($annotation['annotation']): ?>
                            		  <dd id="annotation" class="edit_area"><#?=$annotation['annotation']?></dd>
                            		  <#?php else: ?>
                            		  <dd id="annotation" class="edit_area">No annotation provided yet.</dd>
                            		  <#?php endif; ?>-->
                            		  <dt>Intraclusteral linkage</dt>
                            		  <dd>
                                		<strong>Min</strong> <?php echo number_format($linkage['intra_min_disc'], 2); ?> |
                                		<strong>Avg</strong> <?php echo number_format($linkage['intra_avg_disc'], 2); ?> |
                               			<strong>Max</strong> <?php echo number_format($linkage['intra_max_disc'], 2); ?>
                            		  </dd>
                        	</dl>

                        	<ul class='media-grid'>
                       		     <a href='#' class="hint" rel='twipsy' title='
                               		 2D diagram abbreviations:<br>
                                	    Y: pyrimidines (C or U)<br>
                                	    R: purines (G or A)<br>
                               		    W: weak (A or U)<br>
                                   	    S: strong (G or C)<br>
                                    	    K: keto (G or U)<br>
                                    	    M: amino (A or C)<br>
                                    	    D: not C<br>
                                    	    V: not U<br>
                                    	    H: not G<br>
                                    	    B: not A<br>
                                    	    A: only A<br>
                                    	    G: only G<br>
                                    	    C: only C<br>
                                    	    U: only U<br>
                                    	    N: any nucleotide'>
                               	     <img class="span3 thumbnail"
                                    	 src="<?=$this->config->item('home_url')?>/img/MotifAtlas/<?php echo substr($title,0,2) . $release_id;?>/<?=$title?>.png"
                                    	 alt='2D diagram'>
                            	    </a>
                        	</ul>
                        	<a href="<?=$this->config->item('home_url')?>/main/rna-3d-hub-help/" target="_blank">Help</a>
                	</div>
            	</div>
                <div class="spanjmol" id="jmolBlock">
                            <div class="block-div jmolheight">
<script>
    jmol_isReady = function(applet) {
        // initialize the plugin
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
        // run the plugin
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

    // these are conveniences that mimic behavior of Jmol.js
    function jmolCheckbox(script1, script0,text,ischecked) {Jmol.jmolCheckbox(jmolApplet0,script1, script0, text, ischecked)};
    function jmolButton(script, text) {Jmol.jmolButton(jmolApplet0, script,text)};
    function jmolHtml(s) { document.write(s) };
    function jmolBr() { jmolHtml("<br />") };
    function jmolMenu(a) {Jmol.jmolMenu(jmolApplet0, a)};
    function jmolScript(cmd) {Jmol.script(jmolApplet0, cmd)};
    function jmolScriptWait(cmd) {Jmol.scriptWait(jmolApplet0, cmd)};
</script>
                            </div>
                            <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                            <input type='button' id='prev' class='btn' value='Previous'>
                            <input type='button' id='next' class='btn' value="Next">
                            <input type='button' id='stereo' class='btn' value="Stereo">
                            <input type='button' id='all' class='btn' value="Show all">
                            <input type='button' id='clear' class='btn' value="Clear all">
                            <br>
                            Coloring options: <select id="colorOPT">
                                <option value="Default" selected>Default</option>
                                <option value="CPK" >CPK</option>
                                <option value="RSR" >Real Space R (RSR)</option>
                                <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                            </select>
                            <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
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
                    <div class="span6 jmolmatrixheight mdmatrix" id="mdBlock">
                            <?php echo $matrix;?>

                            <?php if ($matrix != ''): ?>
                            Mutual discrepancy heat map legend:
                            <div id='mdmatrix-help' rel='twipsy' title='
                            Clicking
                            <ul>
                            <li>on the diagonal toggles a motif instance</li>
                            <li>above the diagonal displays a pair of instances</li>
                            <li>below the diagonal selects multiple instances on the diagonal</li>
                            </ul>'>
                            </div>
                            <table id='legend'><tr>
                            <td class="md00" rel="twipsy" data-original-title="Discrepancy = 0"></td>
                            <td class="md01" rel="twipsy" data-original-title="Discrepancy < 0.1"></td>
                            <td class="md02" rel="twipsy" data-original-title="Discrepancy 0.1-0.2"></td>
                            <td class="md03" rel="twipsy" data-original-title="Discrepancy 0.2-0.3"></td>
                            <td class="md04" rel="twipsy" data-original-title="Discrepancy 0.3-0.4"></td>
                            <td class="md05" rel="twipsy" data-original-title="Discrepancy 0.4-0.5"></td>
                            <td class="md06" rel="twipsy" data-original-title="Discrepancy 0.5-0.6"></td>
                            <td class="md07" rel="twipsy" data-original-title="Discrepancy 0.6-0.7"></td>
                            <td class="md08" rel="twipsy" data-original-title="Discrepancy 0.7-0.8"></td>
                            <td class="md09" rel="twipsy" data-original-title="Discrepancy 0.8-0.9"></td>
                            <td class="md10" rel="twipsy" data-original-title="Discrepancy 0.9-1.0"></td>
                            </tr></table>
                            <?php endif; ?>
                    </div>
            <!-- <div id ='heatmap' style="text-align: left;">
                <script src="//d3js.org/d3.v4.min.js"></script>

<script type="text/javascript">

<?php echo 'var data = ["#heatmap",[
[0.0000,0.2197,0.1386,0.2155,0.3057,0.4566,0.4253,0.4702,0.5454,0.8603,1.1495,1.1801,1.3594,1.3572,1.4752,1.5048,1.4920,1.5462,1.4457,1.3857,1.3153,1.5635,1.5142,1.6603,1.5786,1.6038,1.5892,1.4635],
[0.2197,0.0000,0.1607,0.2040,0.2303,0.2864,0.2164,0.2664,0.3424,0.7462,0.9783,1.0120,1.2697,1.2858,1.3915,1.4287,1.4089,1.4561,1.3469,1.2800,1.2261,1.4826,1.3928,1.5598,1.4434,1.4606,1.4494,1.3460],
[0.1386,0.1607,0.0000,0.0933,0.1803,0.3849,0.3551,0.3938,0.4696,0.7684,1.0827,1.1190,1.2809,1.2729,1.3935,1.4253,1.4161,1.4686,1.3727,1.3154,1.2245,1.4836,1.4535,1.5943,1.5184,1.5456,1.5265,1.3890],
[0.2155,0.2040,0.0933,0.0000,0.1723,0.3731,0.3659,0.3988,0.4865,0.7727,1.1246,1.1636,1.3237,1.3033,1.4317,1.4603,1.4570,1.5114,1.4208,1.3657,1.2580,1.5168,1.5058,1.6388,1.5657,1.5919,1.5740,1.3815],
[0.3057,0.2303,0.1803,0.1723,0.0000,0.2507,0.2400,0.2660,0.3368,0.6122,0.9673,1.0057,1.1725,1.1619,1.2900,1.3252,1.3156,1.3629,1.2712,1.2121,1.1073,1.3866,1.3674,1.5034,1.4270,1.4526,1.4233,1.2424],
[0.4566,0.2864,0.3849,0.3731,0.2507,0.0000,0.1340,0.1304,0.2012,0.5792,0.8851,0.9166,1.2113,1.2250,1.3483,1.3901,1.3669,1.4020,1.2982,1.2243,1.1579,1.4541,1.3698,1.5293,1.4041,1.4119,1.3829,1.1649],
[0.4253,0.2164,0.3551,0.3659,0.2400,0.1340,0.0000,0.0764,0.1671,0.6276,0.8490,0.8826,1.1873,1.2109,1.3171,1.3605,1.3369,1.3745,1.2633,1.1922,1.1398,1.4215,1.3165,1.4894,1.3552,1.3639,1.3448,1.2162],
[0.4702,0.2664,0.3938,0.3988,0.2660,0.1304,0.0764,0.0000,0.1226,0.5663,0.8034,0.8409,1.1429,1.1636,1.2716,1.3155,1.2928,1.3310,1.2226,1.1514,1.0895,1.3725,1.2767,1.4468,1.3138,1.3236,1.3032,1.1574],
[0.5454,0.3424,0.4696,0.4865,0.3368,0.2012,0.1671,0.1226,0.0000,0.5104,0.7214,0.7597,1.1014,1.1379,1.2412,1.2942,1.2629,1.2918,1.1800,1.1039,1.0581,1.3548,1.2356,1.4198,1.2691,1.2755,1.2434,1.1308],
[0.8603,0.7462,0.7684,0.7727,0.6122,0.5792,0.6276,0.5663,0.5104,0.0000,0.5789,0.6053,0.8116,0.8277,0.9732,1.0290,0.9921,1.0057,0.9298,0.8515,0.7607,1.1010,1.0727,1.2036,1.1143,1.1321,1.0438,0.7618],
[1.1495,0.9783,1.0827,1.1246,0.9673,0.8851,0.8490,0.8034,0.7214,0.5789,0.0000,0.0964,0.5349,0.6235,0.6373,0.7047,0.6553,0.6806,0.5733,0.4964,0.4891,0.7769,0.6091,0.8173,0.6370,0.6364,0.5677,0.7201],
[1.1801,1.0120,1.1190,1.1636,1.0057,0.9166,0.8826,0.8409,0.7597,0.6053,0.0964,0.0000,0.4801,0.5768,0.6184,0.6843,0.6244,0.6398,0.5262,0.4424,0.4700,0.7882,0.6052,0.8082,0.6354,0.6290,0.5424,0.6836],
[1.3594,1.2697,1.2809,1.3237,1.1725,1.2113,1.1873,1.1429,1.1014,0.8116,0.5349,0.4801,0.0000,0.1696,0.2069,0.2965,0.2297,0.2173,0.1707,0.2234,0.2809,0.5998,0.6448,0.7516,0.8132,0.8540,0.7161,0.8391],
[1.3572,1.2858,1.2729,1.3033,1.1619,1.2250,1.2109,1.1636,1.1379,0.8277,0.6235,0.5768,0.1696,0.0000,0.2199,0.2386,0.2778,0.3568,0.3239,0.3708,0.3429,0.6174,0.7566,0.8367,0.9174,0.9606,0.8386,0.8921],
[1.4752,1.3915,1.3935,1.4317,1.2900,1.3483,1.3171,1.2716,1.2412,0.9732,0.6373,0.6184,0.2069,0.2199,0.0000,0.1245,0.1384,0.2297,0.2061,0.3017,0.2937,0.5227,0.6130,0.7175,0.7840,0.8290,0.7143,0.9257],
[1.5048,1.4287,1.4253,1.4603,1.3252,1.3901,1.3605,1.3155,1.2942,1.0290,0.7047,0.6843,0.2965,0.2386,0.1245,0.0000,0.1123,0.2572,0.2652,0.3427,0.3403,0.4612,0.5786,0.6641,0.7433,0.7866,0.6819,0.9095],
[1.4920,1.4089,1.4161,1.4570,1.3156,1.3669,1.3369,1.2928,1.2629,0.9921,0.6553,0.6244,0.2297,0.2778,0.1384,0.1123,0.0000,0.1687,0.1888,0.2531,0.3021,0.4370,0.5210,0.6043,0.6839,0.7237,0.6001,0.8337],
[1.5462,1.4561,1.4686,1.5114,1.3629,1.4020,1.3745,1.3310,1.2918,1.0057,0.6806,0.6398,0.2173,0.3568,0.2297,0.2572,0.1687,0.0000,0.1601,0.2137,0.2895,0.4994,0.5526,0.6077,0.6913,0.7191,0.5519,0.7557],
[1.4457,1.3469,1.3727,1.4208,1.2712,1.2982,1.2633,1.2226,1.1800,0.9298,0.5733,0.5262,0.1707,0.3239,0.2061,0.2652,0.1888,0.1601,0.0000,0.1202,0.2603,0.5577,0.5412,0.6553,0.7066,0.7408,0.5965,0.8128],
[1.3857,1.2800,1.3154,1.3657,1.2121,1.2243,1.1922,1.1514,1.1039,0.8515,0.4964,0.4424,0.2234,0.3708,0.3017,0.3427,0.2531,0.2137,0.1202,0.0000,0.2389,0.5493,0.4784,0.5954,0.6325,0.6635,0.5097,0.7069],
[1.3153,1.2261,1.2245,1.2580,1.1073,1.1579,1.1398,1.0895,1.0581,0.7607,0.4891,0.4700,0.2809,0.3429,0.2937,0.3403,0.3021,0.2895,0.2603,0.2389,0.0000,0.4735,0.5538,0.6092,0.6610,0.6950,0.5648,0.6777],
[1.5635,1.4826,1.4836,1.5168,1.3866,1.4541,1.4215,1.3725,1.3548,1.1010,0.7769,0.7882,0.5998,0.6174,0.5227,0.4612,0.4370,0.4994,0.5577,0.5493,0.4735,0.0000,0.2616,0.3159,0.3526,0.4197,0.4748,0.8904],
[1.5142,1.3928,1.4535,1.5058,1.3674,1.3698,1.3165,1.2767,1.2356,1.0727,0.6091,0.6052,0.6448,0.7566,0.6130,0.5786,0.5210,0.5526,0.5412,0.4784,0.5538,0.2616,0.0000,0.2923,0.2068,0.2705,0.3223,0.8727],
[1.6603,1.5598,1.5943,1.6388,1.5034,1.5293,1.4894,1.4468,1.4198,1.2036,0.8173,0.8082,0.7516,0.8367,0.7175,0.6641,0.6043,0.6077,0.6553,0.5954,0.6092,0.3159,0.2923,0.0000,0.2355,0.2238,0.3349,0.8565],
[1.5786,1.4434,1.5184,1.5657,1.4270,1.4041,1.3552,1.3138,1.2691,1.1143,0.6370,0.6354,0.8132,0.9174,0.7840,0.7433,0.6839,0.6913,0.7066,0.6325,0.6610,0.3526,0.2068,0.2355,0.0000,0.0821,0.2553,0.8327],
[1.6038,1.4606,1.5456,1.5919,1.4526,1.4119,1.3639,1.3236,1.2755,1.1321,0.6364,0.6290,0.8540,0.9606,0.8290,0.7866,0.7237,0.7191,0.7408,0.6635,0.6950,0.4197,0.2705,0.2238,0.0821,0.0000,0.2321,0.8068],
[1.5892,1.4494,1.5265,1.5740,1.4233,1.3829,1.3448,1.3032,1.2434,1.0438,0.5677,0.5424,0.7161,0.8386,0.7143,0.6819,0.6001,0.5519,0.5965,0.5097,0.5648,0.4748,0.3223,0.3349,0.2553,0.2321,0.0000,0.6587],
[1.4635,1.3460,1.3890,1.3815,1.2424,1.1649,1.2162,1.1574,1.1308,0.7618,0.7201,0.6836,0.8391,0.8921,0.9257,0.9095,0.8337,0.7557,0.8128,0.7069,0.6777,0.8904,0.8727,0.8565,0.8327,0.8068,0.6587,0.0000],
],
["4V9F|1|0|G|2033","7OW7|1|A|G|3839","7O7Y|1|B5|G|3571","7QIW|1|2|G|2338","7QI4|1|A|G|2655","7RQB|1|1A|G|1992","7P7Q|1|A|G|2005","8A57|1|A|G|2025","4YBB|1|DA|G|1992","4YBB|1|AA|G|94","6QN3|1|A|G|18","5DDP|1|A|G|22","7P7Q|1|A|G|374","4YBB|1|DA|G|338","7QIW|1|2|G|224","7O7Y|1|B5|G|241","7OW7|1|A|G|241","4V9F|1|0|G|345","7RQB|1|1A|G|338","8A57|1|A|G|381","7ZW0|1|LA|G|227","7P7Q|1|a|G|292","4YBB|1|AA|G|266","7O7Y|1|A2|G|386","7QIZ|1|S2|G|341","7ZW0|1|2|G|337","7ZHG|1|2|G|275","7OYC|1|51|G|307"]
]'; ?>
</script>
<script type="text/javascript" src="http://rna.bgsu.edu/webfr3d/js/heatmap.js"></script>
</div> -->
                </div>
            </div>
        </div>

<?/*
        <br>

        <div class="row">
            <div class="span16 history">
                <h4>History</h4>
                <?php echo $history;?>
            </div>
        </div>
*/?>

      </div>

      <script>

        $('.mdmatrix td, #mdmatrix-help, .hint').twipsy({
            html: true
        });
        $(".pdb").click(LookUpPDBInfo);
        $("#sort").tablesorter();

        // only add this code if the user is logged in
        <?php if ($this->session->userdata('username')): ?>
        (function () {

            done_callback = function(value, settings) {
                $('<div></div>').addClass('alert-message success')
                                .html('Saved')
                                .appendTo('#messages')
                                .fadeOut(2200);
            };

            submit_data = function(value, settings) {
                 $.ajax({
                    url: '<?=$baseurl?>motif/save_annotation',
                    type: 'POST',
                    data: {
                        'id'       : this.id,
                        'value'    : value,
                        'motif_id' : '<?=$title?>',
                        'author'   : '<?=$author?>'
                    }
                 });
                 return(value);
            };

            $('.edit').editable( submit_data, {
                 callback : done_callback
            });

            $('.edit_area').editable( submit_data, {
                type: 'textarea',
                submit: 'OK',
                cancel: 'Cancel',
                callback : done_callback
            });

        })();
        <?php endif; ?>


        (function() {
            $('.mdmatrix table').not('#legend').on('click', 'td', function() {

                var $this = $(this);

                var col = $this.parent().children().index($this);
                var row = $this.parent().parent().children().index($this.parent());

                var loops = $this.data('pair').split(':');

                if ( row < col ) {
                    // above the diagonal
                    $.jmolTools.models[loops[0]].hideAll();
                    $('#' + loops[0]).jmolShow();
                    $('#' + loops[1]).jmolShow();
                } else if ( row > col ) {
                    // below the diagonal
                    var diagElem = $('.md00').slice(col, row+1);
                    // if not all are clicked, click all
                    if (diagElem.not(".clicked").length > 0 ) {
                        diagElem.each(function(ind, elem) {
                            $('#' + $(elem).data('pair').split(':')[0] ).jmolShow();
                            $(elem).addClass('clicked');
                        });
                    } else {
                        // if all are clicked, unclick all
                        diagElem.each(function(ind, elem) {
                            $('#' + $(elem).data('pair').split(':')[0] ).jmolHide();
                            $(elem).removeClass('clicked');
                        });
                    }
                } else {
                    $('#' + loops[0]).jmolToggle();
                }

                $this.addClass('jmolActive');
                if ( $.jmolTools.clickedCell ) {
                    $.jmolTools.clickedCell.removeClass('jmolActive').addClass('clicked');
                }
                $.jmolTools.clickedCell = $this;

            });
        })();

        (function() {
            var resizable = $('.motifview .interactions');
            var h = Math.min($('#sort').find('tr').length * 34, 300);
            if ( resizable.height() > 300 ) {
                resizable.height(h);
            }
            $('#variants, #similar, #history').css('max-height', resizable.height());
        })();


      </script>
