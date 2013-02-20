$(document).ready(function() {

  $('#about-selection').hide();

  var generateJmol = function($jmol) {
    var form = '<button type="button" id="neighborhood" class="btn">Show neighborhood</button>' +
      ' ' +
      '<button type="button" id="stereo" class="btn">Stereo</button>' +
      ' ' +
      '<label><input type="checkbox" id="showNtNums">Show Numbers</label>';
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
    if (plot.view() == 'circular') {
      plot.pie.addLetters()([this]);
    } else {
      d3.select(this).style('font-size', plot.nucleotides.fontSize() + 4)
        .style('fill', 'red');
    }
    var inters = plot.nucleotides.interactions(this);
    inters.style('opacity', 1);
  };

  var normalizeNucleotide = function() {
    var inters = plot.nucleotides.interactions(this);
    inters.style('opacity', 0.4);
    if (plot.view() === 'circular') {
      plot.pie.clearLetters()();
    } else {
      d3.select(this).style('font-size', plot.nucleotides.fontSize())
        .style('fill', null);
    }
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

  var brushShow = function(selection) {
    var ids = {};
    $('#' + plot.jmol.divID()).show();
    $.each(selection, function(id, entry) { ids[normalizeID(id)] = entry; });
    $('#about-selection').hide();
    return plot.jmol.showSelection(ids);
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

  $('.view-control').on('click', function(e) {
    var $btn = $(e.target),
        view = $btn.data('view');

    plot.view(view);

    // TODO: Clear brush and jmol when switching views
    plot.brush.clear();
    $('#' + plot.jmol.divID()).hide();

    if (view === 'airport') {
      plot.height(11/8 * plot.width());
    } else {
      plot.height(400);
    }

    $('.view-control').removeClass('active');
    $btn.addClass('active');

    $('.toggle-control').removeClass('active');
    $('#cWW-toggle').addClass('active');
    plot();
  });
  

  var convertNTID = function(id) { return id.replace(/\|/g, '_'); };
  var normalizeID = function(id) { return id.replace(/_/g, '|'); };
  var ntURL = function(id) { return 'http://rna.bgsu.edu/rna3dhub/unitid/describe/' + encodeURIComponent(id); };
  var ntLink = function(id) { return '<a target="_blank" href="' + ntURL(id) + '">' + id + "</a>"; };

  var plot = Rna2D({view: 'circular', width: 500, height: 400, selection: '#rna-2d' });

  plot.frame.add(false);
  plot.nucleotides(NTS)
    .getID(function(d, i) { return convertNTID(d['id']); })
    .click(clickNucleotide)
    .mouseover(highlightNucleotide)
    .mouseout(normalizeNucleotide);

  plot.brush.enabled(true)
    .update(brushShow);

  plot.jmol.overflow(function() { $("#overflow").show(); })
    .stereoID('stereo')
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
      .click(clickInteraction)
      .mouseover(highlightInteraction)
      .mouseout(normalizeInteraction);

    return plot();
  });

  return true;
});
