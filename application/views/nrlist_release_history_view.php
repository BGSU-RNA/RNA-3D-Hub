    <div class="container nrlist_release_history_view">
      <div class="content">

        <div class="page-header">
          <h1>Detailed release history of representative sets</h1>
        </div>

        <div class="row">
          <div class="span12">

            <ul class="tabs" data-tabs="tabs">
                <li><a href="#1_5A">1.5A</a></li>
                <li><a href="#2_0A">2.0A</a></li>
                <li><a href="#2_5A">2.5A</a></li>
                <li><a href="#3_0A">3.0A</a></li>
                <li><a href="#3_5A">3.5A</a></li>
                <li class="active"><a href="#4_0A">4.0A</a></li>
                <li><a href="#20_0A">20.0A</a></li>
                <li><a href="#all">All</a></li>
            </ul>

            <div class="tab-content">
            <?php
            $labels = array('1_5A','2_0A','2_5A','3_0A','3_5A','4_0A','20_0A','all');
            foreach ($labels as $label) {
                if ($label == '4_0A') {
                    echo "<div class='tab-pane active' id='$label'>";
                } else {
                    echo "<div class='tab-pane' id='$label'>";
                }

                echo "$tables[$label]</div>";
            }
            ?>
            </div>
          </div>

          <div class="span3 offset1">
<!--
            <h3>About</h3>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus scelerisque feugiat lorem at luctus. Pellentesque sem diam, laoreet hendrerit pulvinar id, placerat non purus. Curabitur tempor, velit vel bibendum bibendum, lectus massa facilisis nunc, et egestas libero libero eget mi. Nulla nec nunc eu nunc placerat tincidunt. Praesent urna purus, ultrices sit amet semper quis, consequat id sem. Donec quis diam sit amet elit ornare lacinia at quis nunc. Cras ac auctor dolor. Donec sit amet quam quam. Donec vel leo nisl. Sed eu felis vel lorem rhoncus feugiat.
            </p>
 -->
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
            $("#alltable").tablesorter();
        })

    </script>
