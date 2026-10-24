var env = window.location.href.split('/')[3]; // rna3dhub or rna3dhub_dev
var api_url = "http://rna.bgsu.edu/" + env + "/apiv1/validate_nts";

// TO DO: test cases when chain is omitted

module("Nucleotide validation");
asyncTest( "nt_single_valid", function() {
    query = {
        pdb: '1J5E',
        nts: '1000',
        ch:  'A'
    };

    $.post(api_url, query, function(data){
        equal(data.valid, true);
        start();
    });
});
asyncTest( "nt_single_invalid", function() {
    query = {
        pdb: '1J5E',
        nts: '-40',
        ch:  'A'
    };

    $.post(api_url, query, function(data){
        if ( data.error_msg != '' ) {
            console.log(data.error_msg);
        }
        equal(data.valid, false);
        start();
    });
});
asyncTest( "nt_multiple_range_valid", function() {
    query = {
        pdb: '2AW4',
        nts: '2:20, 62:69,109:118',
        ch:  'A'
    };

    $.post(api_url, query, function(data){
        equal(data.valid, true);
        start();
    });
});
asyncTest( "nt_multiple_range_invalid", function() {
    query = {
        pdb: '2AW4',
        nts: '2:20, 62:690000,109:118',
        ch:  'A'
    };

    $.post(api_url, query, function(data){
        if ( data.error_msg != '' ) {
            console.log(data.error_msg);
        }
        equal(data.valid, false, data.error_msg);
        start();
    });
});
asyncTest( "nt_mixed_valid", function() {
    query = {
        pdb: '2AW4',
        nts: '2, 62:5, 68, 69, 109:117, 118',
        ch:  'A'
    };

    $.post(api_url, query, function(data){
        equal(data.valid, true);
        start();
    });
});
asyncTest( "nt_mixed_valid", function() {
    query = {
        pdb: '2AW4',
        nts: '2, 62:5, -68, 69, 109:-117, 118',
        ch:  'A'
    };

    $.post(api_url, query, function(data){
        if ( data.error_msg != '' ) {
            console.log(data.error_msg);
        }
        equal(data.valid, false);
        start();
    });
});
