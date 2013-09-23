<body>
    <div class="container motifs_home_view">

      <div class="content">

        <div class="hero-unit">

            <h1>RNA 3D Motif Atlas</h1>

            <p>
            is a comprehensive and representative collection of internal and hairpin loop RNA 3D motifs
            extracted from the <a href="<?=$baseurl?>nrlist">Non-redundant lists</a> of RNA 3D structures.
            Automatically updated every 4 weeks.
            </p>

            <a class="btn primary large" href="<?=$baseurl?>motifs/release/il/current">Internal loops</a>
            <a href="<?=$baseurl?>motifs/graph/il/<?=$release_info['il_release']?>">Graph view</a>
            <em>Current version: <?=$release_info['il_release']?></em>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

            <a class="btn primary large" href="<?=$baseurl?>motifs/release/hl/current">Hairpin loops</a>
            <a href="<?=$baseurl?>motifs/graph/hl/<?=$release_info['hl_release']?>">Graph view</a>
            <em>Current version: <?=$release_info['hl_release']?></em>

            <br>
            <br>

            <em>
            Last update: <?=date('d-m-Y', $release_info['last_update'])?>;
            next update: <?=date('d-m-Y', $release_info['next_update'])?>
            </em>

        </div>


        <div class="row">

            <div class="span16">

            <div class="well">
            <a class="btn" href="<?=$baseurl?>motifs/2ds">Ribosomal 2D diagrams</a>
            <a class="btn" href="<?=$baseurl?>motifs/release_history">Release history</a>
            <a class="btn" href="<?=$baseurl?>motifs/compare_releases">Compare releases</a>
            <a class="btn" href="http://rna.bgsu.edu/main/rna-3d-hub-help/" target="_blank">Help</a>
            <a class="btn" href="<?=$baseurl?>search">Search</a>
            </div>

            <p>
              <strong>RNA 3D motifs</strong> are recurrent structural modules that are essential for many biological functions and RNA folding. Usually drawn as unstructured <strong>hairpin and internal loops</strong>, these motifs are organized by non-canonical basepairs, supplemented by characteristic stacking and base-backbone interactions.
            </p>

            <p>
              <strong>Method.</strong> To create the Motif Atlas we extract RNA 3D motif instances from the current <a href="http://rna.bgsu.edu/rna3dhub/nrlist">non-redundant list</a> using <a href="http://rna.bgsu.edu/FR3D">FR3D</a>, a program for symbolic and geometric searching of RNA 3D structures. Next, we use a clustering approach based on maximum cliques to obtain a representative collection of RNA 3D motifs. Unique and stable ids are assigned to all motifs and motif instances.
            </p>

                <strong>Citation.</strong>The paper describing RNA 3D Motif Atlas has been <a href="http://rnajournal.cshlp.org/content/19/10/1327.full" target="_blank">published in RNA</a>. If you use this resource, please cite:
                <blockquote>
                <p>Automated classification of RNA 3D motifs and the RNA 3D Motif Atlas</p>
                <p>Anton I. Petrov, Craig L. Zirbel, and Neocles B. Leontis</p>
                <small>RNA October 2013 19: 1327-1340; Published in Advance August 22, 2013, <a href="http://dx.doi.org/10.1261/rna.039438.113"  target="_blank">doi:10.1261/rna.039438.113</a></small>
                </blockquote>

            </div>

        </div>

	<div>

        <div class="row">

            <div class="span16">
              <h4>Featured Motifs</h4>
              <ul class="media-grid">

                <?php foreach($featured as $name=>$motif): ?>
                <li>
                  <a href="<?=$baseurl?>motif/view/<?=$motif?>" target="_blank">
                    <img src="http://rna.bgsu.edu/img/MotifAtlas/<?php
                        if ( strstr($motif, 'IL') ) {
                            echo 'IL' . $release_info['il_release'];
                        } else {
                            echo 'HL' . $release_info['hl_release'];
                        }
                    ?>/<?=$motif?>.png" class="thumbnail span2" alt="Motif <?=$motif?>">
                    <?=ucfirst($name);?>
                  </a>
                </li>
                <?php endforeach; ?>

              </ul>
            </div>

        </div>


      </div>
