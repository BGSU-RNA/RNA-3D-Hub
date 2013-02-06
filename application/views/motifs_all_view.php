    <div class="container release_all_view">

      <div class="content">
        <div class="page-header">
          <h1><?php echo $title;?></h1>
        </div>
        <div class="row">
          <div class="span9">

            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#ils">Internal Loops</a></li>
                <li><a href="#hls">Hairpin Loops</a></li>
            </ul>


            <div class="tab-content">

                <div class="tab-pane active" id="ils">
                    <?=$table['ils']?>
                </div>

                <div class="tab-pane" id="hls">
                    <?=$table['hls']?>
                </div>

            </div>

          </div>

          <div class="span4 offset1">
            View <a href="<?=$baseurl?>/motifs/2ds">2D diagrams labeled with motif instances</a>

          </div>


        </div>
      </div>



