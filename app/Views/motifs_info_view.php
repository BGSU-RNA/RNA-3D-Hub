    <div class="container motifs_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small><?=$status?></small>
          </h1>
          <a href='<?=$alt_view?>'>Switch to graph view</a>
          <a href='<?=$polymorph_url?>'>Polymorphs</a>
        </div>
        <div class="row">
          <div class="span15">

            <h2></h2>
            <?php echo $counts; echo $table;?>

          </div>

        </div>
      </div>

    <script>

        $("#sort").tablesorter();

    </script>
