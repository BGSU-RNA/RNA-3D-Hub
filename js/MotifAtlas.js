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