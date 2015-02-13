    <div class="container motifs_graph_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small>
              Graph view
              <a href='<?=$alt_view?>'>Switch to list view</a>
            </small>
          </h1>
        </div>

        <div class="row">

          <div class="span9">
            <div id="cytoscapeweb" class="block"></div>
            <div id='buttons'>
                <input id='edgeDisc' placeholder="from 0 to 1.0"/><button class='btn' id='edgeDiscBtn'>Filter by edge discrepancy</button><br>
                <input id='numCand' placeholder="1 or more"/><button class='btn' id='numCandBtn'>Filter by motif instances</button><br>
                <input id='numNT' placeholder="4 or more for IL, 3 or more for HL"/><button class='btn' id='numNtBtn'>Filter by number of nucleotides</button><br>
                <input id='selectNode' placeholder="motif id"/><button class='btn' id='selectNodeBtn'>Highlight group</button>
            </div>
            <div>
              <p>
              <span class="label notice">Info</span>
              Graph view shows connections between motif groups that remain after all
              filtering and quality assurance steps. The initial layout is
              calculated at geometric discrepancy 0.5.
              </p>
              <p>
              <strong>Clicking on a node</strong> shows the exemplar of the selected motif.
              <strong>Clicking on an edge</strong> provides information
              about the closest link between the two motifs,
              and the two connected motifs which can be compared
              by following the <strong>Compare motifs</strong> link, which will appear below the 3D structure.
              </p>
              <p>
              <strong>Node size</strong> reflects the number of instances.
              <strong>Node color</strong> indicates the maximum discrepancy within
              the group (red means low discrepancy, blue means high discrepancy).
              </p>

              <a href="http://cytoscapeweb.cytoscape.org/" target="_blank">
                  <img src="http://cytoscapeweb.cytoscape.org/img/logos/cw_s.png" alt="Cytoscape Web"/>
              </a>
            </div>
          </div>

          <div class="span6" id="jmol" >
              <div class="block jmolheight">
<script>
    var Info = {
        width: 340,
        height: 340,
        debug: false,
        color: '#f5f5f5',
        addSelectionOptions: false,
        use: 'HTML5',
        j2sPath: '<?=$baseurl?>/js/jsmol/j2s/',
        disableInitialConsole: true
    };

    var jmolApplet0 = Jmol.getApplet('jmolApplet0', Info);

    // these are conveniences that mimic behavior of Jmol.js
    function jmolCheckbox(script1, script0,text,ischecked) {Jmol.jmolCheckbox(jmolApplet0,script1, script0, text, ischecked)};
    function jmolButton(script, text) {Jmol.jmolButton(jmolApplet0, script,text)};
    function jmolHtml(s) { document.write(s) };
    function jmolBr() { jmolHtml("<br />") };
    function jmolMenu(a) {Jmol.jmolMenu(jmolApplet0, a)};
    function jmolScript(cmd) {Jmol.script(jmolApplet0, cmd)};
    function jmolScriptWait(cmd) {Jmol.scriptWait(jmolApplet0, cmd)};
</script>
              </div>
              <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
              <input type='button' id='showNtNums' class='btn' value="Show numbers">

              <div class="row">
              <div id='signature' class="span3"></div>
              <div class="span3">
              <ul class="media-grid"><a href='#' id='varna'></a></ul>
              </div>
              </div>
          </div>

        </div>
      </div>

    <script>



    $(document).ready(function() {

        function jmol_neighborhood_button_click(id) {
            $('#'+id).click(function() {
                var t = $(this);
                if ( t.attr('value') == 'Show neighborhood' ) {
                    t.attr('value', 'Hide neighborhood');
                    jmolScript('frame *;display displayed or 1.2;');
                } else {
                    t.attr('value', 'Show neighborhood');
                    jmolScript('frame *;display displayed and not 1.2;');
                }
            });
        }

        function jmol_show_nucleotide_numbers_click(id) {
            $('#'+id).click(function() {
                var t = $(this);
                if ( t.attr('value') == 'Show numbers' ) {
                    t.attr('value', 'Hide numbers');
                    jmolScript('select {*.P},{*.CA};label %[sequence]%[resno];color labels black;');
                } else {
                    t.attr('value', 'Show numbers');
                    jmolScript('label off;');
                }
            });
        }

        function apply_jmol_styling() {
                jmolScript('select [U];color navy;');
                jmolScript('select [G]; color chartreuse;');
                jmolScript('select [C]; color gold;');
                jmolScript('select [A]; color red;');
                jmolScript('select 1.2; color grey; color translucent 0.8;');
                jmolScript('select protein; color purple; color translucent 0.8;');
                jmolScript('select 1.0;spacefill off;center 1.1;');
                jmolScript('frame *;display displayed and not 1.2;');
                jmolScript('select hetero;color pink;');
                jmolScript('zoom 150');
        }

        function show_motif_exemplar_in_jmol(id) {
            $.post('http://rna.bgsu.edu/' + get_rna3dhub_environment() + '/ajax/get_exemplar_coordinates', { motif_id: id }, function(data) {
                jmolScript('zap;');
                jmolScriptWait("load DATA \"append structure\"\n" + data + 'end "append structure";')
                apply_jmol_styling();
            });
        }

        $('#varna').hide();

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

        jmol_neighborhood_button_click('neighborhood');
        jmol_show_nucleotide_numbers_click('showNtNums');

        // id of Cytoscape Web container div
        var div_id = "cytoscapeweb";

        // initialization options
        var options = {
            swfPath: "<?=$baseurl?>cytoscapeweb/swf/CytoscapeWeb",
            flashInstallerPath: "<?=$baseurl?>cytoscapeweb/swf/playerProductInstall"
        };

        var visual_style = {
            global: {
               backgroundColor: "#f5f5f5"
            },
            nodes: {
                selectionGlowBlur: 8,
                selectionGlowColor: 'green',
                size: {
                    defaultValue: 45,
                    continuousMapper: { attrName: "cands", minValue: 45, maxValue: 120, minAttrValue: 1, maxAttrValue: 100 }
                },
                color: {
                    continuousMapper: { attrName: "within", minValue: "#ff0000", maxValue: "#0000ff" }
                }
            },
            edges: {
                selectionGlowBlur: 8,
                selectionGlowColor: 'yellow',
                color: {
                    continuousMapper: { attrName: "disc", minValue: "#ff0000", maxValue: "#0000ff" }
                }
            }
        };

        // init and draw
        vis = new org.cytoscapeweb.Visualization(div_id, options);

        // callback when Cytoscape Web has finished drawing
        vis.ready(function() {

            vis.addListener("click", "nodes", function(event) {
                handle_node_click(event);
            })

            .addListener("click", "edges", function(event) {
                handle_edge_click(event);
            })

            .addContextMenuItem("Select first neighbors", "nodes",
                 function (evt) {
                     var rootNode = evt.target;
                     var fNeighbors = vis.firstNeighbors([rootNode]);
                     var neighborNodes = fNeighbors.neighbors;
                     vis.select([rootNode]).select(neighborNodes);
                 }
             )

            // force the layout at low discrepancy for the initial drawing
            .filter("edges",function(edge) {
                return edge.data.disc <= 0.5;
            })

            .layout('Circle');

            function handle_edge_click(event) {
                 var target = event.target;
                 var env = get_rna3dhub_environment();
                 var text = 'Motifs <a href="http://rna.bgsu.edu/' + env + '/motif/view/' + target.data.source + '" target="_blank">' + target.data.source + '</a>';
                 text += ' and ' + '<a href="http://rna.bgsu.edu/' + env + '/motif/view/' + target.data.target + '" target="_blank">' + target.data.target + '</a>';
                 text += ' are connected by loops ';
                 text += target.data.connection.split(' ').join(' and ');
                 text += ' at discrepancy ' + target.data.disc.toFixed(4) + '.';
                 text +=  ' <a href="http://rna.bgsu.edu/' + env + '/motif/compare/'+ target.data.source + '/' + target.data.target + '" target="_blank">Compare motifs</a>';
                 $('#signature').html(text);

                 text =  '<img class="thumbnail" src="http://rna.bgsu.edu/img/MotifAtlas/<?=$img_loc?>/'+target.data.source+'.png"/>';
                 text += '<img class="thumbnail" src="http://rna.bgsu.edu/img/MotifAtlas/<?=$img_loc?>/'+target.data.target+'.png"/>'
                 $('#varna').html(text).show();
            }

            function handle_node_click(event) {
                 var target = event.target;
                 var env = get_rna3dhub_environment();
                 var text = 'Motif <a href="http://rna.bgsu.edu/' + env + '/motif/view/' + target.data.id + '" target="_blank">' + target.data.id + '</a>';
                 text += '<dl><dt>Basepair signature</dt><dd>' + target.data.signature +  '</dd>';
                 text += '<dt>Number of instances</dt><dd>' + target.data.cands + '</dd>';
                 text += '<dt>Number of nucleotides</dt><dd>' + target.data.numnt + '</dd></dl>';
                 $('#signature').html(text);
                 show_motif_exemplar_in_jmol( target.data.id );
                 $('#neighborhood').attr('value','Show neighborhood');
                 $('#varna').html('<img class="thumbnail" src="http://rna.bgsu.edu/img/MotifAtlas/<?=$img_loc?>/'+target.data.id+'.png"/>').show();
            }

            vis.visualStyle(visual_style);
        });

        var graphml = '<?=$graphml?>';
        vis.draw({ network: graphml });

	});

    </script>