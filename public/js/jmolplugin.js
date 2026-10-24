
    jmolApplet0 = Jmol.getApplet("jmolApplet0", Info);

    $(document).ready(function(){
    // initialize the plugin
    $('.jmolInline').jmolTools({
        showStereoId: 'stereo2',
        showNeighborhoodId: 'neighborhood',
        showNumbersId: 'showNtNums',
        colorOption: 'colorOPT',
        showNextId: 'next',
        showPrevId: 'prev',
        showAllId: 'all'
    });

});

