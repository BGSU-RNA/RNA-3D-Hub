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
                            <style>
                                #int th:nth-child(6), #int td:nth-child(6),
                                #int th:nth-child(7), #int td:nth-child(7),
                                #int th:nth-child(8), #int td:nth-child(8){
                                    text-align: left;
                                }
                                .tab-pane.active th{
                                    background-color: rgba(255, 255, 255, 1);
                                    position: sticky; /* make the header sticky */
                                    top: 0; /* position the header at the top of the container */
                                    z-index: 1; /*set the z-index to ensure the header appears on top of other elements*/
                                }
                                
                            </style>
                            
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
                            		  <dt>Heat map statistics</dt>
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
                            <!-- <input type='button' id='all' class='btn' value="Show all"> -->
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
                    <!-- <div class="span6 jmolmatrixheight mdmatrix" id="mdBlock">
                            <?php echo $discrepancy_matrix;?>

                            
                           
                            <div id='mdmatrix-help' rel='twipsy' title='
                            Clicking
                            <ul>
                            <li>on the diagonal toggles a motif instance</li>
                            <li>above the diagonal displays a pair of instances</li>
                            <li>below the diagonal selects multiple instances on the diagonal</li>
                            </ul>'>
                            </div>
                            
                            
                    </div> -->
            <!-- <div id ='heatmap' style="text-align: left;"> -->
                <div id = 'heatmap' style="width: 110%;">
                <script src="//d3js.org/d3.v4.min.js"></script>

<script type="text/javascript">
<?php echo $discrepancy_matrix;?>

</script>
<script type="text/javascript" src="http://rna.bgsu.edu/webfr3d/js/heatmap.js"></script>
</div>
<!-- <div id='mdmatrix-help' rel='twipsy' style = "text-align:left; " title='
                                    Clicking
                                    <ul>
                                    <li>on the diagonal toggles a motif instance</li>
                                    <li>above the diagonal displays a pair of instances</li>
                                    <li>below the diagonal selects multiple instances on the diagonal</li>
                                    </ul>'>
                                    
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
