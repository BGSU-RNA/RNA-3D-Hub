    <div class="container motifs_compare_view">

      <div class="content">

        <div class="page-header">
          <h1><?php echo $title;?></h1>
        </div>

        <div class="row">
          <div class="span8">

            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#ils">Internal Loops</a></li>
                <li><a href="#hls">Hairpin Loops</a></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane active" id="ils">
                    <form method="post" action="<?=$action_il?>"  />
                    <?=$table['ils']?>
                    <br>
                    <input type='submit' class='btn primary' value="Compare selected">
                    </form>
                </div>

                <div class="tab-pane" id="hls">
                    <form method="post" action="<?=$action_hl?>"  />
                    <?=$table['hls']?>
                    <br>
                    <input type='submit' class='btn primary' value="Compare selected">
                    </form>
                </div>

            </div>

          </div>

          <div class="span4 offset2">
<!--
            <h3>About</h3>
            <p>Coming soon.</p>
 -->
          </div>

        </div>

      </div>