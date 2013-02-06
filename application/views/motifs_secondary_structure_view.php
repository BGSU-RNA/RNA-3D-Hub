    <div class="container motifs_view">

      <div class="content">

        <div class="page-header">

          <h1>
            <?php if ( $selected != 'all' ): ?>

                <?=$selected['organism']?> <?=$selected['type']?>
                <small><a href="<?php echo site_url(array('motifs', '2ds')); ?>">View all 2Ds</a>

            <?php else: ?>

                RNA 3D Motifs Instances Mapped on 2Ds

            <?php endif; ?>
          </h1>

        </div>

        <div class="row">

            <?php if ( $selected != 'all' ): ?>

              <div class="span16">

                <p>
                  <span class="label notice">Help</span>
                  Internal loops are shown in green, hairpin loops are blue, and three-way junctions
                  are yellow. Loops not present in RNA 3D Hub are red.

                  Clicking on the colored boxes takes to the page for the respective loop.
                  If you experience problems with the embedded document, try
                  <a href="<?php echo str_replace('preview', 'edit', $selected['url']); ?>" target="_blank">downloading</a>
                  the pdf and opening it on your computer. Please don't hesitate to
                  <a href="http://rna.bgsu.edu/main/contact-us">contact us</a>
                  with questions or feedback.
                </p>

                <iframe src="<?=$selected['url']?>" height="1200" width="920"></iframe>
              </div>

            <?php else: ?>

              <div class="span6">

                <h4>Available secondary structures:</h4>

                <ol>

                <?php foreach($all as $url=>$molecule): ?>

                    <li>
                      <a href="<?php echo site_url(array('motifs', '2ds', $url)); ?>">
                        <?=$molecule['organism']?> <?=$molecule['type']?>
                      </a>
                    </li>

                <?php endforeach; ?>

                </ol>

                <p>
                  <a href="https://docs.google.com/file/d/0B1RoD7V_rQavYURtdjNoWEtrbFE/edit" target="_blank">Thermus thermophilus 16S and 23S</a>
                  can be viewed only locally.
                </p>

              </div>

              <div class="span8">

                <p>
                </p>

                <p>
                  Secondary structures provide a convenient way of exploring
                  ribosomal RNAs and are familiar to many scientists.

                  To facilitate navigating RNA 3D Motif Atlas,
                  we labeled publicly available 2D diagrams of rRNAs
                  with links to RNA 3D motif instances from the Motif Atlas.

                </p>

                <p>
                  We used <a href="http://rna.bgsu.edu/rna3dhub/nrlist">Non-redundant lists</a>
                  of RNA 3D structures
                  to choose representative structures for each molecule type and
                  linked secondary structure diagrams with their motif instances.

                  All motifs and motif instances are assigned unique and stable identifiers,
                  so even as new structures become available,
                  it will be possible to track the current motif
                  group for each motif instance using the
                  versioning system built into RNA 3D Motif Atlas .
                </p>

                <br>

                <p>
                  Special thanks to Alma Gonzales for preparing the PDFs.
                </p>

              </div>

            <?php endif; ?>

        </div>
      </div>