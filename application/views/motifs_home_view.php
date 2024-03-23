<body>
    <div class="container motifs_home_view">

      <div class="content">

        <div class="hero-unit">

            <h1>RNA 3D Motif Atlas</h1>

            <p>
            is a comprehensive and representative collection of internal and hairpin loop RNA 3D motifs
            extracted from the <a href="<?=$baseurl?>nrlist">Representative Sets</a> of RNA 3D structures.
            </p>

            <p>
              In Summer 2021 we made small adjustments to the clustering methodology.
              See the <a href="https://docs.google.com/document/d/1OpeT3w00PsFly5eVMLXxyTgxrf9odadse1m6Slpqhkc/edit?usp=sharing">release notes for each release</a>.
              Follow us on <a href="https://twitter.com/rna3dhub">Twitter</a>.
            </p>

            <a class="btn primary large" href="<?=$baseurl?>motifs/release/il/<?=$release_info['il_release']?>">Internal loops</a>
            <em>Current version: <?=$release_info['il_release']?></em>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

            <a class="btn primary large" href="<?=$baseurl?>motifs/release/hl/<?=$release_info['hl_release']?>">Hairpin loops</a>
            <em>Current version: <?=$release_info['hl_release']?></em>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J3/<?=$release_info['j3_release']?>">3-way Junction</a>
            <em>Current version: <?=$release_info['j3_release']?></em>

            <br>
            <br>

            <em>
            Latest motif atlas release based on the representative set from: <?=date('Y-m-d', $release_info['last_update'])?>
<!--             next update: <?=date('d-m-Y', $release_info['next_update'])?> -->
            </em>

        </div>


        <div class="row">

            <div class="span97p">

            <div class="well">
            <a class="btn" href="<?=$baseurl?>motifs/2ds">Ribosomal 2D diagrams</a>
            <a class="btn" href="<?=$baseurl?>motifs/release_history">Release history</a>
            <a class="btn" href="<?=$baseurl?>motifs/compare_releases">Compare releases</a>
            <a class="btn" href="<?=$this->config->item('home_url')?>/main/rna-3d-hub-help/" target="_blank">Help</a>
            <a class="btn" href="<?=$baseurl?>search">Search</a>
            </div>

            <p>
              <strong>RNA 3D motifs</strong> are recurrent structural modules that are essential for many biological functions and RNA folding. Usually drawn as unstructured <strong>hairpin and internal loops</strong>, these motifs are organized by non-canonical basepairs, supplemented by characteristic stacking and base-backbone interactions.
            </p>

            <p>
              <strong>Method.</strong> To create the Motif Atlas we extract RNA 3D motif instances from the current <a href="<?=$this->config->item('home_url')?>/rna3dhub/nrlist">representative set</a> using <a href="<?=$this->config->item('fr3d_url')?>">FR3D</a>, a program for symbolic and geometric searching of RNA 3D structures. Next, we use a clustering approach based on maximum cliques to obtain a representative collection of RNA 3D motifs. Unique and stable ids are assigned to all motifs and motif instances.
            </p>

                <strong>Citation.</strong> The paper describing RNA 3D Motif Atlas has been <a href="http://rnajournal.cshlp.org/content/19/10/1327.full" target="_blank">published in RNA</a>. If you use this resource, please cite:
                <blockquote>
                <p>Automated classification of RNA 3D motifs and the RNA 3D Motif Atlas</p>
                <p>Anton I. Petrov, Craig L. Zirbel, and Neocles B. Leontis</p>
                <small>RNA October 2013 19: 1327-1340; Published in Advance August 22, 2013, <a href="http://dx.doi.org/10.1261/rna.039438.113"  target="_blank">doi:10.1261/rna.039438.113</a></small>
                </blockquote>

            </div>
        </div>
      </div>
