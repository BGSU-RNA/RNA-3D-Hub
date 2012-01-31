    <div class="container pdb_loops_view">

      <div class="content">
        <div class="page-header">
          <h1><?=$title?></h1>
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
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['IL']['valid']?>
                    </div>
                    <h3>Disqualified</h3>
                    <div class="modified block">
                        <?=$loops['IL']['invalid']?>
                    </div>
                </div>

                <div class="tab-pane" id="hls">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['HL']['valid']?>
                    </div>
                    <h3>Disqualified</h3>
                    <div class="modified block">
                        <?=$loops['HL']['invalid']?>
                    </div>
                </div>

                <div class="tab-pane" id="jls">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['J3']['valid']?>
                    </div>
                    <h3>Disqualified</h3>
                    <div class="modified block">
                        <?=$loops['J3']['invalid']?>
                    </div>
                </div>

            </div>


          </div>


          <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
                        jmolInitialize(" /jmol");
                        jmolSetAppletColor("#ffffff");
                        jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
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
                    serverUrl: 'http://leontislab.bgsu.edu/Motifs/jmolInlineLoader/nt_coord_test.php',
//                     serverUrl: 'http://leontislab.bgsu.edu/Motifs/jmolInlineLoader/nt_coord_new.php',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums'
	    		});
			}, 1500);
      	}
      </script>