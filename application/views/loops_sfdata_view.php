    <div class="container loops_sfdata_view">

      <div class="content">
        <div class="page-header">
          <h1>Loops that are part of motifs
          <small>Sfcheck and Mapman</small>
          </h1>
        </div>
        <div class="row">
          <div class="span16 block scroll_table">
            <h2></h2>
            <?=$table?>
          </div>
        </div>
        <br>
        <div class="row">
            <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
                        jmolInitialize("/jmol");
                        jmolSetAppletColor("#ffffff");
                        jmolApplet(340, "javascript appletLoaded()");
                    </script>
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='prev' class='btn' value='Previous'>
                <input type='button' id='next' class='btn' value="Next">
                <br>
                <label><input type='checkbox' id='showNtNums'>Nucleotide numbers</label>
           </div>

            <div class="span9 offset1" id='explanation'>
                <p>
                Table columns can be reordered by dragging.
                </p>

                <h5>1,2: correlation, correlation_side_chain</h4>
                <p>
                Correlation between observed and calculated structure-factor amplitudes.
                Small values of Dcorr indicate that the model of the corresponding backbone or side chain agrees poorly with the electron density.
                </p>

                <h5>3,4: real_space_R, real_space_R_side_chain</h4>
                <p>
                explanation here
                </p>

                <h5>5: connect</h4>
                <p>
                The connectivity index, connect, is the same quantity as the residue-density index, but computed for the backbone atoms excluding the carbonyl O atoms in proteins, and considering the P, O5’, C5’, C3’ and O3’ atoms in nucleic acids. Connect measures the level of the electron density along the macromolecule skeleton and can be used to assess the continuity of the electron density along the polymer chain. Low levels of the connect index indicate locations where this continuity is broken. Such locations may occur in loops lying in regions with low electron density or in places where errors in model tracing occurred.
                </p>

                <h5>6,7: shift, shift_side_chain</h4>
                <p>
                The quantity shift, expressed in units of sigma, indicates the tendency of the considered group of atoms to move away from their current position, with large values of shift corresponding to regions where this tendency is high.
                </p>

                <h5>8,9: density_index_main_chain, density_index_side_chain</h4>
                <p>
                The density index reflects the level of the electron density at the backbone or side-chain atoms of a given residue, and thereby provides a local measure of the density level. For regions with high electron density, the value of density index nearly always exceeds 1. For regions with low electron density, this value will be < 1. Such regions may be problematic for model fitting.
                </p>

                <h5>10,11: B_iso_main_chain, B_iso_side_chain</h4>
                <p>
                This quantity is computed as the average of the atomic B factors of the backbone and side-chain atoms of each residue. Comparison of the B-factor and density index plots can be useful for detecting regions with errors in the model. It would be expected that in a well refined model, atoms with large B factors would lie in regions with low density, characterized in our plot by a low density index. Therefore, when such atoms occur in high-density regions, problems with either the model or the refinement procedure may be suspected.
                </p>

                <h5>12: mapman correlation</h5>
                <p>
                explanation here
                </p>

                <h5>13: mapman real space R</h5>
                <p>
                explanation here
                </p>

                <h5>14: mapman B iso mean</h5>
                <p>
                explanation here
                </p>

                <h5>15: mapman occupancy mean R</h5>
                <p>
                explanation here
                </p>


            </div>

        </div>
      </div>

      <script>
        $('.twipsy').twipsy();
        $(".pdb").click(LookUpPDBInfo);
        $("#sftable").tablesorter();
      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: 'http://leontislab.bgsu.edu/Motifs/jmolInlineLoader/nt_coord_new.php',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showNucleotideNumbersId: 'showNtNums'
	    		});
			}, 1500);
      	}

      </script>
