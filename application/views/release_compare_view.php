<body class="release_compare_view">
    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1><?php echo $title;?></h1>
        </div>
        <div class="row">
          <div class="span10">

            <h2></h2>
            <form method="post" action="<?=$action?>"  />
            <?php echo $table;?>
            <br>
            <input type='submit' class='btn primary' value="Compare selected">
            </form>
            <br>

          </div>
          <div class="span4">
            <h3></h3>
          </div>
        </div>
      </div>