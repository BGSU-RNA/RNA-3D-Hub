	</br></br></br>
	<div align = 'center'>


<script>

    jmol_isReady = function(applet) {
        // initialize the plugin
        $('.jmolInline').jmolTools({
            showStereoId: 'stereo',
            showNeighborhoodId: 'neighborhood',
            showNumbersId: 'showNtNums',
            colorOption: 'colorOPT',
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
        width: 850,
        height: 566,
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


	<input type="checkbox" id="showNtNums">Nucleotide numbers
    <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
    <input type='button' id='stereo' class='btn' value="Stereo">
    <br>
                Coloring options: <select id="colorOPT">
                    <option value="Default" selected>Default</option>
                    <option value="CPK">CPK</option>
                    <option value="RSR">Real Space R (RSR)</option>
                    <option value="RSRZ">RSR Z-Score (RSRZ)</option>
                </select>
    <div class='showRSR' style="display:none">
                    <svg height="30" width="340">
                        <defs>
                            <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="10%" style="stop-color:#0d0887; stop-opacity:1" />
                                <stop offset="20%" style="stop-color:#6603a5; stop-opacity:1" />
                                <stop offset="30%" style="stop-color:#8a0da2; stop-opacity:1" />
                                <stop offset="40%" style="stop-color:#ac2693; stop-opacity:1" />
                                <stop offset="50%" style="stop-color:#ca457a; stop-opacity:1" />
                                <stop offset="60%" style="stop-color:#df6264; stop-opacity:1" />
                                <stop offset="70%" style="stop-color:#f0824d; stop-opacity:1" />
                                <stop offset="80%" style="stop-color:#faa638; stop-opacity:1" />
                                <stop offset="90%" style="stop-color:#fbcc27; stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#f0f921; stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    <rect x="20" y="0" width="300" height="15" fill="url(#grad3)"  />
                    <text x="20" y="30" font-family="sans-serif" font-size="12px" fill="black">0.0</text>
                    <text x="90" y="30" font-family="sans-serif" font-size="12px" fill="black">RSR Scale truncated at 0.5</text>
                    <text x="310" y="30" font-family="sans-serif" font-size="12px" fill="black">0.5</text>
                    </svg>
                </div>

                <div class='showRSRZ' style="display:none">
                    <svg height="45" width="340">
                        <defs>
                        <text x="120" y="0" font-family="sans-serif" font-size="12px" fill="black">RSRZ Scale</text>
                            <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="25%" style="stop-color:green;stop-opacity:1" />
                                <stop offset="25%" style="stop-color:yellow;stop-opacity:1" />
                                <stop offset="50%" style="stop-color:yellow;stop-opacity:1" />
                                <stop offset="50%" style="stop-color:orange;stop-opacity:1" />
                                <stop offset="75%" style="stop-color:orange;stop-opacity:1" />
                                <stop offset="75%" style="stop-color:red;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:red;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    <rect x="20" y="0" width="300" height="15" fill="url(#grad2)"  />
                    <text x="92" y="30" font-family="sans-serif" font-size="12px" fill="black">1</text>
                    <text x="167" y="30" font-family="sans-serif" font-size="12px" fill="black">2</text>
                    <text x="243" y="30" font-family="sans-serif" font-size="12px" fill="black">3</text>
                    <text x="140" y="45" font-family="sans-serif" font-size="12px" fill="black">RSRZ Scale</text>
                    </svg>
                </div>
    <!-- <br><input type='checkbox' id='example1' class='jmolInline' data-coord="<?= $coord ?>" data-quality="<?= $coord ?>"<label for='example1'>Selection: </label><?= $coord ?> -->
    <p style="text-align: left;">
    <?php
    // Split the $coord variable by semicolons to display different sets differently
    $coords = explode(';', $coord);

    // Loop through the array and generate HTML for each field
    foreach ($coords as $index => $coord) {
        // Generate unique IDs for each checkbox
        $checkbox_id = ($index + 1);

        // Output HTML for each checkbox
        echo "<br><input type='checkbox' id='$checkbox_id' class='jmolInline' data-coord='$coord' data-quality='$coord' <label for='$checkbox_id'>Selection $checkbox_id: $coord</label>\n";
    }
    ?>
    </div>