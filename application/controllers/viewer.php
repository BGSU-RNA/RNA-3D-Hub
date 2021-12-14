<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <script type="text/javascript" src="js/jsmol/JSmol.min.js"></script> 
    <script type="text/javascript">

var Info = {
  width: 500,
  height: 500,
  serverURL: "http://propka.ki.ku.dk/~jhjensen/jsmol/jsmol.php ",
  use: "HTML5",
  j2sPath: "js/jsmol/j2s",
  console: "jmolApplet0_infodiv"
}

</script>
</head>
<body>

<?php

// an array of key-value pairs where the key is the residue number and the value is the RSRZ score
$RSRZ = array(
	"C1165" => -0.615,
	"G1166" => 0.513,
	"A1167" => -0.339,
	"A1168" => -0.092,
	"A1169" => 1.572,
	"G1171" => 2.868
);

// this function evaluates the RSRZ score of each residue and returns the appropriate coloring command
function color_by_RSRZ ($score) {
	$command = "";
	foreach($score as $res=>$RSRZ_score) {
		if (round($RSRZ_score, 2) < 1.00) {
			$command .= "select $res and :A; color green; ";
		} elseif ((round($RSRZ_score, 2)) >= 1.00 && (round($RSRZ_score, 2) < 2.00)) {
			$command .= "select $res and :A; color yellow; ";
		} elseif ((round($RSRZ_score, 2)) >= 2.00 && (round($RSRZ_score, 2) < 3.00)) {
			$command .= "select $res and :A; color orange; ";
		} else {
			$command .= "select $res and :A; color red; ";
		}
 	
	}
	return $command;
}

$color_RSRZ = color_by_RSRZ($RSRZ);

///////////////////////////////////////////////////////////////////////


 echo "<div align = 'center'>";

 echo "<h3>Basic RSRZ viewer prototype showing HL_1FJG_027</h3>";

 print "<script type=\"text/javascript\" >;";

 print "Jmol.jmolHtml('<table border=\"2.0\" border-collapse: \"collapse\">');";

 print "Jmol.jmolHtml(\"<tr>\");";

 print "Jmol.jmolHtml(\"<td class='view'>\");";

 print "myJmol = Jmol.getApplet(\"jmolApplet0\", Info);";

 print "Jmol.jmolHtml('</td>');";

 print "Jmol.jmolHtml(\"</tr>\");";
      	   
 print "Jmol.jmolHtml(\"</table>\");";

 print "Jmol.script(myJmol, \"set echo top right; echo loading...; refresh; load structure/MotifA.pdb; spacefill off; wireframe 0.2; \");";

 print "Jmol.jmolHtml(\"<table>\");";
      	   
 print "Jmol.jmolHtml(\"<tr>\");";
      	   
 print "Jmol.jmolHtml(\"<td>\");";
      	   
 print "Jmol.jmolButton(myJmol, \" $color_RSRZ \",\"Color by RSRZ\");";

 print "Jmol.jmolHtml(\"</td>\");";

 print "Jmol.jmolHtml(\"<td>\");";

 print "Jmol.jmolButton(myJmol, \" select [U]; color navy; select [G]; color chartreuse; select [C]; color gold; select [A]; color red;  \", \"Color by Nt Type\");";
      	   
 print "Jmol.jmolHtml(\"</td>\");";

 print "Jmol.jmolHtml(\"</tr>\");";

 print "Jmol.jmolHtml(\"<tr>\");";
      	   
 print "Jmol.jmolHtml(\"<td>\");";
      	   
 print "Jmol.jmolButton(myJmol, \" select {*.C1'},{*.CA}; label %[sequence]%[resno]; color labels black; \", \"Labels on\");";

 print "Jmol.jmolHtml(\"</td>\");";

 print "Jmol.jmolHtml(\"<td>\");";

 print "Jmol.jmolButton(myJmol, \" label off \", \"Labels off\");";
      	   
 print "Jmol.jmolHtml(\"</td>\");";

 print "Jmol.jmolHtml(\"</tr>\");";
      	   
 print "Jmol.jmolHtml(\"</table>\");";

 print "</script>";

 echo "</div>"; 
      	   
?>

<style type="text/css">
	td {text-align: center;}	
</style>>

<div align="center">
<h3>RSRZ data for HL_1FJG_027</h3>

<table>
  <tr>
    <th>Residue</th>
    <th>RSRZ</th>
  </tr>
  <tr>
    <td>C1165</td>
    <td>-0.615</td>
  </tr>
  <tr>
    <td>G1166</td>
    <td>0.513</td>
  </tr>
  <tr>
    <td>A1167</td>
    <td>-0.339</td>
  </tr>
  <tr>
    <td>A1168</td>
    <td>-0.092</td>
  </tr>
  <tr>
    <td>A1169</td>
    <td>1.572</td>
  </tr>
  <tr>
    <td>G1171</td>
    <td>2.868</td>
  </tr>
</table>


</div>>

</body>
</html>




