    <div class="container pdb_interactions_view">

      <div class="content">
        <div class="page-header">
          <h1><?=strtoupper($pdb_id)?>
          <small><?=strtoupper($method)?> <?=$interaction_type?> pairwise interaction annotations
            <?php if ($analyzed_structure == 'AU'): ?>
            of file <?=strtoupper($pdb_id)?>.pdb
            <?php elseif ($analyzed_structure == 'BA1'): ?>
            of file <?=strtoupper($pdb_id)?>.pdb1
            <?php endif; ?>
          </small>
          <small><a class="btn pull-right success" href="<?=$current_url?>/csv">Download</a></small>
          </h1>
        </div>

        <!-- navigation -->
        <div class="row">
          <div class="span16">
            <ul class="tabs">
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>">Summary</a></li>
                <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/motifs">Motifs</a></li>
                <li class="dropdown active" data-dropdown="dropdown">
                <a href="#" class="dropdown-toggle">Interactions</a>
                  <ul class="dropdown-menu">
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basepairs">Base-pair</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/stacking">Base-stacking</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/basephosphate">Base-phosphate</a></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/baseribose">Base-ribose</a></li>
                    <li class="divider"></li>
                    <li><a href="<?=$baseurl?>pdb/<?=$pdb_id?>/interactions/<?=$method?>/all">All interactions</a></li>
                  </ul>
                </li>
            </ul>
          </div>
        </div>
        <!-- end navigation -->

        <div class="row">

          <!-- annotations -->
          <div class="span8" id="left_content">
            <pre><?=$table?></pre>
          </div>
          <!-- end annotations -->

          <div class="span6 well" id="jmol"></div>

        </div>
      </div>


    <script>

        jmolInitialize("/jmol");
        jmolSetDocument(0);
        jmolSetAppletColor("#f5f5f5");

        $('pre').on('click', 'span', function(){
            // hide all previously displayed popovers
            $('.popover-displayed').removeClass('popover-displayed')
                                   .popover('hide')
                                   .unbind();
            var a = $(this);
            var unit_id = a.html().trim();
            var content = '<a href="http://rna.bgsu.edu/' +
                          get_rna3dhub_environment() +  '/unitid/describe/'
                          + unit_id + '">Details</a>';
            a.popover({
              offset: 10,
              content: function(){return content;},
              title: function(){return 'Unit id ' + unit_id;},
              delayOut: 1200,
              html: true,
              animate: false,
              placement:'right'
            });
            a.popover('show');
            a.addClass('popover-displayed');
        });

        $('.jmolInline').click(function(){
            var jmolApp = $('#jmolApplet0');
            var jmolDiv = $('#jmol');
            $this = $(this);

            // launch jmol if necessary
            if (jmolApp.length == 0 ) {
                jmolDiv.html( jmolApplet(300, "", 0) )
                       .append('<label><input type="checkbox" id="showNtNums">Nucleotide numbers</label>')
                       .append('<input type="button" class="btn" id="neighborhood" value="Show neighborhood">')
                       .show();
            }

            // clear jmol window
            jmolScript('zap;');

            $('a.current').removeClass('current').addClass('viewed');
            $this.addClass('current');

            // reset the state of the system
            $.jmolTools.numModels = 0;
            $.jmolTools.stereo = false;
            $.jmolTools.neighborhood = false;
            $('#neighborhood').val('Show neighborhood');
            $.jmolTools.models = {};
            // unbind all events
            $('#stereo').unbind();
            $('#neighborhood').unbind();
            $('#showNtNums').unbind();

            var data_coord = $this.prev().html() + ',' + $this.next().html();
            data_coord = data_coord.replace(/\s+/g, '');
            $('#tempJmolToolsObj').remove();
            $('body').append("<input type='radio' id='tempJmolToolsObj' data-coord='" + data_coord + "'>");
            $('#tempJmolToolsObj').hide();
            $('#tempJmolToolsObj').jmolTools({
                showNeighborhoodId: 'neighborhood',
                showNumbersId: 'showNtNums',
            }).jmolToggle();
        });

        // position jmol
        var offset_left = $('#left_content').offset().left + 470; // 530 = span9 width
        var offset_top  = $('#left_content').offset().top;
        $('#jmol').css('position','fixed')
                  .css('left',offset_left)
                  .css('top',offset_top)
                  .hide();


      </script>