    <div class="container motifs_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small><?=$status?></small>
          </h1>
          <a href='<?=$alt_view?>'>Switch to graph view</a>
        </div>
        <div class="row">
          <div class="span9" id='left_content'>

            <h2></h2>
            <?php echo $counts; echo $table;?>

          </div>

          <div class="span6" id="jmol">
              <div class="block jmolheight">
                  <script type="text/javascript">
                      jmolInitialize(" /jmol");
                      jmolSetAppletColor("#ffffff");
                      jmolApplet(340);
                  </script>
              </div>
              <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
              <input type='button' id='showNtNums'   class='btn' value="Show numbers">
          </div>

        </div>
      </div>

    <script>
        $(function () {

            $('.exemplar').click(function() {
                var t = $(this);
                var motif_id = t.next().html();
                show_motif_exemplar_in_jmol(motif_id);
                $('#neighborhood').attr('value','Show neighborhood');
            });

            jmol_neighborhood_button_click('neighborhood');
            jmol_show_nucleotide_numbers_click('showNtNums');

            $('#jmol').css('position','fixed');
            var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
            var offset_top  = $('#left_content').offset().top;
            $('#jmol').css('left',offset_left);
            $('#jmol').css('top',offset_top);


            $("#sort").tablesorter();
    		$(".fancybox").fancybox({
    		    openSpeed  : 'fast',
    		    closeSpeed : 'fast',
    		    arrows     : true
    		});
        })
    </script>
