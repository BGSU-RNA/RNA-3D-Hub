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

              <br><br>
              <?php if ($type == 'modified_nt'): ?>
               <p>
               Modified nucleotides are shown in pink when the 'Show neighborhood' button is clicked
               </p>
              <?php elseif ($type == 'missing_nt'): ?>
               <p>
               Some nucleotides may either be completely missing in some 3D structures, or only
               their phosphate backbone is described in the PDB file. RNA 3D motifs with such nucleotides
               are excluded from further analysis.
               </p>
              <?php elseif ($type == 'complementary'): ?>
               <p>
                Some internal loops are self-complementary, which may indicate that they are, in fact,
                simply normal watson-crick helices and shouldn't be considered as internal loops.
               </p>
              <?php endif; ?>
          </div>

        </div>
      </div>

      <script>
          $(function () {

            $(".pdb").click(LookUpPDBInfo);
//             $("#sortable").tablesorter();

            $('.loop').click(function() {
                var t = $(this);
                var loop_id = t.next().html();
                show_loop_in_jmol(loop_id);
                $('#neighborhood').attr('value','Show neighborhood');
                $('#showNtNums').attr('value','Show numbers');
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
