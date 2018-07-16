    <div class="container nrlist_release_view">

      <div class="content">
        <div class="page-header">
          <h1><?=$title?>
          <br>
          <small>Release <?=$release_id?>, <?=$description?></small>
          </h1>
        </div>

        <div class="row">
          <div class="span16">
            <ul class="tabs" data-tabs="tabs">
                <li><a>Resolution cutoffs:</a></li>
                <?php foreach(array('1.5A', '2.0A', '2.5A', '3.0A', '3.5A', '4.0A', '20.0A', 'all') as $res): ?>
                  <?php if ($resolution == $res): ?>
                    <li class="active"><a href="<?=$baseurl?>nrlist/release/<?=$release_id?>/<?=$res?>"><?=ucfirst($res)?></a></li>
                  <?php else: ?>
                    <li><a href="<?=$baseurl?>nrlist/release/<?=$release_id?>/<?=$res?>"><?=ucfirst($res)?></a></li>
                  <?php endif; ?>
                <?php endforeach; ?>
                <li class="dropdown" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Download</a>
                  <ul class="dropdown-menu">
                    <?php foreach(array('1.5A', '2.0A', '2.5A', '3.0A', '3.5A', '4.0A', '20.0A') as $res): ?>
                      <li><a href="<?=$baseurl?>nrlist/download/<?=$release_id?>/<?=$res?>/csv"><?=$res?></a></li>
                    <?php endforeach; ?>
                    <li class="divider"></li>
                    <li><a href="<?=$baseurl?>nrlist/download/<?=$release_id?>/all/csv">All</a></li>
                  </ul>
                </li>
            </ul>

            <?=$counts?>

            <?=$class?>

          </div>



        </div>
      </div>

    <script type="text/javascript" src="<?=$baseurl?>js/jquery.dataTables.min.js"></script>

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