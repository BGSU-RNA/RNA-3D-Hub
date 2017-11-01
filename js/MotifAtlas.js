function LookUpPDBInfo()
{
    a = $(this);
    var anchor_text = a.text(),
        re = /[a-zA-Z0-9]{4}/,
        pdb = re.exec(anchor_text),
        loc = window.location.protocol + '//' + window.location.host +
            '/rna3dhub/rest/getPdbInfo';

    $.post(loc, { pdb: pdb[0] }, function(data) {
        // hide all previously displayed popovers
        $('.popover-displayed').removeClass('popover-displayed')
                               .popover('hide')
                               .unbind()
                               .bind('click', LookUpPDBInfo);
        a.data('content',data);
        a.data('original-title',pdb);
        a.popover({
          offset: 10,
          content: function(){return data;},
          title: function(){return pdb;},
          //delayOut: 1200,
          html: true,
          animate: true,
          placement: a.offset().top - $(window).scrollTop() < 500 ? 'below' : 'above'
        });
        a.addClass('popover-displayed');
        a.popover('show');
    });

}

function get_rna3dhub_environment()
{
    return window.location.href.split('/')[3]; // rna3dhub or rna3dhub_dev
}
