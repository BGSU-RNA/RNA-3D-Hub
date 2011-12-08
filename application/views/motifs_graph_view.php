    <div class="container motifs_graph_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small>Graph view</small>
          </h1>
          <a href='<?=$alt_view?>'>Switch to list view</a>
        </div>

        <div class="row">

          <div class="span9">
            <div id="cytoscapeweb" class="block"></div>
            <div id='buttons'>
                <input id='edgeDisc'/><button class='btn' id='edgeDiscBtn'>Filter by edge discrepancy</button><br>
                <input id='numCand'/><button class='btn' id='numCandBtn'>Filter by number of candidates</button><br>
                <input id='numNT'/><button class='btn' id='numNtBtn'>Filter by number of nucleotides</button><br>
                <input id='selectNode'/><button class='btn' id='selectNodeBtn'>Highlight group</button>
            </div>
          </div>

          <div class="span6" id="jmol" >
              <div class="block jmolheight">
                  <script type="text/javascript">
                      jmolInitialize(" /jmol");
                      jmolSetAppletColor("#ffffff");
                      jmolApplet(340);
                  </script>
              </div>
              <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
              <input type='button' id='neighborhood' class='btn' value="Show numbers">

              <div id='signature'></div><br>
              <ul class="media-grid"><a href='#' id='varna'></a></ul>
          </div>

        </div>
      </div>



    <script>
        $(document).ready(function() {

            $('#edgeDiscBtn').click(function() {
                var edgeDisc = $(this).prev().val();
                if (edgeDisc != '') {
                    vis.filter("edges",function(edge) {
                        return edge.data.disc <= edgeDisc;
                    });
                }
            });

            $('#numCandBtn').click(function() {
                var numCand = $(this).prev().val();
                if (numCand != '') {
                    vis.filter("nodes",function(node) {
                        return node.data.cands >= numCand;
                    });
                }
            });

            $('#numNtBtn').click(function() {
                var numNt = $(this).prev().val();
                if (numNt != '') {
                    vis.filter("nodes",function(node) {
                        return node.data.numnt >= numNt;
                    });
                }
            });

            $('#selectNodeBtn').click(function() {
                var id = $(this).prev().val();
                vis.select("nodes",[id]);
            });

            $('#neighborhood').click(function() {
                var t = $(this);
                if ( t.attr('value') == 'Show neighborhood' ) {
                    t.attr('value', 'Hide neighborhood');
                    jmolScript('frame *;display displayed or 1.2;');
                } else {
                    t.attr('value', 'Show neighborhood');
                    jmolScript('frame *;display displayed and not 1.2;');
                }
            });

            $('#showNtNums').click(function() {
                var t = $(this);
                if ( t.attr('value') == 'Show numbers' ) {
                    t.attr('value', 'Hide numbers');
                    jmolScript('select {*.P},{*.CA};label %[sequence]%[resno];');
                } else {
                    t.attr('value', 'Show numbers');
                    jmolScript('label off;');
                }
            });

            // id of Cytoscape Web container div
            var div_id = "cytoscapeweb";

            // initialization options
            var options = {
                swfPath: "<?=$baseurl?>cytoscapeweb/swf/CytoscapeWeb",
                flashInstallerPath: "<?=$baseurl?>cytoscapeweb/swf/playerProductInstall"
            };

            var layout = {
//                 name: "Radial",
//                 options: {angleWidth: 360}
                name: "ForceDirected",
                options: {gravitation: -10500, autoStabilize: true}
            };

            var visual_style = {
                global: {
                   backgroundColor: "#f5f5f5"
                },
                nodes: {
                    size: {
                        defaultValue: 45,
                        continuousMapper: { attrName: "cands", minValue: 45, maxValue: 120, minAttrValue: 1, maxAttrValue: 100 }
                    },
                    color: {
                        continuousMapper: { attrName: "within", minValue: "#ff3300", maxValue: "#0033ff" }
                    }
                },
                edges: {
//                            width: {
//                               continuousMapper: { attrName: "links", minValue: 1, maxValue: 10 }
//                          },
                    color: {
                        continuousMapper: { attrName: "disc", minValue: "#ff3300", maxValue: "#0033ff" }
                    }
                }
            };

            // init and draw
            vis = new org.cytoscapeweb.Visualization(div_id, options);

            // callback when Cytoscape Web has finished drawing
            vis.ready(function() {

                    vis.addListener("click", "nodes", function(event) {
                        handle_click(event);
                    })

                    .addListener("click", "edges", function(event) {
                        handle_edge_click(event);
                    });

                    vis.addContextMenuItem("Select first neighbors", "nodes",
                         function (evt) {
                             var rootNode = evt.target;
                             var fNeighbors = vis.firstNeighbors([rootNode]);
                             var neighborNodes = fNeighbors.neighbors;
                             vis.select([rootNode]).select(neighborNodes);
                         }
                     );

//                     function handle_edge_click(event) {
//                          var target = event.target;
//                          $('#signature').html('The closest link is between loops '+target.data.connection+'<br>'+'with discrepancy '+target.data.disc);
//                     }

                    function handle_click(event) {
                         var target = event.target;
                         $('#signature').html(target.data.signature);
                         show_motif_exemplar_in_jmol( target.data.id );
                         $('#neighborhood').attr('value','Show neighborhood');
                         $('#varna').html('<img class="thumbnail" src="http://rna.bgsu.edu/img/MotifAtlas/<?=$img_loc?>/'+target.data.id+'.png"/>');
                    }

                vis.visualStyle(visual_style);
            });
            var graphml = '<?=$graphml?>';
            vis.draw({ network: graphml });
	});

    </script>