    <div class="container loops_single_view">

      <div class="content">
        <div class="page-header">
          <h1><?=$id?></h1>
        </div>
        <div class="row">

            <div class="span9">
                <ul class="tabs" data-tabs="tabs">
                    <li class="active"><a href="#general">General information</a></li>
                    <li><a href="#similar">Similar loops</a></li>
                    <li><a href="#history">Motif history</a></li>
                    <li><a href="#rnastar">RNASTAR</a></li>
                </ul>
            </div>

            <div class="tab-content span9" id='left_content'>

                <div class="tab-pane active" id='general'>

                    <div class="row">
                        <div class="span6">
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
                                <dt>Non-redundant classes</dt>
                                <dd><?=$nr_classes?></dd>
                            </dl>
                        </div>
                        <div class="span3">
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
                        <div class="span3">
                            <h4>Motif assignment</h4>
                            <dl>
                                <dt>Current motif</dt>
                                <dd><?=$motif_url?></dd>
                                <dt>Common name</dt>
                                <dd><?=$motif_common_name?></dd>
                                <dt>Basepair signature</dt>
                                <dd><?=$bp_signature?></dd>
                                <dt>Number of instances</dt>
                                <dd><?=$motif_instances?></dd>
                            </dl>
                        </div>
                        <div class="span6">
                            <h4>Nearby proteins</h4>
                            <?php
                                if (count($proteins) > 0) {
                                    echo '<dl>';
                                    foreach($proteins as $chain => $desc) {
                                        echo "<dt>Chain $chain</dt>";
                                        echo "<dd>{$desc['description']} (Uniprot: {$desc['uniprot']})</dd>";
                                    }
                                    echo '</dl>';
                                } else {
                                    echo "No proteins within 16&Aring;";
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id='similar'>
                    <?=$table?>
                </div>

                <div class="tab-pane" id='history'>
                    Coming soon
                </div>

                <div class="tab-pane" id='rnastar'>
                    Coming soon
                </div>

            </div>

            <div class="span6" id="jmol" >
                <div class="block-div jmolheight">
                    <script type="text/javascript">
                        jmolInitialize(" /jmol");
                        jmolSetAppletColor("#ffffff");
                        jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
                <br><br>
                <div id='checkboxes'>
                    <label><input type='checkbox' class='jmolInline' data-coord='<?=$id?>'><?=$id?></label>
                </div>
           </div>

        </div>
      </div>


      <script>


        // initialize jmolTools
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
        });

        // run when jmol is ready
      	function appletLoaded (){
      	    // toggle the first checkbox
      	    $('.jmolInline').first().jmolToggle();
      	}

//           function appletLoaded() {
//
//             show_loop_in_jmol('<?=$id?>');
//             jmol_neighborhood_button_click('neighborhood');
//             jmol_show_nucleotide_numbers_click('showNtNums');
//
//         }

        $('#sortable').tablesorter();

        var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top);


      </script>
    <script>

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

            // initialize the plugin on the temporary elements
            $('.'+tempClass).jmolTools({
                showStereoId: 'stereo',
                showNeighborhoodId: 'neighborhood',
                showNumbersId: 'showNtNums'
            });
            // show the loops
            $('#l0').jmolToggle();
            $('#l1').jmolToggle();
            $(document).ajaxStop(function() {
                jmolScript('center 1.0;zoom {1.1} 0;select 2.1;color black;');
            });
        });

    </script>
