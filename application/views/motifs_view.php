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
            <div class="table_controls"></div>
            <?=$table?>
          </div>

          <div class="span6" id="jmol">
              <div class="block-div jmolheight">
<script>
    jmol_isReady = function(applet) {
        // initialize the plugin
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            showNextId: 'next',
            showPrevId: 'prev'
        });
        // run the plugin
        $('.jmolInline').first().jmolToggle();
    };

    var Info = {
        width: 340,
        height: 340,
        debug: false,
        color: 'white',
        addSelectionOptions: false,
        use: 'HTML5',
        j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
        readyFunction: jmol_isReady,
        disableInitialConsole: true
    };

    var jmolApplet0 = Jmol.getApplet('jmolApplet0', Info);

    // these are conveniences that mimic behavior of Jmol.js
    function jmolCheckbox(script1, script0,text,ischecked) {Jmol.jmolCheckbox(jmolApplet0,script1, script0, text, ischecked)};
    function jmolButton(script, text) {Jmol.jmolButton(jmolApplet0, script,text)};
    function jmolHtml(s) { document.write(s) };
    function jmolBr() { jmolHtml("<br />") };
    function jmolMenu(a) {Jmol.jmolMenu(jmolApplet0, a)};
    function jmolScript(cmd) {Jmol.script(jmolApplet0, cmd)};
    function jmolScriptWait(cmd) {Jmol.scriptWait(jmolApplet0, cmd)};
</script>
              </div>
              <input type='button' id='prev' class='btn' value='Previous'>
              <input type='button' id='next' class='btn' value="Next">
              <input type='button' id='stereo' class='btn' value="Show stereo">
              <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
              <br>

              <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>

              <br><br>

              <?=$counts?>
          </div>

        </div>
      </div>

    <script type="text/javascript" src="<?=$baseurl?>js/jquery.dataTables.min.js"></script>

    <script>

    $(function() {

        // fix jmol positioning
        var offset_left = $('#left_content').offset().left + 530; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top);

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
