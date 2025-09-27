
// 1/21/2013 10:54:15 PM -- adds image handling

var jmolApplet0; // set up in HTML table, below

// logic is set by indicating order of USE -- default is HTML5 for this test page, though
var use = "HTML5" // JAVA HTML5 WEBGL IMAGE  are all otions

jmol_isReady = function(applet) {
    Jmol._getElement(applet, "appletdiv").style.border="1px solid #D3D3D3";
    $('.jmolInline').first().jmolToggle();
}

var Info = {
    width: 630,
    height: 420,
    debug: false,
    color: "white",
    addSelectionOptions: false,
    use: "HTML5",
    j2sPath: "https://rna.bgsu.edu/rna3dhub/js/jsmol/j2s/",
    readyFunction: jmol_isReady,
    //script: script,
    //jarPath: "java",
    //jarFile: (useSignedApplet ? "JmolAppletSigned.jar" : "JmolApplet.jar"),
    //isSigned: useSignedApplet,
    //disableJ2SLoadMonitor: true,
    disableInitialConsole: true
    //defaultModel: "$dopamine",
    //console: "none", // default will be jmolApplet0_infodiv
}


// these are conveniences that mimic behavior of Jmol.js

function jmolCheckbox(script1, script0,text,ischecked) {Jmol.jmolCheckbox(jmolApplet0,script1, script0, text, ischecked)}
function jmolButton(script, text) {Jmol.jmolButton(jmolApplet0, script,text)}
function jmolHtml(s) { document.write(s) };
function jmolBr() { jmolHtml("<br />") }
function jmolMenu(a) {Jmol.jmolMenu(jmolApplet0, a)}
function jmolScript(cmd) {Jmol.script(jmolApplet0, cmd)}
function jmolScriptWait(cmd) {Jmol.scriptWait(jmolApplet0, cmd)}
