<body>
    <div class="container motifs_home_view">

      <div class="content">

        <div class="hero-unit">

            <h1>RNA 3D Motif Atlas</h1>

            <p>
            The RNA 3D Motif Atlas is a comprehensive and representative collection of hairpin, internal,
            and multi-helix junction loops
            extracted from the <a href="<?=$baseurl?>nrlist">Representative Sets</a> of RNA 3D structures.
            </p>

            <p>
              With release 3.94 we include four-way and higher junctions, filling in back to release 3.2.
              Releases 3.77 to 3.84, plus 3.88 and 3.89 appear to be missing some data.  Best to work with 3.90 and later.
              With release 3.83 we include three-way junctions, filling in back to release 3.2 from 2018-02-09.
              With release 3.77 we implemented a new clustering strategy based on hierarchical clustering.
              See the <a href="https://docs.google.com/document/d/e/2PACX-1vSWF7oAX0XQb-vNHCEV1F93KAgpBmoZ3r-rbgaubM1Nyg-vBSZi9uf_ayOWSl-s6_UYTRayJHxibTm4/pub">
              release notes for each release</a>.
              <!-- Follow us on <a href="https://twitter.com/rna3dhub">Twitter</a>. -->
            </p>

            <a class="btn primary large" href="<?=$baseurl?>motifs/release/hl/<?=$release_info['hl_release']?>">Hairpin loops</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/il/<?=$release_info['il_release']?>">Internal loops</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J3/<?=$release_info['j3_release']?>">3-way Junctions</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J4/<?=$release_info['j4_release']?>">J4</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J5/<?=$release_info['j5_release']?>">J5</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J6/<?=$release_info['j6_release']?>">J6</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J7/<?=$release_info['j7_release']?>">J7</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J8/<?=$release_info['j8_release']?>">J8</a>

            &nbsp;&nbsp;
            <a class="btn primary large" href="<?=$baseurl?>motifs/release/J9/<?=$release_info['j9_release']?>">J9</a>

            <br>
            <em>Current release <?=$release_info['hl_release']?> based on the representative set from <?=date('Y-m-d', $release_info['last_update'])?>
            </em>

        </div>


        <div class="row">

            <div class="span97p">

            <div class="well">
            <a class="btn" href="<?=$baseurl?>motifs/release_history">Release history</a>
            <!-- <a class="btn" href="<?=$baseurl?>motifs/compare_releases">Compare releases</a> -->
            <a class="btn" href="<?=config('BGSUConfig')->bgsu_url?>/help/rna-3d-hub-help/" target="_blank">Help</a>
            <a class="btn" href="<?=$baseurl?>search">Search</a>
            </div>

            <p>
              <strong>RNA 3D motifs</strong> are recurrent structural modules that are essential for many biological functions and RNA folding. Usually drawn as approximately circular listing of bases A, C, G, U on secondary structure diagrams, in 3D these loops are often organized by non-canonical basepairs, supplemented by characteristic stacking and base-backbone interactions.
            </p>

            <p>
              <strong>Method.</strong> To create the Motif Atlas we extract RNA 3D loop instances from the current <a href="<?=config('BGSUConfig')->hub_url?>/nrlist">representative set</a> using <a href="<?=config('BGSUConfig')->fr3d_url?>">FR3D</a>, a program for symbolic and geometric searching of RNA 3D structures. Next, we use a clustering approach based on maximum cliques (replaced by hierarchical clustering in release 3.77) to create motif groups that are similar in geometry and basepairing interactions. Unique and stable ids are assigned to all motif groups and loop instances.
            </p>

                <strong>Citation.</strong> The paper describing RNA 3D Motif Atlas was <a href="http://rnajournal.cshlp.org/content/19/10/1327.full" target="_blank">published in RNA</a>. If you use this resource, please cite:
                <blockquote>
                <p>Automated classification of RNA 3D motifs and the RNA 3D Motif Atlas</p>
                <p>Anton I. Petrov, Craig L. Zirbel, and Neocles B. Leontis</p>
                <small>RNA October 2013 19: 1327-1340; Published in Advance August 22, 2013, <a href="http://dx.doi.org/10.1261/rna.039438.113"  target="_blank">doi:10.1261/rna.039438.113</a></small>
                </blockquote>

            </div>
        </div>
      </div>
