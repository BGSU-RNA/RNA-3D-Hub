<body class="motifview">
    <div class="container">

      <div class="content">
      
        <div class="page-header">
          <h1><?php echo $title;?> <small>Release <?php echo $release_id;?></small></h1>
        </div>
        
        <div class="row">
          <div class="span16 block interactions">
            <h2></h2>            
            <?php echo $table;?>            
          </div>
        </div>
        
        <br>
        

        <div class="span16 block row">
            <div class="span6" id="jmol" >
                <div class="block jmolheight">
                    <script type="text/javascript">
                        jmolInitialize(" /jmol");
                        jmolSetAppletColor("#ffffff");
                        jmolApplet(340, "javascript appletLoaded()");
                    </script>                          
                </div>
                <input type='button' id='neighborhood' class='btn' value="Show neighborhood">
                <input type='button' id='all' class='btn' value="Show all">				                    
           </div>
            
            <div class="span3 block">
                <div id="prev_next_controls">
                    <input type='button' id='prev' class='btn' value='Previous'>
                    <input type='button' id='next' class='btn' value="Next">
                </div>
                <div id="checkboxes" class="jmolheight">
                    <?php echo $checkboxes;?>        
                </div>
            </div>

            <div class="span6 jmolheight mdmatrix">
                <?php echo $matrix;?>        
            </div>            
        </div>

        
        <br>
        
        <div class="row">
            <div class="span16 history">
                <h4>History</h4>
                <?php echo $history;?>                
            </div>
        </div>
        
      </div>
      
      <script>
        
        $('.twipsy').twipsy();
        
      	function appletLoaded (){
			var timeoutID = window.setTimeout(function(){
	    		jmolInlineLoader.init({
                    chbxClass: 'jmolInline',
                    serverUrl: 'http://leontislab.bgsu.edu/Motifs/jmolInlineLoader/nt_coord_new.php',
                    neighborhoodButtonId: 'neighborhood',
                    showNextButtonId: 'next',
                    showPreviousButtonId: 'prev',
                    showAllButtonId: 'all'
	    		});
			}, 1500);
      	}    

      </script>      	