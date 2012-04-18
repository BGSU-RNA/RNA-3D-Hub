    <div class="container motifs_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small><?=$status?></small>
          </h1>
          <a href='<?=$alt_view?>'>Switch to graph view</a>
          <a href='<?=$polymorph_url?>'>Polymorphs</a>
        </div>
        <div class="row">
          <div class="span9" id='left_content'>

            <h2></h2>
            <?php echo $counts; echo $table;?>

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

        $("#sort").tablesorter();

       function appletLoaded() {
            var timeoutID = window.setTimeout(function(){
                jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: '<?=$baseurl?>ajax/get_exemplar_coordinates',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums',
                    showStereoId: 'stereo'
                });
            }, 200);
        }

        var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top);

        $(".fancybox").fancybox({
            openSpeed  : 'fast',
            closeSpeed : 'fast',
            arrows     : true
        });

    </script>
