    <div class="container nrlist_release_compare_view">

      <div class="content">
        <div class="page-header">
          <h1>Compare representative set releases</h1>
          <br>Note:  Release 2 must be chosen to be the most recent release, otherwise a blank result is returned.
          This restriction was imposed to reduce the load from bots crawling the web.
        </div>
        <div class="row">
          <div class="span8">

            <form method="post" action="<?=$action?>" />
            <input type='submit' class='btn primary' value="Compare selected">
            <div>
                <?=$table?>
            </div>
            </form>
            <br>

          </div>
          <div class="span4 offset1">
<!--
            <h3>Help</h3>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus scelerisque feugiat lorem at luctus. Pellentesque sem diam, laoreet hendrerit pulvinar id, placerat non purus. Curabitur tempor, velit vel bibendum bibendum, lectus massa facilisis nunc, et egestas libero libero eget mi. Nulla nec nunc eu nunc placerat tincidunt. Praesent urna purus, ultrices sit amet semper quis, consequat id sem. Donec quis diam sit amet elit ornare lacinia at quis nunc. Cras ac auctor dolor. Donec sit amet quam quam. Donec vel leo nisl. Sed eu felis vel lorem rhoncus feugiat.
            </p>
 -->
          </div>
        </div>
      </div>