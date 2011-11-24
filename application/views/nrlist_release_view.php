<body class="nrlist_release_view">
    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1>Nonredundant lists of RNA structures
          <small>Release <?=$release_id?>, <?=$description?></small>
          </h1>
        </div>

        <div class="row">
          <div class="span16">

            <ul class="tabs" data-tabs="tabs">
                <li><a href="#1_5A">1.5A</a></li>
                <li><a href="#2_0A">2.0A</a></li>
                <li><a href="#2_5A">2.5A</a></li>
                <li><a href="#3_0A">3.0A</a></li>
                <li><a href="#3_5A">3.5A</a></li>
                <li class="active"><a href="#4_0A">4.0A</a></li>
                <li><a href="#20_0A">20.0A</a></li>
                <li><a href="#all">All</a></li>
                <li><a href="#download">Download</a></li>
            </ul>

            <div class="tab-content" id="my-tab-content">
                <div class="tab-pane" id="1_5A">
                    <div >
                        <?php echo $class['1.5'];?>
                    </div>
                </div>
                <div class="tab-pane" id="2_0A">
                    <div >
                        <?php echo $class['2.0'];?>
                    </div>
                </div>
                <div class="tab-pane" id="2_5A">
                    <div >
                        <?php echo $class['2.5'];?>
                    </div>
                </div>
                <div class="tab-pane" id="3_0A">
                    <div >
                        <?php echo $class['3.0'];?>
                    </div>
                </div>
                <div class="tab-pane" id="3_5A">
                    <div >
                        <?php echo $class['3.5'];?>
                    </div>
                </div>
                <div class="tab-pane active" id="4_0A">
                    <div >
                        <?php echo $class['4.0'];?>
                    </div>
                </div>
                <div class="tab-pane" id="20_0A">
                    <div >
                        <?php echo $class['20.0'];?>
                    </div>
                </div>
                <div class="tab-pane" id="all">
                    <div >
                        <?php echo $class['all'];?>
                    </div>
                </div>
                <div class="tab-pane" id="download">
                    Coming soon.
                </div>
            </div>

          </div>



        </div>
      </div>

    <script>
        $(function () {
            $('.tabs').tabs()
            $("#1_5Atable").tablesorter();
            $("#2_0Atable").tablesorter();
            $("#2_5Atable").tablesorter();
            $("#3_0Atable").tablesorter();
            $("#3_5Atable").tablesorter();
            $("#4_0Atable").tablesorter();
            $("#20_0Atable").tablesorter();
            $("#allAtable").tablesorter();
        })

    </script>
