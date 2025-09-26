    <div class="container  nrreleasemin release_nonredundant_view">

      <div class="content">
        <div class="page-header">
          <h1>Non-redundant Set of <?=$type_upper?> 3D Structures
          <br>
          <small>Non-redundant based on Rfam families and clans from release <?=$release_id?>, <?=$description?></small>
          </h1>
        </div>

        <div class="row">
          <div class="span97p">
            <ul class="tabs span97p" data-tabs="tabs">
                <li><a>Resolution cutoffs:</a></li>
                <?php foreach(array('1.5A', '2.0A', '2.5A', '3.0A', '3.5A', '4.0A', '20.0A', 'all') as $res): ?>
                  <?php if ($resolution == $res): ?>
                    <li class="active"><a href="<?=$baseurl?>nrlist/nonredundant/<?=$type?>/<?=$release_id?>/<?=$res?>/<?=$criterion?>/<?=$count_limit?>"><?=ucfirst($res)?></a></li>
                  <?php else: ?>
                    <li><a href="<?=$baseurl?>nrlist/nonredundant/<?=$type?>/<?=$release_id?>/<?=$res?>/<?=$criterion?>/<?=$count_limit?>"><?=ucfirst($res)?></a></li>
                  <?php endif; ?>
                <?php endforeach; ?>
                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Download</a>
                  <ul class="dropdown-menu">
                    <?php foreach(array('1.5A', '2.0A', '2.5A', '3.0A', '3.5A', '4.0A', '20.0A') as $res): ?>
                      <li><a href="<?=$baseurl?>nrlist/nonredundant/<?=$type?>/<?=$release_id?>/<?=$res?>/<?=$criterion?>/<?=$count_limit?>/csv"><?=$res?></a></li>
                    <?php endforeach; ?>
                    <li class="divider"></li>
                    <li><a href="<?=$baseurl?>nrlist/nonredundant/<?=$type?>/<?=$release_id?>/all/<?=$criterion?>/<?=$count_limit?>/csv">All</a></li>
                  </ul>
                </li>
            </ul>

            <?=$class?>

          </div>
        </div>
      </div>

    <script type="text/javascript" src="<?=$baseurl?>js/jquery.dataTables.min.js"></script>

    <style>
.table_controls.well {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.table_controls label {
    margin: 0;
    white-space: nowrap;
}

.table_controls input[type="search"] {
    margin-left: 8px;
    display: inline-block;
    width: auto;
}
</style>

    <script>
    $(function() {

        $('#sort').dataTable({
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "sDom": '<"table_controls well"fi>t'
        });

        $(".pdb").click(LookUpPDBInfo);

    });
    </script>