    <div class="container unit_id_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?=strtoupper($unit_id)?>
            <small><?=ucfirst($id_type)?>-style unit id</small>
            <small><?=anchor('unitid', 'Unit id nomenclature')?></small>
          </h1>
        </div>

        <div class="row">
          <div class="span12">
            <?php if (count($result) > 1): ?>
                This new-style unit id is present in both biological assembly and asymmetric unit.
            <?php elseif ($id_type == 'new' ): ?>
                Here is the description of the new-style unit id.
            <?php elseif ($id_type == 'old' ): ?>
                This old-style id corresponds to this new-style id: <?=$result[0]['unit_id']?>.
            <?php endif; ?>
          </div>
        </div>

        <br>

        <div class="row">

            <?php foreach ($result as $unit_id): ?>
            <div class="span4">
              <dl>
                <dt>Unit id</dt>
                <dd>
                  <?=anchor("unitid/describe/{$unit_id['unit_id']}", $unit_id['unit_id'])?>
                </dd>
                <dt>Old id</dt>
                <dd>
                  <?=anchor("unitid/describe/{$unit_id['old_id']}", $unit_id['old_id'])?>
                </dd>
                <dt>PDB <?=$unit_id['pdb_id']?></dt>
                <dd>
                  View in
                  <?=anchor_popup("http://www.pdb.org/pdb/explore.do?structureId={$unit_id['pdb_id']}", 'PDB')?>
                  or
                  <?=anchor("pdb/{$unit_id['pdb_id']}", 'RNA 3D Hub')?>
                </dd>
                <dt>Model</dt>
                <dd><?=$unit_id['model']?></dd>
                <dt>Chain</dt>
                <dd><?=$unit_id['chain']?></dd>
                <dt>Compound</dt>
                <dd>
                    <?=anchor_popup("http://www.pdb.org/pdb/ligand/ligandsummary.do?hetId={$unit_id['comp_id']}", $unit_id['comp_id'])?>
                </dd>
                <dt>Number</dt>
                <dd><?=$unit_id['seq_id']?></dd>
                <dt>Insertion code</dt>
                <dd>
                  <?php if ($unit_id['ins_code'] != ''): ?>
                    <?=$unit_id['ins_code']?>
                  <?php else: ?>
                    <i>blank</i>
                  <?php endif; ?>
                </dd>
                <dt>Alt id</dt>
                <dd>
                  <?php if ($unit_id['alt_id'] != ''): ?>
                    <?=$unit_id['alt_id']?>
                  <?php else: ?>
                    <i>blank</i>
                  <?php endif; ?>
                </dd>
                <dt>Symmetry operator</dt>
                <dd><?=$unit_id['sym_op']?></dd>
                <dt>PDB file</dt>
                <dd><?=$unit_id['pdb_id']?>.<?=$unit_id['pdb_file']?></dd>
              </dl>
            </div>
            <?php endforeach; ?>

        </div> <!-- end row -->

    </div>
