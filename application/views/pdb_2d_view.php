
    <!-- RNA2D -->
    <script type="text/javascript" src="<?=$baseurl?>js/sizzle.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/d3.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/jquery.rna2d.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d-controls.js"></script>

    <div class="container pdb-2d-view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?=strtoupper($pdb_id)?>
            <small>2D representation</small>
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
          <div class="span16">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Motifs</a></li>
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
                <li class="active"><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/2d">2D Diagram</a></li>
            </ul>
          </div>
        </div>
        <!-- end navigation -->

        <div class="row">
            <div class="span3 offset4">
                <div id='view-buttons' class='btn-group' data-toggle='buttons-radio'>
                    <?php if ($has_airport): ?>
                    <button id='airport-view' class="btn view-control" data-view='airport'>
                      Airport
                    </button>
                    <?php else: ?>
                    <button id='airport-view' disabled="disabled" 
                        class="btn hasTooltip disabled view-control" data-view='airport'
                        title="No airport diagram is available yet">
                      Airport
                    </button>
                    <?php endif; ?>
                    <button id='circular-view' class="btn active view-control" data-view='circular'>
                      Circular
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div id='controls' class='span1 block-controls'>

              <button data-controls-modal="help-modal" data-backdrop="true" 
                data-keyboard="true" class="btn primary btn-block">Help</button>

                <div id="motif-controls">
                    <button id='il-toggle' type="button" class="btn btn-block
                        motif-toggle active" disabled='disabled' data-toggle='button' data-motif='IL'>IL</button>
                    <button id='hl-toggle' type="button" class="btn btn-block
                        motif-toggle active" disabled='disabled' data-toggle='button' data-motif='HL'>HL</button>
                    <button id='j3-toggle' type="button" class="btn btn-block
                        motif-toggle active" disabled='disabled' data-toggle='button' data-motif='J3'>J3</button>
                </div>

              <div id="control-groups">
                <div id="interaction-controls">
                    <button type="button" id="all-toggle" class="btn btn-block
                      toggle-control" data-toggle='button' data-family='all'>All</button>

                    <button type="button" id='cWW-toggle' class="btn btn-block
                      cWW toggle-control active" data-toggle='button' data-family='cWW'>cWW</button>

                    <button type="button" id='tWW-toggle' class="btn btn-block
                      tWW toggle-control" data-toggle='button' data-family='tWW'>tWW</button>

                    <button type="button" id="cWS-toggle" class="btn btn-block
                      cWS toggle-control" data-toggle='button' data-family='cWS'>cWS</button>

                    <button type="button" id="tWS-toggle" class="btn btn-block
                      tWS toggle-control" data-toggle='button' data-family='tWS'>tWS</button>

                    <button type="button" id="cWH-toggle" class="btn btn-block
                      cWH toggle-control" data-toggle='button' data-family='cWH'>cWH</button>

                    <button type="button" id="tWH-toggle" class="btn btn-block
                      tWH toggle-control" data-toggle='button' data-family='tWH'>tWH</button>

                    <button type="button" id="cSH-toggle" class="btn btn-block
                      cSH toggle-control" data-toggle='button' data-family='cSH'>cSH</button>

                    <button type="button" id="tSH-toggle" class="btn btn-block
                      tSH toggle-control" data-toggle='button' data-family='tSH'>tSH</button>

                    <button type="button" id="cSS-toggle" class="btn btn-block
                      cSS toggle-control" data-toggle='button' data-family='cSS'>cSS</button>

                    <button type="button" id="tSS-toggle" class="btn btn-block
                      tSS toggle-control" data-toggle='button' data-family='tSS'>tSS</button>

                    <button type="button" id="cHH-toggle" class="btn btn-block
                      cHH toggle-control" data-toggle='button' data-family='cHH'>cHH</button>

                    <button type="button" id="tHH-toggle" class="btn btn-block
                      tHH toggle-control" data-toggle='button' data-family='tHH'>tHH</button>

                </div>

              </div>

            </div>

          <div id='rna-2d' class='rna2d span8'>
            &nbsp;
          </div>

            <div class="right-side row span6">

              <div class="row span6">
                <div id="error-message" class="alert-message error hide fade in" data-alert='alert'>
                   <a class="close" href="#">×</a>
                </div> 

                <div class="row span6">
                    <div id="jmol" class="well span6">
                        <script type='text/javascript'>
                            jmolInitialize(" /jmol");
                            jmolSetAppletColor("#ffffff");
                            jmolApplet(340, "javascript appletLoaded()");
                        </script>
                        <button type="button" id="neighborhood" class="btn">Show neighborhood</button>
                        <button type="button" id="stereo" class="btn">Stereo</button>
                        <label><input type="checkbox" id="showNtNums">Show numbers</label>
                    </div>
                </div>
                <div class="row span6">
                    <div id="about-selection" class="alert-message block-message info hide span6"></div>
                </div>
                <div class="row span6">
                    <div id="related-structures" class="alert-message block-message info span6">
                        <h4>Related 2D Diagrams</h4>
                        <?php if (count($related_pdbs) == 0): ?>
                          <strong>None found</strong>
                        <?php else: ?>
                          <?php foreach($related_pdbs as $pdb): ?>
                            <a href="<?=$baseurl?>pdb/<?=$pdb?>/2d"><?=$pdb?></a>
                          <?php endforeach; ?>
                          <br>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

          </div>

        </div>
      </div>

        <div id="help-modal" class='modal hide fade' tabindex="-1" role="dialog">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="help-modal" aria-hidden="true">×</button>
            <h3>2D structures</h3>
          </div>

          <div class="modal-body">

            <p>
              Shown here is a <strong>circular diagram</strong> generated using the 
              <a href="http://rna.bgsu.edu/main/software/fr3d/">FR3D</a> annotations 
              for <?=$pdb_id?>. The black circle represents the annotated chains. For 
              some structures an <strong>airport diagram</strong> is provided. To draw it click the 
              airport button. Structures without one have a disabled button.
            </p>

            <h4>Interactions</h4>
            <p>
              Interactions are displayed as arcs connecting nucleotides,
              by default only cWW interactions are displayed. The 
              <strong>dotted arcs</strong>
              are long range interactions, there include things like 
              pseudoknots. To display other interactions use the interaction 
              controls to the right. Clicking on a interaction will toggle 
              displaying all interactions of that family and ones near that 
              family. So clicking on tWW shows all tWW and ntWW. 
            </p>

            <h4>Motifs</h4>
            <p>
                In airport mode motifs are displayed by default. To hide them click the motif button.
                Internal loops are shown in a green box, hairpins in blue and 3-way junctions in yellow.
                Currently we only extract 3-way junctions, in the future this may change.
            </p>

            <h4>Modes</h4>
            <p>
              In the default <strong>select mode</strong>, click and drag to create a selection box. 
              All nucleotides within the selection box will be displayed in a jmol 
              window to the right. The selection box is dragable and resizeable. 
            </p>

            <p>
              In <strong>click mode</strong>, click on a interaction or nucleotide to display it
              in 3D. In addition, some information about the clicked element will 
              be displayed below the jmol window. To switch to the click mode use 
              the selection mode control. Hovering over an interaction will 
              highlight it and the nucleotides that form it. Hovering over a 
              nucleotide will highlight it as well as all intereracations it forms.
            </p>

            <a class="btn primary" href="http://rna.bgsu.edu/main/interacting-with-2d-structures" target="_blank">More details</a>
          </div>
        </div>

<script type='text/javascript'>
    NTS = <?=$nts?>;
    LONG = <?=$long_range?>;
    INTERACTION_URL = "http://rna.bgsu.edu/rna3dhub/pdb/<?=$pdb_id?>/interactions/fr3d/basepairs/csv";
    LOOP_URL = "http://rna.bgsu.edu/rna3dhub/loops/download/<?=$pdb_id?>";

    $('#chosen').chosen().change(function(){
        window.location.href = "<?=$baseurl?>pdb/" + $(this).val();
    });

    if (!NTS[0].nts.length) {
        $("#rna-2d").append("<h3 align='center'>Could not generate 2D diagram. " +
            "Check back later</h3>");
    }
</script>
