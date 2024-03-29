    <div class="container unit_id_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?=strtoupper($unit_id)?>
            <small><?=anchor('unitid', 'Unit id nomenclature')?></small>
          </h1>
        </div>

        <div class="row">

            <?php foreach ($result as $unit_id): ?>
            <div class="span4">
              <dl>
                <dt>Unit id</dt>
                <dd>
                  <?=anchor("unitid/describe/{$unit_id['unit_id']}", $unit_id['unit_id'])?>
                </dd>
                <dt>PDB <?=$unit_id['pdb_id']?></dt>
                <dd>
                  View in
                  <?=anchor_popup("https://www.rcsb.org/structure/{$unit_id['pdb_id']}", 'PDB')?>
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
                <dt>Atom name</dt>
                <dd><i>blank (all atoms)</i></dd>
                <dt>Alternate id</dt>
                <dd><i>blank (or 'A')</i></dd>
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
                <dd><?=$unit_id['pdb_id']?>.cif</dd>
              </dl>
            </div>
            <?php endforeach; ?>

        </div> <!-- end row -->

    </div>
