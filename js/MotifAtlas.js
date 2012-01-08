function LookUpPDBInfo()
{
    a = $(this);
    var anchor_text = a.text();

	re = /[a-zA-Z0-9]{4}/;
	pdb = re.exec(anchor_text);

    $.post('http://rna.bgsu.edu/webfr3d/lookuppdbinfo.php', { pdb: pdb[0] }, function(data) {
        a.data('content',data);
        a.data('original-title',pdb);
        a.popover({
          offset: 10,
          content: function(){return data;},
          title: function(){return pdb;},
          delayOut: 1200,
          html: true,
          animate: true,
          placement:'above'
        });
        a.popover('show');
    });

}

// *****************************************************************************
// Common Jmol functions
// *****************************************************************************
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
// *****************************************************************************
// End of common Jmol functions
// *****************************************************************************
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

// Cytoscapeweb functions
function show_motif_exemplar_in_jmol(id) {
    $.post('http://rna.bgsu.edu/MotifAtlas_dev/ajax/get_exemplar_coordinates', { motif_id: id }, function(data) {
        jmolScript('zap;');
        jmolLoadInlineScript(data);
        apply_jmol_styling();
    });
}
// end of Cytoscapeweb functions

// Cytoscapeweb functions
function show_loop_in_jmol(id) {
    $.post('http://rna.bgsu.edu/MotifAtlas_dev/ajax/get_loop_coordinates', { loop_id: id }, function(data) {
        jmolScript('zap;');
        jmolLoadInlineScript(data);
        apply_jmol_styling();    });
}
// end of Cytoscapeweb functions