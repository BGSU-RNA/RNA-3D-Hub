
    <!-- RNA2D -->
    <script type="text/javascript" src="<?=$baseurl?>js/sizzle.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/d3.js"></script>
    <script type="text/javascript" src="<?=$baseurl?>js/rna2d.js"></script>
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
            <div id='controls' class='span1 block-controls'>

              <button data-controls-modal="help-modal" data-backdrop="true" data-keyboard="true" class="btn primary btn-block">Help</button>

              <!-- <h5>Mode</h5> -->
              <button type="button" id="mode-toggle" class="btn btn-block"
                autocomplete="off" data-normal-text="Select"
                data-loading-text="Click">Select</button>

              <div id="control-groups">
                <div id="interaction-controls">
                    <button type="button" id='cWW-toggle' class="btn btn-block
                      cWW toggle-control active" data-family='cWW'>cWW</button>

                    <button type="button" id='tWW-toggle' class="btn btn-block
                      tWW toggle-control" data-family='tWW'>tWW</button>

                    <button type="button" id="cWS-toggle" class="btn btn-block
                      cWS toggle-control" data-family='cWS'>cWS</button>

                    <button type="button" id="tWS-toggle" class="btn btn-block
                      tWS toggle-control" data-family='tWS'>tWS</button>

                    <button type="button" id="cWH-toggle" class="btn btn-block
                      cWH toggle-control" data-family='cWH'>cWH</button>

                    <button type="button" id="tWH-toggle" class="btn btn-block
                      tWH toggle-control" data-family='tWH'>tWH</button>

                    <button type="button" id="cSH-toggle" class="btn btn-block
                      cSH toggle-control" data-family='cSH'>cSH</button>

                    <button type="button" id="tSH-toggle" class="btn btn-block
                      tSH toggle-control" data-family='tSH'>tSH</button>

                    <button type="button" id="cSS-toggle" class="btn btn-block
                      cSS toggle-control" data-family='cSS'>cSS</button>

                    <button type="button" id="tSS-toggle" class="btn btn-block
                      tSS toggle-control" data-family='tSS'>tSS</button>

                    <button type="button" id="cHH-toggle" class="btn btn-block
                      cHH toggle-control" data-family='cHH'>cHH</button>

                    <button type="button" id="tHH-toggle" class="btn btn-block
                      tHH toggle-control" data-family='tHH'>tHH</button>

                </div>
              </div>

            </div>

          <div id='rna-2d' class='rna2d span8'></div>

          <div class="row span6">
            <div id="error-message" class="alert-message error hide fade in" data-alert='alert'>
               <a class="close" href="#">×</a>
            </div>
            <div id="jmol" class="span6">
              <script type='text/javascript'>
                jmolInitialize(" /jmol");
                jmolSetDocument(0);
                jmolSetAppletColor("#ffffff");
              </script>
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
              <a href='https://github.com/blakesweeney/rna2d.js'>RNA2D.js</a>
              is a tool to visualize and interact with RNA Secondary
              structures. It is meant to create the standard airport diagrams and
              provide a way to interact with them. Currently, we can easily
              integrate the 2D visualization with the 3D visualization provided by
              <a href='https://github.com/AntonPetrov/jmolTools'>jmolTools</a>.
            </p>

            <a class="btn primary" href="http://rna.bgsu.edu/main/interacting-with-2d-structures" target="_blank">More details</a>
            <h4>Interactivity</h4>

            <p>
              In the default select mode,
              click and drag to create a selection box. All nucleotides within
              the selection box will be displayed in a jmol window to the right.
            </p>

            <p>
              The selection box is dragable and resizeable. Click inside and
              drag to move it. Click on the border and drag to resize it.
            </p>

            <p>
              In click mode, click on a interaction to display the interaction
              in 3D. To switch to the click mode use the selection mode control.
            </p>

            <p>
              Interactions are displayed as black bars connecting nucleotides,
              by default only cWW interactions are displayed. To display other
              interactions use the interaction controls.
            </p>
          </div>
        </div>

<script type='text/javascript'>
    NTS = <?=$nts?>;
    INTERACTION_URL = "<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/basepairs/csv";

    $('#chosen').chosen().change(function(){
        window.location.href = "<?=$baseurl?>pdb/" + $(this).val();
    });

    if (!NTS.length) {
        $("#rna-2d").append("<h3 align='center'>Could not generate 2D diagram. " +
            "Check back later</h3>");
    }
</script>
