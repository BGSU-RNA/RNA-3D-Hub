BTN = null; 
$(document).ready(function() {
  'use strict';
/*globals Rna2D, d3, document, $, NTS, INTERACTION_URL, LONG, LOOP_URL */

  var convertNTID = function(id) { return id.replace(/\|/g, '_'); },
      normalizeID = function(id) { return id.replace(/_/g, '|'); },
      ntURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/unitid/describe/' + encodeURIComponent(id); },
      ntLink = function(id) { return '<a target="_blank" href="' + ntURL(id) + '">' + id + "</a>"; },
      loopURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/loops/view/' + id; },
      loopLink = function(id) { return '<a target="_blank" href="' + loopURL(id) + '">' + id + "</a>"; },
      plot = Rna2D({view: 'circular', width: 500, height: 687.5, selection: '#rna-2d'});

  $('#about-selection').hide();

  var generateJmol = function($jmol) {
    var form = '<button type="button" id="neighborhood" class="btn">Show neighborhood</button>' +
      ' ' +
      '<button type="button" id="stereo" class="btn">Stereo</button>' +
      ' ' +
      '<label><input type="checkbox" id="showNtNums">Show numbers</label>';
    $jmol.append(form);
  };

  var showAbout = function(text) {
    $("#about-selection")
      .empty()
      .append('<p>' + text + '</p>')
      .addClass('info')
      .show();
  };

  var clickInteraction = function() {
    $('#about-selection').hide();
    var data = d3.select(this).datum(),
        selection = {};

    $('#' + plot.jmol.divID()).show();
    showAbout(data.family + ' interaction between ' + ntLink(data.nt1) +
              ' and ' + ntLink(data.nt2));

    selection[data.nt1] = true;
    selection[data.nt2] = true;
    return plot.jmol.showSelection(selection);
  };

  var clickNucleotide = function() {
    var data = d3.select(this).datum(),
        selection = {};

    $('#' + plot.jmol.divID()).show();
    showAbout('Nucleotide: ' + ntLink(data.id));

    selection[data.id] = true;
    return plot.jmol.showSelection(selection);
  };

  var clickMotif = function() {
    var data = d3.select(this).datum(),
      selection = {},
      i = 0;

    $('#' + plot.jmol.divID()).show();
    showAbout('Loop: ' + loopLink(data.id));

    $.each(data.nts, function(i, nt) { selection[normalizeID(nt)] = true; });

    return plot.jmol.showSelection(selection);
  };

  var brushShow = function(selection) {
    $('#' + plot.jmol.divID()).show();
    $('#about-selection').hide();
    return plot.jmol.showSelection(selection);
  };

  plot.frame.add(false);

  plot.views.airport.fontSize(8);

  plot.nucleotides(NTS)
    .getID(function(d, i) { return convertNTID(d.id); })
    .click(clickNucleotide)
    .mouseover('highlight');

  plot.interactions
    .getNTs(function(d) { return [convertNTID(d.nt1), convertNTID(d.nt2)]; });

  plot.brush.enabled(false)
    .update(brushShow);

  plot.jmol.overflow(function() { $("#overflow").show(); })
    .stereoID('stereo')
    .windowSize(350)
    .windowBuild(generateJmol);

  // TODO: Use interaction.highlightColor to select the color based upon css for
  // the interaction

  plot.interactions
    .getNTs(function(d) { return [convertNTID(d.nt1), convertNTID(d.nt2)]; })
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
    .center(function() { return { x: plot.width() / 2, y: plot.height() / 4 }; });


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
      data.nts = $.map(data.nts.split(','), convertNTID);
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
          $('#' + plot.jmol.divID()).hide();
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

});
