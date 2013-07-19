$(document).ready(function() {
  'use strict';
/*globals Rna2D, d3, document, $, NTS, INTERACTION_URL, LONG, LOOP_URL */

  var convertNTID = function(id) { return id.replace(/\|/g, '_'); },
      ntURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/unitid/describe/' + encodeURIComponent(id); },
      ntLink = function(id) { return '<a target="_blank" href="' + ntURL(id) + '">' + id + "</a>"; },
      loopURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/loops/view/' + id; },
      loopLink = function(id) { return '<a target="_blank" href="' + loopURL(id) + '">' + id + "</a>"; },
      plot = Rna2D({view: 'circular', width: 500, height: 687.5, selection: '#rna-2d'});

  if (NTS[0].hasOwnProperty('x')) {
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
    return plot.interactions.jmol(data, i);
  };

  var clickNucleotide = function(data, i) {
    showAbout('Nucleotide: ' + ntLink(data.id));
    return plot.nucleotides.jmol(data, i);
  };

  var clickMotif = function(data, i) {
    showAbout('Loop: ' + loopLink(data.id));
    return plot.motifs.jmol(data, i);
  };

  var brushShow = function(nts) {
    $('#about-selection').hide();
    return plot.brush.jmol(nts);
  };

  plot.frame.add(false);

  plot.views.airport.fontSize(8);

  plot.nucleotides(NTS)
    .encodeID(convertNTID)
    .click(clickNucleotide)
    .mouseover('highlight');

  plot.brush.enabled(false)
    .update(brushShow);

  plot.jmol.overflow(function() { $("#overflow").show(); })
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

  plot.views.circular
    .center(function() { return { x: plot.width() / 2, y: plot.height() / 4 }; })
    .addLetters(function(nts) {
        var positionOf = plot.views.circular.letterPosition(),
            highlightColor = plot.nucleotides.highlightColor();

        plot.vis.selectAll(plot.views.circular.letterClass())
          .data(nts).enter().append('svg:text')
          .attr('id', plot.views.circular.letterID())
          .attr('class', plot.views.circular.letterClass())
          .attr('x', function(d) { return positionOf(d).x; })
          .attr('y', function(d) { return positionOf(d).y; })
          .attr('font-size', plot.views.circular.letterSize())
          .attr('pointer-events', 'none')
          .text(function(d) { 
            return d.getAttribute('data-sequence') + d.getAttribute('id').split('_')[4];
          })
          .attr('fill', function(d) { return highlightColor(d); });

        return plot;
    });


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
      brush: {
        selector: '#mode-toggle',
        callback: function(e) {
          var $btn = $(e.target),
              newText = ($btn.text() === $btn.data('loading-text') ? 'normal-text' : 'loading-text');
          $btn.text($btn.data(newText));
        }
      },
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
