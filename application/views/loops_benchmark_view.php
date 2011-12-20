    <div class="container loops_benchmark_view">

      <div class="content">
        <div class="page-header">
          <h1>Loop Extraction Benchmark
          <small><?=$kind?></small>
          </h1>
        </div>
        <div class="row">
          <div class="span16 block scroll_table">
            <h2></h2>
            <?=$table?>
          </div>
        </div>
        <br>
        <div class="row">
            <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
                        jmolInitialize("/jmol");
                        jmolSetAppletColor("#ffffff");
                        jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                <label><input type='checkbox' id='showNtNums'>Nucleotide numbers</label>
           </div>

            <div class="span9 offset1" id='explanation'>
                <h4>About</h4>
                Coming soon.
            </div>

        </div>
      </div>

      <script>
        $('.twipsy').twipsy();
        $(".pdb").click(LookUpPDBInfo);
        $("#sortable").tablesorter();
      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: '<?=$baseurl?>ajax/get_coordinates',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums'
	    		});
			}, 1500);
      	}

      </script>
