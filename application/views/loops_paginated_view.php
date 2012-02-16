    <div class="container loops_paginated_view">

      <div class="content">
        <div class="page-header">
            <div class="row">
                <div class="span8">
                <h1><?php echo $title;?></h1>
                </div>
            <div class="span5">
            <ul class="tabs" data-tabs="tabs">

                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Quality Assurance</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?=$baseurl?>loops/view_all/valid/<?=$motif_type?>/<?=$release_id?>">Valid</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/missing/<?=$motif_type?>/<?=$release_id?>">Missing</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/modified/<?=$motif_type?>/<?=$release_id?>">Modified</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/incomplete/<?=$motif_type?>/<?=$release_id?>">Incomplete</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/abnormal/<?=$motif_type?>/<?=$release_id?>">Composite</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/complementary/<?=$motif_type?>/<?=$release_id?>">Self-complementary</a></li>
                    </ul>
                </li>

                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Loop types</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?=$baseurl?>loops/view_all/<?=$type?>/IL/<?=$release_id?>">Internal loops</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/<?=$type?>/HL/<?=$release_id?>">Hairpin loops</a></li>
                        <li><a href="<?=$baseurl?>loops/view_all/<?=$type?>/J3/<?=$release_id?>">Junction loops</a></li>
                    </ul>
                </li>
            </ul>
            </div>
            </div>
        </div>
        <div class="row">

          <div class="span9" id='left_content'>
            <?php echo $table;?>
            <?php echo $this->pagination->create_links(); ?>
          </div>

          <div class="span6" id="jmol">
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

              <br><br>
              <?php if ($type == 'modified'): ?>
               <p>
               Modified nucleotides are shown in pink when the 'Show neighborhood' button is clicked
               </p>
              <?php elseif ($type == 'missing'): ?>
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

      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: '<?=$baseurl?>ajax/get_loop_coordinates',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums'
	    		});
			}, 200);
      	}

        $(".pdb").click(LookUpPDBInfo);

        $('#jmol').css('position','fixed');
        var offset_left = $('#left_content').offset().left + 560; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('left',offset_left);
        $('#jmol').css('top', offset_top);

      </script>
