    <div class="container nrlist_class_view">

      <div class="content">
        <div class="page-header">
          <h1>Equivalence class <?=$title?>
            <small><?=$status?></small>
          </h1>
        </div>


            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#members">Members (<?=$num_members?>)</a></li>
                <li><a href="#history">History</a></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane active" id="members">
                  <div class="span16">
                      <div>
                        <?=$members?>
                      </div>
                  </div>
                </div>

                <div class="tab-pane" id="history">
                  <div class="span16">
                      <h3>Release history</h3>
                      <div class="horizontal_overflow">
                        <?=$releases?>
                      </div>
                  </div>
                  <br>

                  <div class="span16">
                      <h3>Parents</h3>
                      <div class="parents maxheight400">
                        <?=$parents?>
                      </div>
                  </div>

                  <div class="span16">
                      <h3>Children</h3>
                      <div class="parents maxheight400">
                        <?=$children?>
                      </div>
                  </div>

                </div>
            </div>

        </div>


    <script>
        $(function () {
            $("#members_table").tablesorter();
            $(".pdb").click(LookUpPDBInfo);
        })

    </script>
