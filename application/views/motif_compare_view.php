    <div class="container motif_compare_view">

      <div class="content">

        <div class="page-header">
          <div class="row">
            <div class="span14">
              <h1>
                <?php
                    echo anchor_popup("motif/view/$motif1", $motif1) . ' vs. ' .
                         anchor_popup("motif/view/$motif2", $motif2);
                ?>
              </h1>
            </div>
          </div>
        </div> <!-- end of page-header -->

        <div class="row">
            <div class="span6" id="jmol">
                    <div class="block-div jmolheight">
                        <script type="text/javascript">
                            jmolInitialize(" /jmol");
                            jmolSetAppletColor("#ffffff");
                            jmolApplet(340, "javascript appletLoaded()");
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

      <script>

        $(function(){
            $('.mdmatrix td').first().css('width', '25px');
            $('.mdmatrix tr').first().css('height','25px');
        });

        $('.mdmatrix td, #mdmatrix-help').twipsy({
            html: true
        });

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
            ).append('<label for="l1">' + loop_ids[1] + ' (colored black)</label>')
             .append($('<div>')
                .addClass('alert-message block-message warning')
                .html($this.data('original-title'))
            );

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
