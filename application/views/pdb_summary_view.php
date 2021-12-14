    <div class="container summarymin pdb_summary_view">
      <div class="content">
        <div class="page-header">
          <h1>
            <?=strtoupper($pdb_id)?>
            <small>Summary</small>
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
          <div class="span97p">
            <ul class="tabs">
                <li class="active"><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Loops</a></li>
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
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/2d">2D Diagram</a></li>
            </ul>
          </div>
        </div>
        <!-- end navigation -->

        <?php if ($valid): ?>

        <!-- general info from PDB -->
        <div class="row">
          <div class="span93p well">
            <div class="row">
              <!-- 3D image -->
              <div class="span20p">
                <ul class="media-grid">
                  <li>
                    <a href="#">
                      <img src="https://cdn.rcsb.org/images/structures/<?=strtolower(substr($pdb_id, 1, 2));?>/<?=strtolower($pdb_id);?>/<?=strtolower($pdb_id);?>_assembly-1.jpeg" class="span100p" alt="<?=$pdb_id?> assembly">
                    </a>
                  </li>
                </ul>
                View in <a href="<?=$pdb_url?>" target="_blank">PDB</a> or <a href="<?=$ndb_url?>" target="_blank">NDB</a>
              </div>

              <!-- section1 -->
              <div class="span10p">
                <dl>
                  <dt>Structure Title</dt>
                  <dd><?=$title?></dd>
                  <dt>Authors</dt>
                  <dd><?=$authors?></dd>
                  <dt>Release Date</dt>
                  <dd><?=$release_date?></dd>
                </dl>
              </div>

              <!-- section2 -->
              <div class="span15p">
                <dl>
                  <dt>Experimental technique</dt>
                  <dd><?=$experimental_technique?></dd>
                  <?php if ($resolution != ''): ?>
                  <dt>Resolution</dt>
                  <dd><?php echo number_format($resolution, 1); ?> &Aring;</dd>
                  <?php endif; ?>
                  <dt>Chains</dt>
                  <dd>
                    <ul>
                      <li>
                        <?=$rna_chains?> RNA chain<?php if ($rna_chains > 1): ?>s<?php endif; ?>
                        from
                        <?php if ($organisms != ''): ?>
                          <?=$organisms?>
                        <?php else: ?>
                          unknown source
                        <? endif; ?>
                      </li>
                      <li><?=$non_rna_chains?> other chain<?php if ($non_rna_chains != 1): ?>s<?php endif; ?></li>
                    </ul>
                  </dd>
                </dl>
              </div>

              <!-- section3 -->
              <div class="span45p">
                <dl>
                  <dt>Compounds</dt>
                  <dl><?=$compounds?></dl>
                </dl>
              </div>
            </div> <!-- end row -->
          </div> <!-- end well -->
        </div> <!-- end row -->

        <!-- BGSU RNA Site specific data -->
        <div class="row">
              <!-- pairwise interactions -->
              <div class="span20p well">
                <h4>Pairwise Interactions</h4>
                Interactions annotated by <a href="<?=$this->config->item('fr3d_url')?>/FR3D">FR3D</a>:
                <ul>
                  <li><?=$bp_counts?> <a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/basepairs">base-pairs</a></li>
                  <li><?=$bst_counts?> <a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/stacking">base-stacking</a></li>
                  <li><?=$bph_counts?> <a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/basephosphate">base-phosphate</a></li>
                  <li><?=$brb_counts?> <a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/baseribose">base-ribose</a></li>
                  <!-- Commented out since we don't have aa-nt annotations yet
                  <li><?=$baa_counts?> <a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/baseaa">base-amino acids</a></li> -->
                </ul>
                <a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/fr3d/all">View all</a>
              </div>

              <!-- 3D motifs -->
              <div class="span20p well">
                <h4>RNA 3D Motifs</h4>
                In the current release of the <a href="<?=$baseurl?>/motifs">RNA 3D Motif Atlas</a>, <?=$pdb_id?> contains:
                <ul>
                  <li>
                    <?=$loops['IL']?> internal loop<?php if ($loops['IL'] <> 1): ?>s<?php endif; ?>
                    from <?=$il_counts?> motif group<?php if ($il_counts <> 1): ?>s<?php endif; ?>
                  </li>
                  <li>
                    <?=$loops['HL']?> hairpin loop<?php if ($loops['HL'] <> 1): ?>s<?php endif; ?>
                    from <?=$hl_counts?> motif group<?php if ($hl_counts <> 1): ?>s<?php endif; ?>
                  </li>
                  <li><?=$loops['J3']?> three-way junction<?php if ($loops['J3'] <> 1): ?>s<?php endif; ?></li>
                </ul>
                <?=$loops['url']?>
              </div>

              <!-- redundancy -->
              <div class="span20p well">
                <h4>3D Redundancy</h4>
                <?php if (!isset($nr_classes) ): ?>
                    This PDB is not included in any representative set equivalence class. Possibly, this PDB doesn't contain any complete nucleotides.
                <?php else: ?>
                    <?=$pdb_id?> has chains which belong to the following equivalence classes in the current representative set:
                    <ul>
                      <?php foreach ($nr_classes as $nr_class): ?>
                      <li>
                        <?=$nr_urls[$nr_class]?> (<?=$count[$nr_class]?>)
                        <?php if ($representatives[$nr_class] == 1): ?>
                        <strong>rep</strong>
                        <?php endif; ?>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
              </div>

              <!-- related structures -->
              <div class="span15p well">
                <h4>Similar structures</h4>
                <?php if (count($related_pdbs) == 0): ?>
                  None found
                <?php else: ?>
                  <?php foreach($related_pdbs as $pdb): ?>
                  <a class="pdb" href="#"><?=$pdb?></a>
                  <?php endforeach; ?>
                  <br>
                  Structures from <a href="<?=$baseurl?>nrlist/view/<?=$eq_class?>"><?=$eq_class?></a>
                <?php endif; ?>
              </div>
        </div>

        <?php else: ?>
          <div class="row">
            <pre class="span8"><?=$message?></pre>
          </div>
        <?php endif; ?>
      </div>

      <script>
        $('#chosen').chosen().change(function(){
            window.location.href = "<?=$baseurl?>pdb/" + $(this).val();
        });
        $(".pdb").click(LookUpPDBInfo);
      </script>
