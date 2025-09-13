    <div class="container loops_all_view">

      <div class="content">
        <div class="page-header">
          <h1><?php echo $title;?></h1>
        </div>
        <div class="row">
          <div class="span12">

            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#ils">Internal Loops</a></li>
                <li><a href="#hls">Hairpin Loops</a></li>
                <li><a href="#j3">3-way Junctions</a></li>
            </ul>


            <div class="tab-content">

                <div class="tab-pane active" id="ils">
                    <?=$tables['IL']?>
                </div>

                <div class="tab-pane" id="hls">
                    <?=$tables['HL']?>
                </div>

                <div class="tab-pane" id="j3">
                    <?=$tables['J3']?>
                </div>

            </div>

          </div>


        </div>
      </div>



