/*
 *
 *
 *
 */

 var RSRZ_data = {};
 var bulge_data = {};
 var RSR_data = {};
 var plasmaColors = ["#0d0887","#110889","#17078b","#1b078d","#20068f","#240691","#2a0693","#300596","#340597","#3a049a","#3d049b","#43049e","#4903a0","#4b03a1","#5003a2","#5303a2","#5803a3","#5c03a3","#6103a4","#6603a5","#6903a5","#6e03a6","#7103a6","#7603a7","#7b03a8","#7d03a8","#8106a6","#8408a5","#880ba4","#8a0da2","#8e10a1","#93139f","#95149e","#99179c","#9c199b","#a01c99","#a41f98","#a72197","#a92395","#ac2693","#af2990","#b32d8d","#b52f8b","#b83388","#bb3587","#be3984","#c13b82","#c43f7f","#c8427c","#ca457a","#cc4778","#cd4976","#d04d74","#d25071","#d4536f","#d6566d","#d8596b","#da5c68","#dc5e67","#df6264","#e16561","#e36860","#e56b5d","#e66c5c","#e87059","#e97556","#eb7755","#ed7b52","#ee7e50","#f0824d","#f2864a","#f38948","#f58d46","#f69044","#f89441","#f89540","#f99a3e","#f99e3c","#f9a13a","#faa638","#faa936","#fbad34","#fbb131","#fbb430","#fcb92d","#fcbc2c","#fdc02a","#fdc328","#fcc728","#fbcc27","#fad026","#f9d526","#f8d925","#f7de25","#f5e324","#f4e723","#f3ec23","#f2f022","#f1f521","#f0f921"];

 var RSR_pair = [];
 
 for (i=0; i < plasmaColors.length; i++) {
    RSR_pair.push({
    interval: (100/plasmaColors.length)*(i+1),
    colorchoice: plasmaColors[i]
    })
 }

 function isEmpty(obj) {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}


// Utility
if ( typeof Object.create !== 'function' ) {
    Object.create = function( obj ) {
        function F() {};
        F.prototype = obj;
        return new F();
    };
}

;(function($) {

    // an object for keeping track of the whole system
    $.jmolTools = {
        neighborhood : false,
        stereo: false,
        models : {}, // all model objects, both loaded and not
        numModels: 0, // number of loaded models
        showNumbers: false,
        showRSR: false,
        showRSRZ: false
    };

    // an object for keeping track of each individual model's state
    var jmolModel = {

        init: function (options, elem) {
            var self = this; // each element
            self.elem = elem;
            self.$elem = $( elem );
            self.modelNumber = null;
            self.loaded       = false;
            self.neighborhood = false;
            self.superimposed = false;
            //self.styled       = false;
            self.checked      = false;
            self.hidden       = false;
            self.bindEvents();
        },

        bindEvents: function() {
            var self = this;
            self.$elem.on('click', self.jmolToggle );
        },

        loadData: function() {

            var self = this;
            if ( self.loaded ) { return; }

            // This AJAX call gets the bulge units
            $.ajax({
                url: $.fn.jmolTools.options.serverUrlBulgeUnit,
                type: 'GET',
                dataType: 'json',
                contentType: 'application/json',
                data: {'quality' : self.$elem.data($.fn.jmolTools.options.dataAttributeBulgeUnit)}
                }).done(function(data) {
                    //bulge_units= data;      
                    bulge_data[$.jmolTools.numModels+1] = data;
                    //console.log(bulge_data)
            });

            // This AJAX call gets the RSRZ data
            $.ajax({
                url: $.fn.jmolTools.options.serverUrlRSRZ,
                type: 'GET',
                dataType: 'json',
                //contentType: 'application/json',
                data: {'quality' : self.$elem.data($.fn.jmolTools.options.dataAttributeRSRZ)}
                }).done(function(data) {
                    RSRZ_JSON = data;      
                    RSRZ_data[$.jmolTools.numModels+1] = data;
                    console.log(RSRZ_data)
            });

            // This AJAX call gets the RSR data
            $.ajax({
                url: $.fn.jmolTools.options.serverUrlRSR,
                type: 'GET',
                dataType: 'json',
                //contentType: 'application/json',
                data: {'quality' : self.$elem.data($.fn.jmolTools.options.dataAttributeRSR)}
                }).done(function(data) {
                    RSR_JSON = data;
                    RSR_data[$.jmolTools.numModels+1] = data;
            });

            // This AJAX call gets the coordinate data
            $.ajax({
                url: $.fn.jmolTools.options.serverUrlCoord,
                type: 'POST',
                data: {'coord' : self.$elem.data($.fn.jmolTools.options.dataAttributeCoord)}
            }).done(function(data) {
                self.appendData(data);
                if ( self.loaded ) {
                    self.updateModelCounts();
                    modelNum = self.modelNumber;
                    self.superimpose();
                    self.labelnucleotides();
                    self.colorOneModel();
                    self.show();
                }
            });

               
        },

        appendData: function(data) {
            var self = this;
            // change MODEL to data_view
            if ( data.indexOf('data_view') > -1 ) {
                jmolScriptWait("load DATA \"append structure\"\n" + data + 'end "append structure";');
                //console.log(data);
                self.loaded = true;
                //console.error('Server returned: ' + data);
            }
        },

        updateModelCounts: function() {
            this.modelNumber = ++$.jmolTools.numModels;
        },

        returnModelNumber: function() {
            var self=this;
            return self.modelNumber;
        },

        // superimpose this model onto the first one using spine atoms in nucleic acids
        superimpose: function() {
            var self = this;
            if ( self.superimposed ) { return; }
            var m = self.modelNumber;
            if ( m < 2 ) { return; } // m == 1; nothing to superimpose on

                for (var i = 0; i < 3; i++) {
                // if the same number of phosphates, try to superimpose,
                // otherwise take the first four spine atoms
                var command = 'if ({*.P/' + m + '.1}.length == {*.P/1.1}) ' +
                              '{x=compare({spine/' + m + '.1},{spine/1.1});}' +
                              'else {x=compare({(spine/' + m + '.1)[1][4]},{(spine/1.1)[1][4]});};' +
                              'select ' + m + '.1,' + m + '.2,' + m + '.3; rotate selected @{x};';
                jmolScript(command);
                }

            self.superimposed = true;
        },

        labelnucleotides: function () {

            if ( $.jmolTools.showNumbers ) {
                jmolScript("select {*.C1'},{*.CA};label %[sequence]%[resno];color labels black;");
            } else {
                jmolScript('label off;');
            }

        },

        colorOneModel: function () {

            k = $.jmolTools.numModels;

            console.log("Color one model: " + k)
            
            if ($('#colorOPT :selected').val() === 'RSRZ') {
                jmolModel.styleModelRSRZ(k, k);
                $("div.showRSRZ").show(); 
                $("div.showRSR").hide(); 
            } else if ($('#colorOPT :selected').val() === 'RSR') {
                jmolModel.styleModelRSR(k, k);
                $("div.showRSR").show();
                $("div.showRSRZ").hide(); 
            } else if ($('#colorOPT :selected').val() === 'CPK') {
                jmolModel.styleModelCPK(k, k);
                $("div.showRSR").hide();
                $("div.showRSRZ").hide(); 
            } else if ($('#colorOPT :selected').val() === 'Default') {
                jmolModel.styleModel(k, k);
                $("div.showRSR").hide(); 
                $("div.showRSRZ").hide();  
            };
            
                           
        },

        styleModel: function(a,b) {
            
            for (var k=a; k <= b; k++) {
            
                command = 'select [U]/' + k + '.1; color navy;' +
                        'select [G]/' + k + '.1; color chartreuse;' +
                        'select [C]/' + k + '.1; color gold;' +
                        'select [A]/' + k + '.1; color red;' +
                        'select protein and ' + k + '.1; color CPK;' +
                        'select nucleic and ' + k + '.2; color grey;' +
                        'select protein and ' + k + '.2; color purple;' +
                        'select hetero  and ' + k + '.2; color pink;' +
                        'select nucleic and ' + k + '.3; color CPK;' +
                        'select ' + k + '.2; color translucent 0.8;' +
                        'select ' + k + '.1,' + k + '.2,' + k + '.3;' +
                        'spacefill off;' +
                        'center ' + k + '.1;' +
                        'zoom {'  + k + '.1} 0;';
         
                jmolScript(command);

            }

            
        },

        styleModelCPK: function(a,b) {
            
            for (var k=a; k <= b; k++) {
            
                command = 'select nucleic and ' + k + '.1; color CPK;' +
                        'select protein and ' + k + '.1; color CPK;' +
                        'select nucleic and ' + k + '.2; color grey;' +
                        'select protein and ' + k + '.2; color purple;' +
                        'select hetero  and ' + k + '.2; color pink;' +
                        'select ' + k + '.2; color translucent 0.8;' +
                        'select ' + k + '.1,' + k + '.2;' +
                        'spacefill off;' +
                        'center ' + k + '.1;' +
                        'zoom {'  + k + '.1} 0;';
         
                console.log(command);
                jmolScript(command);

            }

            
        },

        styleModelRSRZ: function(a,b) {

            var mod_num1 = a;

            var mod_num2 = b;

            console.log("RSRZ mod_num1: " + mod_num1);

            console.log("RSRZ mod_num2: " + mod_num2);

            command = "";

            for (var i = mod_num1; i <= mod_num2; i++) {
                
                console.log(RSRZ_data[i]);
                //var RSRZ_array_size = RSRZ_data[i].length
                
                if (RSRZ_data[i] === undefined) {
                    command += "select " + i + ".1; "  + "color grey; ";
                } else {
                    for (var k = 0; k < Object.keys(RSRZ_data[i]).length; k++){

                        var RSRZ = RSRZ_data[i][k].real_space_r_z_score;
                        var split_unitid = RSRZ_data[i][k].unit_id.split("|");

                        if (RSRZ === null){
                            command += "select " + split_unitid[4] + "/" + i + ".1;" + " color gray" + "; ";
                        } else {
                            var RSRZ = (parseFloat(RSRZ_data[i][k].real_space_r_z_score)*100)/100;
                            if (RSRZ < 1.00) {
                                command += "select " + split_unitid[4] + "/" + i + ".1;" + " color green; ";   
                            } else if (RSRZ < 2.00) {
                                command += "select " + split_unitid[4] + "/" + i + ".1;" + " color yellow; ";  
                            } else if (RSRZ < 3.00) {
                                command += "select " + split_unitid[4] + "/" + i + ".1;" + " color orange; ";  
                            } else {
                                command += "select " + split_unitid[4] + "/" + i + ".1;" + " color red; ";  
                            }
                        }
                    }

                }

                if (bulge_data[i] == "{}") {
                	console.log('Object is empty');
                } else {
                	for (var k = 0; k < Object.keys(bulge_data[i]).length; k++){

                        var RSRZ = bulge_data[i][k].real_space_r_z_score;
                        var split_unitid = bulge_data[i][k].unit_id.split("|");

                        if (RSRZ === null){
                            command += "select " + split_unitid[4] + "/" + i + ".1;" + " color gray" + "; ";
                        } else {
                            var RSRZ = (parseFloat(bulge_data[i][k].real_space_r_z_score)*100)/100;
                            if (RSRZ < 1.00) {
                                command += "select " + split_unitid[4] + "/" + i + ".3;" + " color green; " + " spacefill off; ";   
                            } else if (RSRZ < 2.00) {
                                command += "select " + split_unitid[4] + "/" + i + ".3;" + " color yellow; " + " spacefill off; ";  
                            } else if (RSRZ < 3.00) {
                                command += "select " + split_unitid[4] + "/" + i + ".3;" + " color orange; " + " spacefill off; ";  
                            } else {
                                command += "select " + split_unitid[4] + "/" + i + ".3;" + " color red; " + " spacefill off; ";  
                            }
                        }
                    }
                }
                



                command += "select " + i + ".1, " + i + ".2;" +
                       "select nucleic and " + i + ".2; color grey;" +
                       "select protein and " + i + ".2; color purple;" +
                       "select hetero  and " + i + ".2; color pink;" +
                       " select " + i + ".2; color translucent 0.8;" + 
                       "select " + i + ".1," + i + ".2;" +
                       " spacefill off; " + "center " + i + ".1;" +
                       "zoom {"  + i + ".1} 0;"; 
            }


            console.log(command);
            jmolScript(command);
        },

        styleModelRSR: function (a,b) {

            var mod_num1 = a;

            var mod_num2 = b;

            console.log("RSR mod_num1: " + mod_num1);

            console.log("RSR mod_num2: " + mod_num2);
            
            command = "";

            for (var i = mod_num1; i <= mod_num2; i++) {

                if (RSR_data[i] === undefined) {
                    command += "select " + i + ".1; "  + "color grey; ";
                } else {
                
                    for (var k = 0; k < Object.keys(RSR_data[i]).length; k++) {
                    
                        var RSR = RSR_data[i][k].real_space_r;
                        var split_unitid = RSR_data[i][k].unit_id.split("|");

                        if (RSR === null){
                            command += "select " + split_unitid[4] + "/" + i + ".1;" + " color gray" + "; ";
                        } else {

                            var RSR = parseFloat(RSR_data[i][k].real_space_r);
                        
                            //Set the min and max values of RSR
                            if (RSR < 0.0) {
                                RSR = 0.0;
                            } else if (RSR > 0.5) {
                                RSR = 0.5;
                            }
                        
                            //map the RSR values between 0 and 100
                            var pert = Math.round(((RSR-0.0)/0.5)*100);
                        
                            for (var a=0; a<Object.keys(RSR_pair).length; a++) {
                                if (pert == (RSR_pair[a].interval)-1){
                                    colorchoice = RSR_pair[a].colorchoice;
                                    command += "select " + split_unitid[4] + "/" + i + ".1;" + " color '" + colorchoice + "'; ";
                                }
                            }

                        }

                    }

                }

                 command += "select nucleic and " + i + ".2; color grey;" +
                       "select protein and " + i + ".2; color purple;" +
                       "select hetero  and " + i + ".2; color pink;" +
                       " select " + i + ".2; color translucent 0.8;" + 
                       "select " + i + ".1," + i + ".2;" +
                       " spacefill off; " + "center " + i + ".1;" +
                       "zoom {"  + i + ".1} 0;";
                
            }

            console.log(command);
            jmolScript(command);

        },

        show: function() {
            var self = this;
            var m = self.modelNumber;

            if ( $.fn.jmolTools.options.mutuallyExclusive ) {
                self.hideAll();
            }

            if (self.neighborhood) {
                command = 'frame *;display displayed or ' + m + '.1,' + m + '.2; center ' + m + '.1;';
            } else {
                command = 'frame *;display displayed or '      + m + '.1,' + m + '.3;' +
                          'frame *;display displayed and not ' + m + '.2;' +
                          'center ' + m + '.1;';
            }
            jmolScript(command);
            self.hidden = false;
            self.toggleCheckbox();
        },

        hide: function () {
            var self = this;
            m = self.modelNumber;
            if ( self.loaded ) {
                command = 'frame *;display displayed and not ' + m + '.1;' +
                                  'display displayed and not ' + m + '.2;' +
                                  'display displayed and not ' + m + '.3;';
                jmolScript(command);
                self.hidden  = true;
                self.toggleCheckbox();
            }
        },

        hideAll: function() {
            jmolScript('hide *');
            $.each($.jmolTools.models, function() {
                this.hidden = true;
                this.toggleCheckbox();
            });
        },

        jmolToggle: function() {
            var self = $.jmolTools.models[this.id];
            //console.log(self);

            if ( ! self.loaded ) {
                self.loadData();
            } else {
                if ( self.hidden ) {
                    self.show();
                } else {
                    self.hide();
                }
            }
        },

        jmolShow: function() {
            var self = $.jmolTools.models[this.id];

            if ( ! self.loaded ) {
                self.loadData();
            } else if ( self.hidden ) {
                self.show();
            }
        },

        jmolHide: function() {
            var self = $.jmolTools.models[this.id];

            if ( ! self.loaded ) {
                self.loadData();
            } else if ( !self.hidden ) {
                self.hide();
            }
        },

        toggleCheckbox: function() {
            if ( !$.fn.jmolTools.options.toggleCheckbox ) { return; }
            this.$elem.prop('checked', !this.hidden);
        },

        toggleNeighborhood: function() {
            var self = this;
            self.neighborhood = !self.neighborhood;
            if ( !self.hidden && self.loaded ) {
                self.show();
            }
        },

    };

    var Helpers = {

        toggleStereo: function() {
            $.jmolTools.stereo
                ? jmolScript('stereo off;')
                : jmolScript('stereo on;')
            $.jmolTools.stereo = !$.jmolTools.stereo;
        },

        toggleNumbers: function() { 
            if ( $(this).is(':checked') ) {
                $.jmolTools.showNumbers = true;
                jmolScript("select {*.C1'},{*.CA};label %[sequence]%[resno];color labels black;");
            } else {
                 $.jmolTools.showNumbers = false;
                jmolScript('label off;');
            }
        },

        toggleRSRZ: function() {

            n = $.jmolTools.numModels;

            if ( $('#colorRSRZ').is(':checked') ) {
                $('#colorRSR').prop('checked', false);
                //$.jmolTools.showRSRZ = true;
                jmolModel.styleModelRSRZ(1, n);                
            } else {
                jmolModel.styleModel(1, n);
            }
        },

        toggleColor: function() {

            n = $.jmolTools.numModels;

            $('#colorOPT').change(function() { 
                if ($(this).val() === 'RSRZ') {
                    jmolModel.styleModelRSRZ(1, n);
                    $("div.showRSRZ").show(); 
                    $("div.showRSR").hide();
                } else if ($(this).val() === 'RSR') {
                    $("div.showRSR").show();
                    $("div.showRSRZ").hide();
                    jmolModel.styleModelRSR(1, n); 
                } else if ($(this).val() === 'CPK') {
                    jmolModel.styleModelCPK(1, n);
                    $("div.showRSR").hide(); 
                    $("div.showRSRZ").hide(); 
                } else if ($(this).val() === 'Default') {
                    jmolModel.styleModel(1, n);
                    $("div.showRSR").hide(); 
                    $("div.showRSRZ").hide(); 
                } 
            });

        },

        toggleRSR: function() { 

            n = $.jmolTools.numModels;
            
            if ( $('#colorRSR').is(':checked') ) {
                $('#colorRSRZ').prop('checked', false); 
                jmolModel.styleModelRSR(1, n);
            } else {
                jmolModel.styleModel(1, n);
            }
        },
        
        toggleNeighborhood: function() {
            // update button text
            if ($.jmolTools.neighborhood) {
                this.value = 'Show neighborhood';
            } else {
                this.value = 'Hide neighborhood';
            }
            $.jmolTools.neighborhood = !$.jmolTools.neighborhood;

            $.each($.jmolTools.models, function(ind, model) {
                model.toggleNeighborhood();
            });
        },

        showAll: function() {
            $.each($.jmolTools.models, function(ind, model) {
                if ( ! model.loaded ) {
                    model.loadData();
                } else {
                    model.show();
                }
                model.toggleCheckbox();
            });
        },

        hideAll: function() {
            $.jmolTools.models[$.fn.jmolTools.elems[0].id].hideAll();
        },

        showNext: function() {
            var elems = $($.jmolTools.selector), // can't use cache because the element order can change
                last = elems.length - 1,
                indToCheck = new Array();

            // figure out which ones should be checked
            for (var i = 0; i < elems.length-1; i++) {
                if ( elems[i].checked ) {
                    indToCheck.push(i+1); // the next one should be checked
                    $.jmolTools.models[elems[i].id].jmolToggle.apply(elems[i]); // toggle this model
                }
            }

            // analyze the last one
            if ( elems[last].checked ) {
                $.jmolTools.models[elems[last].id].jmolToggle.apply(elems[last]);
            }

            // uncheck all
            elems.filter(':checked').prop('checked', false);

            // check only the right ones
            $.each(indToCheck, function(ind, id) {
                elems[id].checked = true;
                $.jmolTools.models[elems[id].id].jmolToggle.apply(elems[id]);
            });

            // keep the first one checked if all are unchecked
            if ( elems.filter(':checked').length == 0 ) {
                elems[0].checked = true;
                $.jmolTools.models[elems[0].id].jmolToggle.apply(elems[0]);
            }
        },

        showPrev: function() {
            var elems = $($.jmolTools.selector), // can't use cache because the element order can change
                last = elems.length - 1,
                indToCheck = new Array();

            // loop over all checkboxes except for the first one
            for (var i = elems.length-1; i >= 1; i--) {
                if ( elems[i].checked ) {
                    indToCheck.push(i-1);
                    $.jmolTools.models[elems[i].id].jmolToggle.apply(elems[i]); // toggle this model
                }
            }
            // separate handling of the first checkbox
            if ( elems[0].checked ) {
                indToCheck.push(elems.length-1);
                $.jmolTools.models[elems[0].id].jmolToggle.apply(elems[0]);
            }

            // temporarily uncheck everything
            elems.filter(':checked').prop('checked', false);

            // check only the right ones
            $.each(indToCheck, function(ind, id) {
                elems[indToCheck[i]].checked = true;
                $.jmolTools.models[elems[id].id].jmolToggle.apply(elems[id]);
            });
            // keep the last checkbox checked if all others are unchecked
            if ( elems.filter(':checked').length == 0 ) {
                elems[last].checked = true;
                $.jmolTools.models[elems[last].id].jmolToggle.apply(elems[last]);
            }
        },

        reportLoadingBegan: function() {
            jmolScript('set echo top left; color echo green; echo Loading...;');
        },

        reportLoadingComplete: function() {
            jmolScript('set echo top left; color echo green; echo Done;');
        },

        reportClear: function() {
            jmolScript('set echo top left; echo ;');
        },

        bindEvents: function() {
            $('#' + $.fn.jmolTools.options.showStereoId).on('click', Helpers.toggleStereo);
            $('#' + $.fn.jmolTools.options.showNeighborhoodId).on('click', Helpers.toggleNeighborhood);
            $('#' + $.fn.jmolTools.options.showNumbersId).on('click', Helpers.toggleNumbers);
            $('#' + $.fn.jmolTools.options.colorOption).on('click', Helpers.toggleColor);
            $('#' + $.fn.jmolTools.options.showAllId)
                    .toggle(Helpers.showAll, Helpers.hideAll)
                    .toggle(
                function() {
                    $(this).val('Hide all');
                },
                function() {
                    $(this).val('Show all');
                }
            );
            $('#' + $.fn.jmolTools.options.showNextId).on('click', Helpers.showNext);
            $('#' + $.fn.jmolTools.options.showPrevId).on('click', Helpers.showPrev);
            $('#' + $.fn.jmolTools.options.clearId).on('click', Helpers.hideAll);

            $(document).ajaxSend(function() {
                Helpers.reportLoadingBegan();
            });

            $(document).ajaxStop(function() {
                Helpers.reportLoadingComplete();
                setTimeout(Helpers.reportClear, 1200);
            });
        },

        setMutuallyExclusiveProperty: function() {
            if ( $.fn.jmolTools.options.mutuallyExclusive ||
                 $.fn.jmolTools.elems.is('input[type="radio"]') ) {
                $.fn.jmolTools.options.mutuallyExclusive = true;
            }
        }

    }

    // plugin initialization
    $.fn.jmolTools = function ( options ) {

        $.jmolTools.selector = $(this).selector;

        $.fn.jmolTools.options = $.extend( {}, $.fn.jmolTools.options, options );

        // bind events
        Helpers.bindEvents();

        // initialize model state for each element
        $.fn.jmolTools.elems = this.each( function() {
            // create a new object to keep track of state
            var jmdb = Object.create( jmolModel );
            jmdb.init( options, this );
            // store the object
            $.jmolTools.models[this.id] = jmdb;
        });

        // add convenience methods to toggle structures
        $.fn.jmolToggle = function ( options ) {
            return this.each( function() {
                $.jmolTools.models[this.id].jmolToggle.apply(this);
            });
        }
        $.fn.jmolShow = function ( options ) {
            return this.each( function() {
                $.jmolTools.models[this.id].jmolShow.apply(this);
            });
        }
        $.fn.jmolHide = function ( options ) {
            return this.each( function() {
                $.jmolTools.models[this.id].jmolHide.apply(this);
            });
        }

        //
        Helpers.setMutuallyExclusiveProperty();

        // return elements for chaining
        return $.fn.jmolTools.elems;
    }

    //
    var loc = window.location.protocol + '//' + window.location.hostname;
    // default options
    $.fn.jmolTools.options = {
        serverUrlCoord   : loc + '/rna3dhub/rest/getCoordinates',
        dataAttributeCoord: 'coord',

        serverUrlRSR   : loc + '/rna3dhub/rest/getRSR',
        dataAttributeRSR: 'quality',

        serverUrlRSRZ   : loc + '/rna3dhub/rest/getRSRZ',
        dataAttributeRSRZ: 'quality',

        serverUrlBulgeUnit   : loc + '/rna3dhub/rest/getBulge',
        dataAttributeBulgeUnit: 'quality',

        toggleCheckbox: true,      // by default each model will monitor the checked state of its corresponding checkbox
        mutuallyExclusive:  false, // by default will set to false for checkboxes and false for radiobuttons
        showNeighborhoodId: false,
        showNextId:         false,
        showPrevId:         false,
        showAllId:          false,
        showNumbersId:      false,
        colorByRSRZ:        false,
        showStereoId:       false,
        clearId:            false
    };

})(jQuery);