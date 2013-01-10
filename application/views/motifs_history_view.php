    <div class="container motifs_history_view">

      <div class="content">

        <div class="page-header">
          <h1>Detailed Release History</h1>
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
<!--
            <h3>About</h3>
            <p>Coming soon.</p>
 -->
          </div>


        </div>
      </div>



<!--
        <div class="row">
          <div class="span12">
            <?=$table?>
          </div>

          <div class="span3 offset1">
            <h3>About</h3>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus scelerisque feugiat lorem at luctus. Pellentesque sem diam, laoreet hendrerit pulvinar id, placerat non purus. Curabitur tempor, velit vel bibendum bibendum, lectus massa facilisis nunc, et egestas libero libero eget mi. Nulla nec nunc eu nunc placerat tincidunt. Praesent urna purus, ultrices sit amet semper quis, consequat id sem. Donec quis diam sit amet elit ornare lacinia at quis nunc. Cras ac auctor dolor. Donec sit amet quam quam. Donec vel leo nisl. Sed eu felis vel lorem rhoncus feugiat.
            </p>
          </div>
        </div>

      </div>
 -->


    <script>
        $(function () {
            $("#sort").tablesorter();
        })
    </script>
