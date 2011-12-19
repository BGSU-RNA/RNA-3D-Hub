    <div class="container loops_single_view">

      <div class="content">
        <div class="page-header">
          <h1><?=$id?></h1>
        </div>
        <div class="row">

            <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
                        jmolInitialize(" /jmol");
                        jmolSetAppletColor("#ffffff");
                        jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood"><br>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
           </div>

            <div class="span6 offset2">
                <h4>Motif</h4>
                Coming soon.
                <h4>Key interactions</h4>
                Coming soon.
                <h4>Similar loops</h4>
                Coming soon.
            </div>

        </div>
      </div>


      <script>
          function appletLoaded() {

            show_loop_in_jmol('<?=$id?>');
            jmol_neighborhood_button_click('neighborhood');
            jmol_show_nucleotide_numbers_click('showNtNums');

        }
      </script>
