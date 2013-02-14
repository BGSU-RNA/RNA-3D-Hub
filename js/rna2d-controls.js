$(document).ready(function() {

  var generateJmol = function($jmol) {
    var form = '<form class="form-inline">' +
      '<button id="neighborhood" type="button" class="btn">Show neighborhood</button>' +
      '<button id="stereo" type="button" class="btn">Stereo</button>' +
      '<label class="checkbox"><input type="checkbox" id="showNtNums"> Numbers</label>' +
      '</form>';
    $jmol.append(form);
  };

  var highlightInteraction = function() {
    var nts = plot.interactions.nucleotides(this),
        family = plot.interactions.family(this),
        stroke = $('.pdb-2d-view #rna-2d .' + family).css('stroke');

    d3.select(this).style('opacity', 1);

    if (plot.view() === 'circular') {
      plot.pie.addLetters()(nts[0]);
    } else {
      nts.style('font-size', plot.nucleotides.fontSize() + 4);
      nts.classed(family, true);
    }
  };

  var normalizeInteraction = function() {
    var nts = plot.interactions.nucleotides(this);
    var family = plot.interactions.family(this);
    nts.classed(family, false);
    nts.style('font-size', plot.nucleotides.fontSize());
    d3.select(this).style('opacity', 0.4);
    plot.pie.clearLetters()();
  };

  var highlightNucleotide = function() {
    plot.pie.addLetters()([this]);
    d3.select(this).style('font-size', plot.nucleotides.fontSize() + 4);
    var inters = plot.nucleotides.interactions(this);
    inters.style('opacity', 1);
  };

  var normalizeNucleotide = function() {
    d3.select(this).style('font-size', plot.nucleotides.fontSize());
    var inters = plot.nucleotides.interactions(this);
    inters.style('opacity', 0.4);
    plot.pie.clearLetters()();
  };

  var clickInteraction = function() {
    $('#about-selection').hide();
    return plot.jmol.showGroup({ 'data-nts': normalizeID(this.getAttribute('nt1')) + ',' + normalizeID(this.getAttribute('nt2')) });
  };

  var clickNucleotide = function() {
    $('#about-selection').hide();
    return plot.jmol.showGroup({ 'data-nts': normalizeID(this.id) });
  };

  var brushShow = function(selection) {
    return plot.jmol.showGroup({'data-nts': $.map(selection, normalizeID) });
  };

  $('.toggle-control').on('click', function(e) {
    var $btn = $(e.target),
      family = $btn.data('family');
    $btn.button('toggle');
    plot.interactions.toggle(family);
    plot.interactions.toggle('n' + family);
  });

  $('#mode-toggle').on('click', function(e) {
    var $btn = $(e.target);
    plot.brush.toggle();

    $btn.button('toggle');
    var text = $btn.data('normal-text');
    if ($btn.hasClass('active')) {
      text = $btn.data('loading-text');
    }
    $btn.text(text);
  });

  var convertNTID = function(id) { return id.replace(/\|/g, '_'); };
  var normalizeID = function(id) { return id.replace(/_/g, '|'); };

  var plot = Rna2D({view: 'circular', width: 550, height: 400, selection: '#rna-2d' });

  plot.frame.add(false);
  plot.nucleotides(NTS)
    .getID(function(d, i) { return convertNTID(d['id']); })
    .mouseover(highlightNucleotide)
    .mouseout(normalizeNucleotide);

  plot.brush.enabled(true)
    .update(brushShow);

  plot.jmol.overflow(function() { $("#overflow").show(); })
    .windowSize(350)
    .windowBuild(generateJmol);

  d3.text(INTERACTION_URL, 'text/csv', function(err, text) {
    var interactions = [];
    if (err || text.indexOf("This structure") !== -1) {
      console.log(err);
      console.log(text);
      if (plot.nucleotides().length) {
        $("#error-message").append("<p>No interactions found.<p>");
        $("#error-message").show();
        $("#cWW-toggle").removeClass("active");
        $(".toggle-control").addClass('disabled');
      }
    } else {
      interactions = d3.csv.parse('"nt1","family","nt2"\n' + text);
    }

    plot.interactions(interactions)
      .getNTs(function(d) { return [convertNTID(d.nt1), convertNTID(d.nt2)]; })
      .mouseover(highlightInteraction)
      .mouseout(normalizeInteraction);

    return plot();
  });

  return true;
});
