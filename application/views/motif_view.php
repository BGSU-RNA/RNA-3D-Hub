    <div class="container motifview">

      <div class="content">

        <div class="page-header">
          <div class="row">
            <div class="span14">
              <h1>Motif <?php echo $title;?><small>Release <?php echo $release_id;?></small></h1>
            </div>
            <div class="span2"></div>
          </div>
        </div> <!-- end of page-header -->

        <div class="row">
            <div class="span4">
                <div class="block-div" id="annotation-panel">
                        <dl>
                            <dt>Common name:</dt>
                            <?php if ($annotation['common_name']): ?>
                            <dd id="common_name" class="edit"><?=$annotation['common_name']?></dd>
                            <?php else: ?>
                            <dd id="common_name" class="edit">No common name assigned yet. Log in to add it.</dd>
                            <?php endif; ?>
                            <dt>Consensus basepair signature:</dt>
                            <dd id="bp_signature" class="edit"><?=$annotation['bp_signature']?></dd>
                            <dt>Free text annotation:</dt>
                            <?php if ($annotation['annotation']): ?>
                            <dd id="annotation" class="edit_area"><?=$annotation['annotation']?></dd>
                            <?php else: ?>
                            <dd id="annotation" class="edit_area">No annotation provided yet. Log in to add one.</dd>
                            <?php endif; ?>
                        </dl>
                </div>
            </div>
            <div class="span12">
                <div class="row span 12 interactions" id="interactions">
                    <ul class="tabs" data-tabs="tabs">
                        <li class="active"><a href="#int">Pairwise interactions</a></li>
                        <li><a href="#variants">Sequence variants</a></li>
                    </ul>


                    <div class="tab-content">

                        <div class="tab-pane active block-div" id='int'>
                            <?php echo $table;?>
                        </div>

                        <div class="tab-pane block-div" id='variants'>
                            Coming soon.
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="span6" id="jmolBlock">
                            <div class="block-div jmolheight">
                                <script type="text/javascript">
                                    jmolInitialize(" /jmol");
                                    jmolSetAppletColor("#ffffff");
                                    jmolApplet(340, "javascript appletLoaded()");
                                </script>
                            </div>
                            <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                            <input type='button' id='all' class='btn' value="Show all">
                            <input type='button' id='stereo' class='btn' value="Show stereo">
                            <input type='button' id='prev' class='btn' value='Previous'>
                            <input type='button' id='next' class='btn' value="Next">
                            <input type='button' id='clear' class='btn' value="Clear">
                            <br>
                            <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
                    </div>
                    <div class="span6 jmolheight mdmatrix" id="mdBlock">
                            <?php echo $matrix;?>

                            <?php if ($matrix != ''): ?>
                            Legend:
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




<!--
        <br>

        <div class="row">
            <div class="span16 history">
                <h4>History</h4>
                <?php echo $history;?>
            </div>
        </div>
 -->

      </div>

      <script>

        $('.mdmatrix td, #mdmatrix-help').twipsy({
            html: true
        });
        $(".pdb").click(LookUpPDBInfo);
        $("#sort").tablesorter();

        // initialize jmolTools
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            showNextId: 'next',
            showPrevId: 'prev',
            showAllId: 'all',
            clearId: 'clear',
            insertionsId: 'insertions'
        });

        // run when jmol is ready
      	function appletLoaded (){
      	    // toggle the first checkbox
      	    $('.jmolInline').first().jmolToggle();

      	    // mark it on the mutual discrepancy matrix
      	    var id = $('.jmolInline').first().attr('id');
      	    $('.md00').each(function(ind, elem) {
                $elem = $(elem);
                if ( $elem.data('pair').indexOf(id) == 0 ) {
                    $elem.addClass('jmolActive');
                    $.jmolTools.clickedCell = $elem;
                    return;
                }
      	    });
      	}



        // greyscale
        function greyscale() {
        }

//         $('#insertions').on('click', greyscale);

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

      </script>
