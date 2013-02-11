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
    var nts = plot.interactions.nucleotides(this);
    var family = plot.interactions.family(this);
    nts.classed(family, true);
    nts.style('font-size', plot.nucleotides.fontSize() + 4);
    d3.select(this).style('opacity', 1);
  };

  var normalizeInteraction = function() {
    var nts = plot.interactions.nucleotides(this);
    var family = plot.interactions.family(this);
    nts.classed(family, false);
    nts.style('font-size', plot.nucleotides.fontSize());
    d3.select(this).style('opacity', 0.4);
  };

  var highlightNucleotide = function() {
    d3.select(this).style('font-size', plot.nucleotides.fontSize() + 4);
    var inters = plot.nucleotides.interactions(this);
    inters.style('opacity', 1);
  };

  var normalizeNucleotide = function() {
    d3.select(this).style('font-size', plot.nucleotides.fontSize());
    var inters = plot.nucleotides.interactions(this);
    inters.style('opacity', 0.4);
  };

  var clickInteraction = function() {
    $('#about-selection').hide();
    return plot.jmol.showGroup({ 'data-nts': this.getAttribute('nt1') + ',' + this.getAttribute('nt2') });
  };

  var clickNucleotide = function() {
    $('#about-selection').hide();
    return plot.jmol.showGroup({ 'data-nts': this.id });
  };

  var brushShow = function(selection) {
    $('#about-selection').hide();
    return selection;
    //return plot.jmol.showSelection(selection);
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

  var plot = Rna2D({view: 'circular', width: 550, height: 400, selection: '#rna-2d' });

  plot.frame.add(false);
  plot.nucleotides(NTS);

  plot.brush.enabled(true)
    //.initial([[100, 36], [207, 132]])
    .update(brushShow);

  plot.jmol.overflow(function() { $("#overflow").show(); })
    .windowBuild(generateJmol);

  d3.text(INTERACTION_URL, 'text/csv', function(err, text) {
    if (err) {
      console.log(err);
      return false;
    }
    var interactions = d3.csv.parse('"nt1","family","nt2"\n' + text);
    plot.interactions(interactions);
    return plot();
  });

  return true;
});
