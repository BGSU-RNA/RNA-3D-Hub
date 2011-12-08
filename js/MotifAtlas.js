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

// Cytoscapeweb functions
function show_motif_exemplar_in_jmol(id) {
    $.post('http://rna.bgsu.edu/MotifAtlas_dev/ajax/get_exemplar_coordinates', { motif_id: id }, function(data) {
        jmolScript('zap;');
        jmolLoadInlineScript(data);
        jmolScript('select [U];color navy;');
        jmolScript('select [G]; color chartreuse;');
        jmolScript('select [C]; color gold;');
        jmolScript('select [A]; color red;');
        jmolScript('select 1.2; color grey; color translucent 0.8;');
        jmolScript('select protein; color purple; color translucent 0.8;');
        jmolScript('select 1.0;spacefill off;center 1.1;');
        jmolScript('frame *;display displayed and not 1.2;');
        jmolScript('zoom 150');
    });
}
// end of Cytoscapeweb functions