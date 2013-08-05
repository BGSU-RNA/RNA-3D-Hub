$(document).ready(function() {
  'use strict';
/*globals Rna2D, d3, document, $, NTS, INTERACTION_URL, LONG, LOOP_URL, location */

  function getURLParameter(name) {
      return decodeURI(
          (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[null,null])[1]
      );
  }

  var convertNTID = function(id) { return id.replace(/\|/g, '_'); },
      ntURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/unitid/describe/' + encodeURIComponent(id); },
      ntLink = function(id) { return '<a target="_blank" href="' + ntURL(id) + '">' + id + "</a>"; },
      loopURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/loops/view/' + id; },
      loopLink = function(id) { return '<a target="_blank" href="' + loopURL(id) + '">' + id + "</a>"; },
      plot = Rna2D({view: 'circular', width: 500, height: 687.5, selection: '#rna-2d'});

  if (NTS[0].nts[0].hasOwnProperty('x') && getURLParameter('view') !== 'circular') {
    plot.view('airport');
    $(".motif-toggle").removeAttr("disabled").addClass('active');
    $("#airport-view").click();
  }

  $('#about-selection').hide();

  var showAbout = function(text) {
    $("#about-selection")
      .empty()
      .append('<p>' + text + '</p>')
      .addClass('info')
      .show();
  };

  var clickInteraction = function(data, i) {
    showAbout(data.family + ' interaction between ' + ntLink(data.nt1) +
              ' and ' + ntLink(data.nt2));
    return plot.jmolTools.interactions()(data, i);
  };

  var clickNucleotide = function(data, i) {
    showAbout('Nucleotide: ' + ntLink(data.id));
    return plot.jmolTools.nucleotides()(data, i);
  };

  var clickMotif = function(data, i) {
    showAbout('Loop: ' + loopLink(data.id));
    return plot.jmolTools.motifs()(data, i);
  };

  var brushShow = function(nts) {
    $('#about-selection').hide();
    return plot.jmolTools.brush()(nts);
  };

  plot.frame.render(false);

  plot.airport.fontSize(8);

  plot.chains(NTS);

  plot.nucleotides
    .encodeID(convertNTID)
    .click(clickNucleotide)
    .mouseover('highlight');

  plot.brush.update(brushShow);

  plot.jmolTools
    .overflow(function() { $("#overflow").show(); })
    .stereoID('stereo')
    .windowSize(350);

  // TODO: Use interaction.highlightColor to select the color based upon css for
  // the interaction

  plot.interactions
    .classOf(function(d, i) {
      var klass = [plot.interactions.getFamily()(d)];
      return (d.long_range ? klass.concat("LR") : klass);
    })
    .click(clickInteraction)
    .mouseover('highlight');

  plot.motifs
    .click(clickMotif)
    .mouseover('highlight');

  plot.circular
    .center(function() { return { x: plot.width() / 2, y: plot.height() / 3 }; })
    .radius(function() { return plot.width() / 2 - 50; });

  var interactionParser = function(text) {
    if (text.indexOf("This structure") !== -1) {
      console.log(text);
      if (plot.nucleotides().length) {
        $("#error-message").append("<p>No interactions found.<p>");
        $("#error-message").show();
        $("#cWW-toggle").removeClass("active");
        $(".toggle-control").addClass('disabled');
        return [];
      }
    }

    var interactions = d3.csv.parse('"nt1","family","nt2"\n' + text),
        idBuilder = plot.interactions.getID(),
        lr = {};

    $.each(LONG, function(i, data) { lr[idBuilder(data)] = data.crossing; });
    return $.map(interactions, function(value, key) {
      var id = idBuilder(value);
      if (lr[id]) {
        value.long_range = true;
        value.crossing = lr[id];
      }
      return value;
    });
  };

  var loopParser = function(text) {
    if (text.indexOf("No loops") !== -1) {
      console.error(text);
      return [];
    }

    var motifs = d3.csv.parse('"id","nts"\n' + text);
    return $.map(motifs, function(data, i) {
      data.nts = data.nts.split(',');
      return data;
    });
  };

  $("#rna-2d").rna2d({
    plot: plot,
    interactions: {
      url: INTERACTION_URL,
      parser: interactionParser
    },
    motifs: {
      url: LOOP_URL,
      parser: loopParser
    }, controls: {
      interactions: {
        selector: '.toggle-control',
        near: true,
        data: 'family'
      },
      motifs: {
        selector: '.motif-toggle',
        data: 'motif',
      },
      views: {
        selector: '.view-control',
        data: 'view',
        post: function(e) {
          var $btn = $(e.target),
              view = $btn.data('view');

          plot.brush.clear();
          $('#about-selection').hide();

          if (view === 'airport') {
            $(".motif-toggle").removeAttr("disabled").addClass('active');
          } else {
            $(".motif-toggle").attr("disabled", "disabled");
          }

        }
      }

    }
  });

  // When using the all toggle we should toggle the other buttons.
  $("#all-toggle").on('click', function(event) {
    var $btn = $(this),
        active = $btn.hasClass('active');
    if (active) {
      $btn.siblings().removeClass('active');
    } else {
      $btn.siblings().addClass('active');
    }
  });

});
