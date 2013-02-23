    <div class="container motif_compare_view">

      <div class="content">

        <div class="page-header">
          <div class="row">
            <div class="span14">
              <h1>
                <?php
                    echo anchor_popup("motif/view/$motif1", $motif1) . ' (1) vs. ' .
                         anchor_popup("motif/view/$motif2", $motif2) . ' (2)';
                ?>
                <small>compare two motifs</small>
              </h1>
            </div>
          </div>
        </div> <!-- end of page-header -->

        <div class="row">
            <div class="span16">
                <p>
                This page shows mutual discrepancy between all instances of two motifs. Click on the
                matrix to view two motif instances superimposed. If no superposition can be calculated
                within the geometric discrepancy of 1.0 Å/nucleotide, then the cell is colored black.
                If the match between two motif instances has been disqualified, then the corresponding
                cell is marked with an "x" and the reason for disqualification is displayed on hover.
                If two motif instances have identical sequences, then the corresponding cell will
                rotate around its center.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="span6" id="jmol">
                    <div class="block-div jmolheight">
                        <script type="text/javascript">
                            jmolInitialize(" /jmol");
                            jmolSetAppletColor("#ffffff");
                            jmolApplet(340);
                        </script>
                    </div>
                    <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                    <input type='button' id='stereo' class='btn' value="Show stereo">
                    <br>
                    <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
                    <br>
                    <div id='checkboxes'></div>
            </div>

            <div class="mdmatrix span8 offset1">
                <?=$matrix?>

                <?php if ($matrix != ''): ?>
                Legend:
                <div id='mdmatrix-help' rel='twipsy' title='
                Rows and columns are marked "1", "2", and "1&2" depending on whether
                the loop instance is seen in the first, in the second, or in both motifs.'>
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

      <script>

        $(function(){
            $('.mdmatrix td').first().css('width', '25px');
            $('.mdmatrix tr').first().css('height','25px');
        });

        $('.mdmatrix td, #mdmatrix-help').twipsy({
            html: true
        });

        $('.jmolTools-loop-pairs').click(function(){

            $this = $(this);

            // mark the clicked element
            var jmolActive = 'jmolActive'; // css class for the clicked td
            $('.' + jmolActive).removeClass(jmolActive);
            $this.addClass(jmolActive);

            // get two loops that need to be shown
            var loop_ids = $this.data('coord').split(':');

            // clear jmol window
            jmolScript('zap;');

            // destroy all previously created temporary elements
            var tempClass = 'jmolTools-temp-elems';
            $('#checkboxes').html('').children().remove();

            // reset the state of the system
            $.jmolTools.numModels = 0;
            $.jmolTools.stereo = false;
            $.jmolTools.neighborhood = false;
            $.jmolTools.models = {};

            // create new temporary elements
            // get loop1 from the alignment of loop1 and loop2
            // if there is no alignment of loop1 and loop2, then
            // get loop1 from the alignment of loop2 and loop1
            $('#checkboxes').append($('<input type="checkbox">')
                .data('coord', '@'+loop_ids[0]+':'+loop_ids[1])
                .attr('id', 'l0')
                .addClass(tempClass)
            ).append('<label for="l0">' + loop_ids[0] + '</label>')
             .append(', <a href="<?=$baseurl?>loops/view/' + loop_ids[0] + '" target="_blank">Go to loop</a>')
             .append('<br>')
              // get loop2 from the alignment of loop1 and loop2
             .append($('<input type="checkbox">')
                .data('coord', loop_ids[0]+':@'+loop_ids[1])
                .attr('id', 'l1')
                .addClass(tempClass)
            ).append('<label for="l1">' + loop_ids[1] + ' (colored black)</label>')
             .append(', <a href="<?=$baseurl?>loops/view/' + loop_ids[1] + '" target="_blank">Go to loop</a>')
             .append($('<div>')
                .addClass('alert-message block-message warning')
                .html($this.data('original-title'))
            );

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
