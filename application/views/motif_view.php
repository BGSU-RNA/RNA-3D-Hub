    <div class="container motifview">

      <div class="content">

        <div class="page-header">
          <div class="row">
            <div class="span14">
              <h1>Motif <?php echo $title;?><small>Release <?php echo $release_id;?></small></h1>
            </div>
            <div class="span2">

              <?php if ($this->session->userdata('username')): ?>
                <input type='button' id='annotate' class='btn success' value='Annotate'>
              <?php endif; ?>

            </div>
          </div>

          <?php if ($this->session->userdata('username')): ?>
          <div class="span15 well" id="annotation">
            <form>
                <div class="row">
                    <div class="span7">
                        <div class="clearfix">
                            <label for="common_name">Common name:</label>
                            <div class="input">
                                <input class="span4" id="common_name" name="common_name" size="30" type="text" value="<?=$annotation['common_name']?>">
                            </div>
                            <input type="text" name="motif_id" value="<?php echo $title;?>" class="motif_id">
                        </div>
                    </div>

                    <div class="span6">
                        <textarea class="span6" rows="4" name="annotation"><?=$annotation['annotation']?></textarea>
                        <span class="help-block">Location on 2D, quality remarks etc </span>
                    </div>

                    <div class="span2">
                        <input type="submit" id="submit_annotation" class="btn primary" value="Save">
                        <div class="messages"></div>
                    </div>
                </div>
            </form>
          </div>
          <?php endif; ?>


        </div> <!-- end of page-header -->

        <div class="row">
          <div class="span16 interactions">
            <h2></h2>
            <?php echo $table;?>
          </div>
        </div>

        <br>


        <div class="block row special_style">
        <div class="span16">
        <div class="row">
            <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
//                         jmolInitialize(" /jmol");
//                         jmolSetAppletColor("#ffffff");
//                         jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='all' class='btn' value="Show all">
                <input type='button' id='stereo' class='btn' value="Show stereo">
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
           </div>

            <div class="span3 block">
                <div id="prev_next_controls">
                    <input type='button' id='prev' class='btn' value='Previous'>
                    <input type='button' id='next' class='btn' value="Next">
                </div>
                <div id="checkboxes">
                    <?php echo $checkboxes;?>
                </div>
            </div>

            <div class="span6 jmolheight mdmatrix">
                <?php echo $matrix;?>
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

        $('.twipsy').twipsy();
        $(".pdb").click(LookUpPDBInfo);
        $("#sort").tablesorter();
      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: 'http://leontislab.bgsu.edu/Motifs/jmolInlineLoader/nt_coord_new.php',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showAllButtonId: 'all',
                    showNucleotideNumbersId: 'showNtNums',
                    showStereoId: 'stereo'
	    		});
			}, 1500);
      	}

        (function() {
            var slider = $('#annotation');
            var sliderSwitch = $('#annotate');
            var messages = $('div.messages');

            sliderSwitch.toggle(function() {
                sliderSwitch.val('Hide');
            }, function() {
                sliderSwitch.val('Annotate');
            });

            sliderSwitch.click( function() {
                slider.slideToggle('slow', 'linear');
            });

            $('#submit_annotation').click( function(e) {
                $.ajax({
                    url: '<?php echo $baseurl;?>motif/save_annotation',
                    type: 'POST',
                    data: $('form').serialize()
                }).done(function(result) {
                    messages.empty()
                            .append('<div class="alert-message success">Data saved</div>')
                            .delay(2000)
                            .fadeOut(300);
                }).fail(function(result) {
                    messages.empty().append('<div class="alert-message error">Error</div>');
                });
                e.preventDefault();
            });
        }());


      </script>