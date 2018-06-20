    <div class="container motifview">

      <div class="content">

        <div class="page-header">
          <div class="row">
            <div class="span16">
              <h1>Motif <?=$title?>
                <small>Release <?=$release_id?> <?=$release_id_label?></small>
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

        <div class="row">
            <div class="span4">
                <div class="block-div resizable" id="annotation-panel">
                        <dl>
                            <dt>Description:</dt>
                            <?php if ($annotation['common_name']): ?>
                            <dd id="common_name" class="edit"><?=$annotation['common_name']?></dd>
                            <?php else: ?>
                            <dd id="common_name" class="edit">No description added yet.</dd>
                            <?php endif; ?>
                            <dt>Basepair signature:</dt>
                            <dd id="bp_signature" class="edit"><?=$annotation['bp_signature']?></dd>
                            <dt>Free text annotation:</dt>
                            <?php if ($annotation['annotation']): ?>
                            <dd id="annotation" class="edit_area"><?=$annotation['annotation']?></dd>
                            <?php else: ?>
                            <dd id="annotation" class="edit_area">No annotation provided yet.</dd>
                            <?php endif; ?>
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
            <div class="span12">
                <div class="row span12 interactions resizable" id="interactions">

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
                            <div class="row span11">
                                <div class="span5">
                                    <h5>Complete motif including flanking bases</h5>
                                    <?=$sequence_variation['complete']?>
                                </div>
                                <div class="span5">
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
                <div class="row">
                    <div class="span6" id="jmolBlock">
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
        width: 340,
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
                            </div>
                    <div class="span6 jmolheight mdmatrix" id="mdBlock">
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
