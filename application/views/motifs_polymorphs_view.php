    <div class="container motifs_polymorphs_view">

      <div class="content">

        <div class="page-header">
          <h1><?=$title?><small>Different motifs with the same sequence</small></h1>
        </div>

        <div class="row">

          <div class="span9" id="left_content">
            <?=$table?>
          </div>


          <div class="span6" id="jmol">
              <div class="block-div jmolheight">
                  <script type="text/javascript">
                      jmolInitialize(" /jmol");
                      jmolSetAppletColor("#ffffff");
                      jmolApplet(340, "javascript appletLoaded()");
                  </script>
              </div>
              <input type='button' id='prev' class='btn' value='Previous'>
              <input type='button' id='next' class='btn' value="Next">
              <input type='button' id='stereo' class='btn' value="Show stereo">
              <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
              <br>

              <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>

          </div>



        </div>
      </div>





    <script>
        $(function () {
            $("#sort").tablesorter();

            var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
            var offset_top  = $('#left_content').offset().top;
            $('#jmol').css('position','fixed')
                      .css('left',offset_left)
                      .css('top',offset_top);
        })

        // initialize jmolTools
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            showNextId: 'next',
            showPrevId: 'prev'
        });

        // run when jmol is ready
      	function appletLoaded (){
      	    // toggle the first checkbox
      	    $('.jmolInline').first().jmolToggle();
      	}

    </script>
