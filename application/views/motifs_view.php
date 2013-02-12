    <div class="container motifs_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small><?=$status?></small>
            <small class="pull-right">
              <ul class="tabs">
              <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Download</a>
                <ul class="dropdown-menu">
                  <li><a href="<?=$current_url?>/csv" download="<?=$title?>.csv">Csv</a></li>
                  <li><a href="<?=$current_url?>/json" download="<?=$title?>.json">Json</a></li>
                </ul>
              </li>
              </ul>
            </small>
          </h1>
          <a href='<?=$alt_view?>'>Switch to graph view</a>
          <a href='<?=$polymorph_url?>'>Polymorphs</a>
        </div>
        <div class="row">
          <div class="span9" id='left_content'>
            <h2></h2>
            <div class="table_controls"></div>
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

    <script type="text/javascript" src="<?=$baseurl?>js/jquery.dataTables.min.js"></script>

    <script>

    // run when jmol is ready
    function appletLoaded (){
        // toggle the first checkbox
        $('.jmolInline').first().jmolToggle();
    }

    $(function() {

        // initialize jmolTools
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            showNextId: 'next',
            showPrevId: 'prev',
            showStereoId: 'stereo'
        });

        // fix jmol positioning
        var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top + 20;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top);

        $(".fancybox").fancybox({
            openSpeed  : 'fast',
            closeSpeed : 'fast',
            arrows     : true
        });

        $('#sort').dataTable({
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "sDom": '<"table_controls well"fi>t'
        });

    });

    </script>
