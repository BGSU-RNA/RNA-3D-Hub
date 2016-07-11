<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
<script src='<?=$baseurl?>/js/jsmol/JSmol.min.nojq.js'></script>
<script src='<?=$baseurl?>/js/jquery.jmolTools.js'></script>

<script>

    jmol_isReady = function(applet) {
        // initialize the plugin
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            showNextId: 'next',
            showPrevId: 'prev',
            showAllId: 'all',
            clearId: 'clear',
            insertionsId: 'insertions'                
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
        //j2sPath: 'http://localhost:8888/RNA-3D-Hub/js/jsmol/j2s/',
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
    <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
	<input type='button' id='prev' class='btn' value='Previous'>
    <input type='button' id='next' class='btn' value="Next">
    <input type='button' id='stereo' class='btn' value="Stereo">
    <input type='button' id='all' class='btn' value="Show all">
    <input type='button' id='clear' class='btn' value="Clear all">
    <br><input type='checkbox' id='example1' class='jmolInline' data-coord="<?= $coord ?>"<label for='example1'>Unit id entered: </label><?= $coord ?>
    </div>