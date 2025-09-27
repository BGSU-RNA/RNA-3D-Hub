/*
 *
 *
 *
 */


;var jmolInlineLoader = function(){

	var models       = new Array();
	var neighborhood = false;
	var radio        = false;
	var maxModel     = 1;
	var settings     = {};
	var self = this;
    var stereo = false;

	// defaults are updated in the "init" function
    var defaults = {
		serverUrl   : 'http://rna.bgsu.edu/research/anton/MotifAtlas/nt_coord.php',
		chbxClass   : '',
		sfdata      : false,
		neighborhoodButtonId: '',
		showNextButtonId: '',
		showPreviousButtonId: '',
		showAllButtonId: '',
		showNucleotideNumbersId: '',
		showStereoId: ''
    };

	// "Private" class. Stores the state of each checkbox.
	this.model = function(id, modelNumber, neighborhood) {

		this.id           = id;
		this.neighborhood = neighborhood;
		this.modelNumber  = modelNumber;
        this.superimposed = false;
        this.styled       = false;
		this.checked      = false;
		this.hidden       = false;
		this.jQ           = $('#'+id);
		var self          = this;

		// load the very first structure
		this.load_and_display = function () {
			// get the inline data-nt info
			var nts = self.jQ.data('nt');
			var data_type = self.jQ.data('type');
			if (data_type==undefined) {
			    data_type = 'model';
			}
			var params = {};
            params[data_type] = nts;
			$.post(settings.serverUrl, params,
				function(data) {
					jmolScriptWait("load DATA \"append structure\"\n" + data + 'end "append structure";');
					self.display();
					// load structure factor data
                    if (settings.sfdata == true) {
                        $.post('http://rna.bgsu.edu/MotifAtlas_dev/ajax/get_dcc_data', { model: nts }, function(data) {
                            var dcc = jQuery.parseJSON(data);
                            jmolScript('set propertyAtomNumberField 0;set propertyAtomColumnCount 1;');
                            $.each(dcc, function (k,v) {
                                jmolScript('select '+self.modelNumber+'.1;'+k + " = '" + v + "'" + ';DATA "property_' + k + ' @' + k + '";');
                            });
                        });
                    }
			});
		}

		// superimpose this model onto the first one using phosphate atoms
		this.superimpose = function () {
		    if (self.superimposed == false) {
                var m = self.modelNumber;
                if ( m > 1 ) {
                    for (var i = 0; i < 3; i++) {

                        // if the same number of phosphates, try to superimpose,
                        // otherwise take the first four phosphates
                        jmolScript('if ({*.P/'+m+'.1}.length == {*.P/1.1}) {x=compare({*.P/' + m + '.1},{*.P/1.1});} else {x=compare({(*.P/' + m + '.1)[1][4]},{(*.P/1.1)[1][4]});}');
                        // jmolScript('x=compare({(*.P/' + m + '.1)[1][4]},{(*.P/1.1)[1][4]});');
                        jmolScript('select ' + m + '.0; rotate selected @{x};');
                    }
                }
                self.superimposed = true;
            }
		}

		// apply styling to the model
		this.style = function () {
		    if (self.styled == false) {
                var m = self.modelNumber;
                jmolScript('select [U]/' + m + '.1; color navy;');
                jmolScript('select [G]/' + m + '.1; color chartreuse;');
                jmolScript('select [C]/' + m + '.1; color gold;');
                jmolScript('select [A]/' + m + '.1; color red;');
                jmolScript('select */' + m + '.2; color grey;');
                jmolScript('select ' + m + '.2; color translucent 0.8;');
                jmolScript('select protein; color purple; select hetero;color pink;color translucent 0.8;');
                jmolScript('select ' + m + '.0;spacefill off;center ' + m + '.1;');
                jmolScript('zoom {' + m + '.1} 0;');
                self.styled = true;
            }
		}

		this.set_neighborhood = function (neighborhood) {
			self.neighborhood = neighborhood;
		}

		this.toggle_checked = function () {
			self.checked ? self.checked = false : self.checked = true;
		}

		this.toggle_checkbox = function () {
			if (self.checked) {
				self.jQ.attr('checked','checked'); // .prop("checked", true);
			} else {
				self.jQ.removeAttr('checked');	// .prop("checked", false);
			}
		}

		this.toggle_view = function () {

			if (self.checked) {
				// show everything
				if (self.neighborhood) {
					jmolScript('frame *;display displayed or ' + self.modelNumber + '.0;');
				} else {
					jmolScript('frame *;display displayed or ' + self.modelNumber + '.1;');
					jmolScript('frame *;display displayed and not ' + self.modelNumber + '.2;');
				}
				jmolScript('center '+self.modelNumber+'.1;');
			} else {
				// hide everything
				this.hide();
// 				jmolScript('frame *;display displayed and not ' + self.modelNumber + '.0;');
			}

		}

		this.hide = function () {
// 		    if (self.hidden == false | self.checked == false) {
                jmolScript('frame *;display displayed and not ' + self.modelNumber + '.0;');
                this.hidden  = true;
                this.checked = false;
// 		    }
		}

		this.display = function() {
			self.toggle_checked();
			self.toggle_view();
		// 	self.toggle_checkbox();
		}

	} // end of the model private class


	// Public methods
	this.checkbox_click = function () {

		if ( typeof(arguments[0]) == 'string' ) {
			var id = arguments[0];
		} else {
			var id = $(this).attr('id');
		}

		if ( typeof(models[id]) == 'undefined' ) {
			models[id] = new model(id, maxModel, neighborhood);
			maxModel++;
			models[id].load_and_display();
		} else {
			models[id].set_neighborhood(neighborhood);
			models[id].display();
		}

        // special case for radiobuttons
        if (radio) {
            var clicked_id = $(settings.chbxClass + ':checked').attr('id');
            for (id in models) {
                if (id != clicked_id) {
                    models[id].hide();
                }
            }
        }


	}

	this.superimpose_all = function () {

		for (id in models) {
			models[id].superimpose();
			models[id].style();
			models[id].toggle_view();
		}

		toggle_nt_numbers();
	}

    this.toggle_stereo = function() {
        if (stereo) {
            stereo = false;
            this.value = 'Show stereo';
            jmolScript('stereo off;');
        } else {
            stereo = true;
            this.value = 'Hide stereo';
            jmolScript('stereo on;');
        }
    }

	this.toggle_neighborhood = function() {

		if (neighborhood) {
			neighborhood = false;
			this.value = 'Show neighborhood';
		} else {
			neighborhood = true;
			this.value = 'Hide neighborhood';
		}

		for (id in models) {
			models[id].set_neighborhood(neighborhood);
			models[id].toggle_view();
		}

	}

	this.init = function(options) {

		if ( options ) {
			settings = $.extend( {}, defaults, options );
		}
   		jmolScript('set baseCartoonEdges = true;');

		$(document).ajaxStop(function() {
			superimpose_all();
		});

   		settings.chbxClass = '.' + settings.chbxClass;

        // different behavior for checkboxes and radiobuttons
   		if ($(settings.chbxClass).attr('type') == 'radio') {
   		    radio = true;
   		}

   		$(settings.chbxClass).click(jmolInlineLoader.checkbox_click);
   		checkbox_click( $(settings.chbxClass).first().attr('id') );
   		$(settings.chbxClass).first().prop("checked", true);

   		// attach event handlers if ids have been provided
   		if ( settings.neighborhoodButtonId != '' ) {
   			settings.neighborhoodButtonId = '#' + settings.neighborhoodButtonId;
	   		$(settings.neighborhoodButtonId).click(jmolInlineLoader.toggle_neighborhood);
   		}
   		if ( settings.showNextButtonId != '' ) {
   			settings.showNextButtonId = '#' + settings.showNextButtonId;
	   		$(settings.showNextButtonId).click(jmolInlineLoader.show_next);
   		}
   		if ( settings.showPreviousButtonId != '' ) {
   			settings.showPreviousButtonId = '#' + settings.showPreviousButtonId;
	   		$(settings.showPreviousButtonId).click(jmolInlineLoader.show_prev);
   		}
   		if ( settings.showAllButtonId != '' ) {
   			settings.showAllButtonId = '#' + settings.showAllButtonId;
	   		$(settings.showAllButtonId).click(jmolInlineLoader.toggle_all);
   		}
   		if ( settings.showNucleotideNumbersId != '' ) {
   			settings.showNucleotideNumbersId = '#' + settings.showNucleotideNumbersId;
	   		$(settings.showNucleotideNumbersId).click(jmolInlineLoader.toggle_nt_numbers);
   		}
   		if ( settings.showStereoId != '' ) {
   		    $('#' + settings.showStereoId).click(jmolInlineLoader.toggle_stereo);
   		}

	}

	this.show_next = function() {

		var chbx = $(settings.chbxClass);
		var toCheck = new Array();
		for (var i=0;i<chbx.length-1;i++) {
			if ( chbx[i].checked ) {
				toCheck.push(i+1);
				checkbox_click(chbx[i].id);
			}
		}
		if ( chbx[chbx.length-1].checked ) {
			toCheck.push(0);
			checkbox_click(chbx[chbx.length-1].id);
		}
		$(settings.chbxClass + ':checked').removeAttr('checked');

		for (i=0;i<toCheck.length;i++) {
			chbx[toCheck[i]].checked = true;
			checkbox_click(chbx[toCheck[i]].id);
		}
		if ( $(settings.chbxClass + ':checked').length == 0 ) {
			chbx[0].checked = true;
			checkbox_click(chbx[0].id);
		}

	}

	this.show_prev = function () {

		var chbx = $(settings.chbxClass);
		var toCheck = new Array();
		// loop over all checkboxes except for the first one
		for (var i=chbx.length-1;i>=1;i--) {
			if ( chbx[i].checked ) {
				toCheck.push(i-1);
				checkbox_click(chbx[i].id);
			}
		}
		// separate handling of the first checkbox
		if ( chbx[0].checked ) {
			toCheck.push(chbx.length-1);
			checkbox_click(chbx[0].id);
		}
		// temporarily uncheck everything
		$(settings.chbxClass+':checked').removeAttr('checked');
		// check only the right ones
		for (i=0;i<toCheck.length;i++) {
			chbx[toCheck[i]].checked = true;
			checkbox_click(chbx[toCheck[i]].id);
		}
		// keep the last checkbox checked if all others are unchecked
		if ( $(settings.chbxClass+':checked').length == 0 ) {
			chbx[chbx.length-1].checked = true;
			checkbox_click(chbx[chbx.length-1].id);
		}
	}

	this.toggle_all = function () {

		var all = $(settings.chbxClass);
		if ( all.length == $(settings.chbxClass + ':checked').length ) {
			// hide all
			this.value = 'Show all';
			all.each( function(i) {
				this.checked = false;
				models[this.id].hide();
// 				checkbox_click(this.id);
			});
		} else {
			// show all
			this.value = 'Hide all';
			$(settings.chbxClass+':not(:checked)').each( function(i) {
				this.checked = true;
				checkbox_click(this.id);
			});
		}
	}

	this.toggle_nt_numbers = function () {

        if ( $(settings.showNucleotideNumbersId).is(':checked') ) {
    	    jmolScript('select {*.P},{*.CA};label %[sequence]%[resno];color labels black;');
        } else {
            jmolScript('label off;');
    	}

	}

	return {
		init: init,
		toggle_all: toggle_all,
		toggle_neighborhood: toggle_neighborhood,
		toggle_stereo: toggle_stereo,
		checkbox_click: checkbox_click,
		show_prev: show_prev,
		show_next: show_next,
		toggle_nt_numbers: toggle_nt_numbers,
		models: models
	}

}();
