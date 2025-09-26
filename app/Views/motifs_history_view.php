    <div class="container motifs_history_view">

      <div class="content">

        <div class="page-header">
          <h1>Detailed Motif Atlas Release History</h1>
          <br>Under construction as of 2024-09-06
        </div>

        <div class="row">
          <div class="span16">

            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#ils">Internal Loops</a></li>
                <li><a href="#hls">Hairpin Loops</a></li>
            </ul>


            <div class="tab-content">

                <div class="tab-pane active" id="ils">
                    <?=$table_il?>
                </div>

                <div class="tab-pane" id="hls">
                    <?=$table_hl?>
                </div>

            </div>

          </div>

          <div class="span4 offset1">
          </div>


        </div>
      </div>


    <script>
        $(function () {
            $("#sort").tablesorter();
        })
    </script>
