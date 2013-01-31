    <div class="container loops_benchmark_view">

      <div class="content">
        <div class="page-header">
          <h1>Loop Extraction Benchmark
          <small>PDB 1S72, <?=$kind?></small>
          <button id="save" class='btn primary'>Save</button>
          </h1>
            <div class="span16">
                Switch to:
                <a href="<?=$baseurl?>loops/benchmark/IL">Internal loops</a>
                <a href="<?=$baseurl?>loops/benchmark/HL">Hairpin loops</a>
                <a href="<?=$baseurl?>loops/benchmark/J3">Three-way junctions</a>
                &nbsp;&nbsp;
                <a href="https://github.com/BGSU-RNA/loop-extraction-benchmark" target="_blank">View code on GitHub</a>
            </div>
        </div>
        <div class="row">
          <div class="span16 block scroll_table">
            <?=$table?>
          </div>
        </div>
        <div class="alert-message" id="status" class="span4"></div>
        <br>
        <div class="row">
            <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
                        jmolInitialize("/jmol");
                        jmolSetAppletColor("#f3f3f3");
                        jmolApplet(400, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                <label><input type='checkbox' id='showNtNums'>Nucleotide numbers</label>
           </div>

            <div class="span9 offset1" id='explanation'>
                <p>
                Complete agreement between methods is marked in green. Overlapping loops are in orange.
                All comparisons are relative to the loops found by FR3D.
                The table can be sorted by clicking on the column headers.
                </p>

                <div id='nts'></div>
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
                    serverUrl: '<?=$baseurl?>ajax/get_nt_coordinates_approximate',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums'
	    		});
			}, 1500);
      	}

        $(".editable").click(function (e) {
            $("#save").show();
            e.stopPropagation();
        });

        $(document).click(function() {
            $("#save").hide();
        });

        $('.jmolInline').click(function() {
            var dict = {};
            var nts = '';
            var order = new Array('fr3d','rna3dmotif','scor','rloom','rnajunction','cossmos');
            dict[$(this).closest('td').attr('class')] = $(this).parent().data('original-title');
            $(this).closest('td').siblings().children('.twipsy').each(function() {
                dict[$(this).closest('td').attr('class')] = $(this).data('original-title');
            });
            for (var key in order) {
                nts += '<strong>' + order[key] + '</strong>: ' + dict[order[key]] + '<br>';
            }
            $('#nts').html(nts);
        });

        $("#save").click(function (e) {
            var content = new Array();
            $('.editable').each(function() {
                var txt = $(this).html();
                if (txt != '') {
                    var id = $(this).first().prev().prev().prev().prev().prev().prev().prev().prev().html();
                    content.push(id, txt);
                }
            });
            $.ajax({
                url: '<?=$baseurl?>ajax/save_loop_extraction_benchmark_annotation',
                type: 'POST',
                data: {
                    content: content
                },
                success:function (data) {
                    if (data == '1')
                    {
                        $("#status")
                        .addClass("success")
                        .html("Data saved successfully")
                        .fadeIn('slow')
                        .delay(2000)
                        .fadeOut('slow');
                    }
                    else
                    {
                        $("#status")
                        .addClass("warning")
                        .html("Error, data could not be saved")
                        .fadeIn('slow')
                        .delay(2000)
                        .fadeOut('slow');
                    }
                }
            });
        });

      </script>
