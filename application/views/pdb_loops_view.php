    <div class="container pdb_loops_view">

      <div class="content">
        <div class="page-header">
          <h1><?=strtoupper($pdb_id)?>
          <small>Internal, hairpin and 3-way junction loop motifs</small>
          <small class="pull-right">
            <select data-placeholder="Choose a structure" id="chosen">
             <option value=""></option>
            <?php foreach ($pdbs as $pdb): ?>
              <option value="<?=$pdb?>"><?=$pdb?></option>
            <?php endforeach; ?>
            </select>
          </small>
          </h1>
        </div>

        <!-- navigation -->
        <div class="row">
          <div class="span9">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li class="active"><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Motifs</a></li>
                <li class="dropdown" data-dropdown="dropdown">
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
          <a class="btn primary pull-right" href="<?=$baseurl?>loops/download/<?=$pdb_id?>">Download loops</a>
        </div>
        <!-- end navigation -->


        <div class="row">

        <?php if ($valid): ?>

          <!-- motifs -->
          <div class="span8 well">

            <ul class="pills" data-tabs="tabs">
                <li class="active"><a href="#ils"><?=$counts['IL']?> Internal loops</a></li>
                <li><a href="#hls"><?=$counts['HL']?> Hairpin loops</a></li>
                <li><a href="#jls"><?=$counts['J3']?> Junction loops</a></li>
            </ul>

            <div class="tab-content" id="my-tab-content">
                <div class="tab-pane active" id="ils">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['IL']['valid']?>
                    </div>
                    <h3>Disqualified loops</h3>
                    <div class="modified block">
                        <?=$loops['IL']['invalid']?>
                    </div>
                </div>

                <div class="tab-pane" id="hls">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['HL']['valid']?>
                    </div>
                    <h3>Disqualified loops</h3>
                    <div class="modified block">
                        <?=$loops['HL']['invalid']?>
                    </div>
                </div>

                <div class="tab-pane" id="jls">
                    <h3>Valid loops</h3>
                    <div class="valid block">
                        <?=$loops['J3']['valid']?>
                    </div>
                    <h3>Disqualified loops</h3>
                    <div class="modified block">
                        <?=$loops['J3']['invalid']?>
                    </div>
                </div>

            </div> <!-- end of tab-content -->

          </div>
          <!-- end of motifs -->

          <?php if ($counts['IL'] != 0 or $counts['HL'] != 0 or $counts['J3'] != 0): ?>

          <div class="span6 well" id="jmol" >
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
        color: '#f5f5f5',
        addSelectionOptions: false,
        use: 'HTML5',
        j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
        readyFunction: jmol_isReady,
        disableInitialConsole: true,
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
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                <label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>
           </div>

           <?php endif; ?>

        <?php else: ?>
          <pre class="span8"><?=$message?></pre>
        <?php endif; ?>

        </div> <!-- end of row -->
      </div>


    <script>
        $(function () {
            $('.tabs').tabs()
        })

        $('.twipsy').twipsy();

        $('table').tablesorter();

        $('#chosen').chosen().change(function(){
            window.location.href = "<?=$baseurl?>pdb/" + $(this).val() + '/motifs';
        });

    </script>
