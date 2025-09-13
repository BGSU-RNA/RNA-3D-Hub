    <div class="container loops_sfjmol_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $pdb;?>
          </h1>
        </div>


        <div class="row span16">
            <div class="span10 block" id="jmol">
                <script type="text/javascript">
                    jmolInitialize("/jmol");
                    jmolSetAppletColor("#ffffff");
                    jmolApplet(550, "load <?=$files?><?=$pdb?>.pdb;javascript dcc();");
                </script>
            </div>

            <div class="span5 controls">
                <ul class="inputs-list">
                <?php
                foreach ($fields as $field) {
                echo <<<EOT
                    <label>
                        <input type="radio" name="d">
                        <span>$field</span>
                    </label>
EOT;
                }
                ?>
                </ul>
                <br>
                <div class="input">
                <input class="span2" type="text" id="minvalue" value="<?=$min['sfcheck_correlation']?>"><span id='minhelp' class="help-inline">min value</span>
                </div>
                <div class="input">
                <input class="span2" type="text" id="maxvalue" value="<?=$max['sfcheck_correlation']?>"><span id='maxhelp' class="help-inline">max value</span>
                </div>
                <br>
                <button class="primary btn" id="minmax">Apply</button>
            </div>
            </div>
      </div>

    <script>
        function color_by_property() {
            var p = $("input[type=radio]:checked").next().html();
            var min_v = $("#minvalue").attr('value');
            var max_v = $("#maxvalue").attr('value');
            jmolScript('color atoms property_'+p+' "low" absolute '+min_v+' '+max_v+';');
        }

        function update_min_max() {
            var p = $(this).next().html();
            eval("$('#minhelp').html(json_min."+p+");");
            eval("$('#minvalue').attr('value',json_min."+p+");");
            eval("$('#maxhelp').html(json_max."+p+");");
            eval("$('#maxvalue').attr('value',json_max."+p+");");
        }

        var json_min={<?php
            foreach ($min as $key => $value) {
                echo "$key:$value,";
            }?>'foo':'bar'};

        var json_max={<?php
            foreach ($max as $key => $value) {
                echo "$key:$value,";
            }?>'foo':'bar'};

        function dcc() {
                jmolScript('spacefill off');
                <?php
                $i = 1;
                foreach ($fields as $field) {
                    echo "jmolScript('{*}.property_{$field}=data(load(\"{$files}{$pdb}.dcc\"),{$i},0,1);');";
                    $i++;
                }
                ?>
        }

        $(function () {
            $("input[type='radio']").click(update_min_max);
            $("input[type='radio']").first().attr('checked','checked');
            $("#minmax").click(color_by_property);
        })
    </script>
