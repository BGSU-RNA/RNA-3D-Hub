    <div class="container pdb_loops_view">

      <div class="content">
        <div class="page-header">
          <h1><?php echo $title;?>
              <small>
                  <?=$ils['valid_count']?> internal loops,
                  <?=$hls['valid_count']?> hairpin loops,
                  <?=$jls['valid_count']?> three-way junctions
              </small>
          </h1>
            <ul class="pills">
              <li class="active"><a href="#">Loops</a></li>
              <li><a href="#">General</a></li>
              <li><a href="#">Motifs</a></li>
              <li><a href="#">Triples</a></li>
            </ul>

        </div>


        <div class="row">
          <div class="span8">

            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#ils">Internal loops</a></li>
                <li><a href="#hls">Hairpin loops</a></li>
                <li><a href="#jls">Junction loops</a></li>
            </ul>

            <div class="tab-content" id="my-tab-content">
                <div class="tab-pane active" id="ils">
                    <h2>Internal loops</h2>
                    <h3>Valid loops <small>(<?=$ils['valid_count']?>)</small></h3>
                    <div class="valid block">
                        <?php echo $ils['valid'];?>
                    </div>
                    <h3>Loops with modified nucleotides <small>(<?=$ils['modified_count']?>)</small></h3>
                    <div class="modified block">
                        <?php echo $ils['modified'];?>
                    </div>
                    <h3>Loops with missing nucleotides <small>(<?=$ils['missing_count']?>)</small></h3>
                    <div class="missing block">
                        <?php echo $ils['missing'];?>
                    </div>
                </div>

                <div class="tab-pane" id="hls">
                    <h2>Hairpin loops</h2>
                    <h3>Valid loops <small>(<?=$hls['valid_count']?>)</small></h3>
                    <div class="valid block">
                        <?php echo $hls['valid'];?>
                    </div>
                    <h3>Loops with modified nucleotides <small>(<?=$hls['modified_count']?>)</small></h3>
                    <div class="modified block">
                        <?php echo $hls['modified'];?>
                    </div>
                    <h3>Loops with missing nucleotides <small>(<?=$hls['missing_count']?>)</small></h3>
                    <div class="missing block">
                        <?php echo $hls['missing'];?>
                    </div>
                </div>

                <div class="tab-pane" id="jls">
                    <h2>Three-way junctions</h2>
                    <h3>Valid loops <small>(<?=$jls['valid_count']?>)</small></h3>
                    <div class="valid block">
                        <?php echo $jls['valid'];?>
                    </div>
                    <h3>Loops with modified nucleotides <small>(<?=$jls['modified_count']?>)</small></h3>
                    <div class="modified block">
                        <?php echo $jls['modified'];?>
                    </div>
                    <h3>Loops with missing nucleotides <small>(<?=$jls['missing_count']?>)</small></h3>
                    <div class="missing block">
                        <?php echo $jls['missing'];?>
                    </div>
                </div>

            </div>


          </div>


          <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
//                         jmolInitialize(" /jmol");
//                         jmolSetAppletColor("#ffffff");
//                         jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
           </div>


        </div>
      </div>


    <script>
        $(function () {
            $('.tabs').tabs()
        })

        $('.twipsy').twipsy();

      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: 'http://leontislab.bgsu.edu/Motifs/jmolInlineLoader/nt_coord_new.php',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev'
	    		});
			}, 1500);
      	}
      </script>