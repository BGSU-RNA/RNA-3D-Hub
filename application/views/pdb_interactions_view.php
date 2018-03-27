    <div class="container pdb_interactions_view">

      <div class="content">
        <div class="page-header">
          <h1><?=strtoupper($pdb_id)?>
          <small><?=strtoupper($method)?> <?=$interaction_type?> pairwise interaction annotations</small>
          <small><a class="btn pull-right success" href="<?=$current_url?>/csv">Download</a></small>
          </h1>
        </div>

        <!-- navigation -->
        <div class="row">
          <div class="span16">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Motifs</a></li>
                <li class="dropdown active" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Interactions</a>
                  <ul class="dropdown-menu">
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basepairs">Base-pair</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/stacking">Base-stacking</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basephosphate">Base-phosphate</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/baseribose">Base-ribose</a></li>
                    <li class="divider"></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/all">All interactions</a></li>
                  </ul>
                </li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/2d">2D Diagram</a></li>
            </ul>
          </div>
        </div>
        <!-- end navigation -->

        <div class="row">

          <!-- annotations -->
          <div class="span8" id="left_content">
            <pre><?=$table?></pre>
          </div>
          <!-- end annotations -->

          <div class="span6 well" id="jmol">
            <script>
              var Info = {
                width: 340,
                height: 340,
                debug: false,
                color: '#f5f5f5',
                addSelectionOptions: false,
                use: 'HTML5',
                j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
                disableInitialConsole: true,
                readyFunction: function(){
                  $('pre a').first().click();
                }
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
            <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
            <input type="button" class="btn" id="neighborhood" value="Show neighborhood">
            <label><input type="checkbox" id="colorRSRZ">Color by RSRZ</label>
            <label><input type="checkbox" id="colorRSR">Color by RSR</label>
          </div>
        </div>
      </div>

      <script>
        $('pre').on('click', 'span', function(){
          // hide all previously displayed popovers
          $('.popover-displayed').removeClass('popover-displayed')
                                 .popover('hide')
                                 .unbind();
          var a = $(this);
          var unit_id = a.html().trim();

          var content = '<a href="<?=$baseurl?>/unitid/describe/'
                        + unit_id + '">Details</a>' +
                        '&nbsp;&nbsp;<?=anchor("unitid", "Nomenclature")?>';
          a.popover({
            offset: 10,
            content: function(){return content;},
            title: function(){return 'Unit id ' + unit_id;},
            delayOut: 1200,
            html: true,
            animate: false,
            placement:'right'
          });
          a.popover('show');
          a.addClass('popover-displayed');
        });

        $('.jmolInline').click(function(){
          var jmolApp = $('#jmolApplet0');
          var jmolDiv = $('#jmol');
          $this = $(this);

          // clear jmol window
          jmolScript('zap;');

          $('a.current').removeClass('current').addClass('viewed');
          $this.addClass('current');

          // reset the state of the system
          $.jmolTools.numModels = 0;
          $.jmolTools.stereo = false;
          $.jmolTools.neighborhood = false;
          $('#neighborhood').val('Show neighborhood');
          $.jmolTools.models = {};
          
          // unbind all events
          $('#stereo').unbind();
          $('#neighborhood').unbind();
          $('#showNtNums').unbind();
          $('#colorRSRZ').unbind();

          var data_coord = $this.prev().html() + ',' + $this.next().html();
          data_coord = data_coord.replace(/\s+/g, '');
          console.log(data_coord);
          $('#tempJmolToolsObj').remove();
          $('body').append("<input type='radio' id='tempJmolToolsObj' data-coord='" + data_coord + "' data-quality='" + data_coord + "'>");
          $('#tempJmolToolsObj').hide();
          $('#tempJmolToolsObj').jmolTools({
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            colorByRSRZ: 'colorRSRZ',
            colorByRSR: 'colorRSR'
          }).jmolToggle();
        });

        // position jmol
        var offset_left = $('#left_content').offset().left + 470; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top);
      </script>
