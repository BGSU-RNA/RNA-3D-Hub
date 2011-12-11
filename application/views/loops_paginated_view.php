    <div class="container loops_paginated_view">

      <div class="content">
        <div class="page-header">
          <h1><?php echo $title;?></h1>
        </div>
        <div class="row">

          <div class="span8" id='left_content'>
            <?php echo $table;?>
            <?php echo $this->pagination->create_links(); ?>
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

            $(".pdb").click(LookUpPDBInfo);
            $("#sortable").tablesorter();

            $('.loop').click(function() {
                var t = $(this);
                var loop_id = t.next().html();
                show_loop_in_jmol(loop_id);
                $('#neighborhood').attr('value','Show neighborhood');
            });

            jmol_neighborhood_button_click('neighborhood');
            jmol_show_nucleotide_numbers_click('showNtNums');

            $('#jmol').css('position','fixed');
            var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
            var offset_top  = $('#left_content').offset().top;
            $('#jmol').css('left',offset_left);
            $('#jmol').css('top', offset_top);
        })
      </script>
