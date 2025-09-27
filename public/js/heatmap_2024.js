// Process data that is passed in with "data" variable, draw cells of a heat map

//console.log(d3);            // detect d3 version

var container = "";
var data_format = "";

// Detect new, more efficient data format
if (typeof data[0] == 'string') {
  // data is an array of length 3
  // element 0 is a string that tells what div the heat map should appear in
  // element 1 is a matrix of numbers, one for each cell of the heat map
  // element 2 is a list of strings, one for each row of the matrix
  // element 3 is optional, for non-square matrices
  // element 4 could be for text labels for diagonal entries
  // element 5 could be for API calls for diagonal entries

  data_format = "efficient";

  if (data[0] == 'new_data') {
    container = '#container';
  } else {
    // make sure to start this string with # so it is a reference
    container = data[0];
  }

  var data_matrix = data[1];                      // call the matrix "data"
  var rowLabelsData = Array.from(data[2]);  // labels for each row

  if (data.length == 4 && data[3].length > 0) {
    var columnLabelsData = Array.from(data[3]);  // labels for each column
  } else {
    var columnLabelsData = rowLabelsData;     // row and column labels the same
  }

} else {

  // data is an array of cell entries, an older and less efficient format
  // each cell entry tells the numerical value, row label, column label, row index, column index

  data_format = "verbose";

  var lookup = {};          // empty dictionary for fast processing
  var rowLabelsData = [];   // empty array

  // loop over entries, take unique indices and store names in the order they occur
  for (var i=0; i < data.length; i++) {
    name = data[i].ife2_index;   //

    if (!(name in lookup)) {
      lookup[name] = i;          // record where this label was seen
      rowLabelsData.push(data[i].ife2);  // add this label to the list
    }
  }

  // set up an empty matrix of the right size
  var data_matrix = [];   // empty matrix to store
  for (var i=0; i < rowLabelsData.length; i++) {
    data_matrix[i] = new Array(rowLabelsData.length);
  }

  // loop over entries again, store values in data_matrix
  for (var i=0; i < data.length; i++) {
    a = data[i].ife1_index;      // row number
    b = data[i].ife2_index;      // column number

    if (data[i].hasOwnProperty('discrepancy')) {
      data_matrix[a][b] = data[i].discrepancy;
    } else {
      console.log(a,b)
      data_matrix[a][b] = null;
    }
  }

  var columnLabelsData = rowLabelsData;     // row and column labels the same

  container = "#chart";    // standard name for the div where the heat map goes
}

// Find max and min values of data_matrix
const maxValue = d3.max(data_matrix, (layer) => {
  return d3.max(layer, (d) => {
    return d;
  });
});
// minValue should not be affected by negatives on diagonal that indicate extra colors
const minValue = Math.max(0,d3.min(data_matrix, (layer) => {
  return d3.min(layer, (d) => {
    return d;
  });
}));

const numrows = data_matrix.length;
const numcols = data_matrix[0].length;

// Make a list of cell data with row and column information
var cell_data = [];
for (var r = 0; r < numrows; r++) {
  for (var c = 0; c < numcols; c++) {
    new_cell = {d: data_matrix[r][c], r: r, c: c};
    cell_data.push(new_cell);
  }
}

const margin = { top: 0, right: 90, bottom: 40, left: 10 },
      width = 420,
      height = 420,
      startColor = "#FC7C89",
      endColor = "#21A38B";
      // startColor = "#440154",
      // endColor = "#fde725";

var viridisColor = ["#440154","#440256","#450457","#450559","#46075a","#46085c","#460a5d","#460b5e","#470d60","#470e61","#471063","#471164","#471365","#481467","#481668","#481769","#48186a","#481a6c","#481b6d","#481c6e","#481d6f","#481f70","#482071","#482173","#482374","#482475","#482576","#482677","#482878","#482979","#472a7a","#472c7a","#472d7b","#472e7c","#472f7d","#46307e","#46327e","#46337f","#463480","#453581","#453781","#453882","#443983","#443a83","#443b84","#433d84","#433e85","#423f85","#424086","#424186","#414287","#414487","#404588","#404688","#3f4788","#3f4889","#3e4989","#3e4a89","#3e4c8a","#3d4d8a","#3d4e8a","#3c4f8a","#3c508b","#3b518b","#3b528b","#3a538b","#3a548c","#39558c","#39568c","#38588c","#38598c","#375a8c","#375b8d","#365c8d","#365d8d","#355e8d","#355f8d","#34608d","#34618d","#33628d","#33638d","#32648e","#32658e","#31668e","#31678e","#31688e","#30698e","#306a8e","#2f6b8e","#2f6c8e","#2e6d8e","#2e6e8e","#2e6f8e","#2d708e","#2d718e","#2c718e","#2c728e","#2c738e","#2b748e","#2b758e","#2a768e","#2a778e","#2a788e","#29798e","#297a8e","#297b8e","#287c8e","#287d8e","#277e8e","#277f8e","#27808e","#26818e","#26828e","#26828e","#25838e","#25848e","#25858e","#24868e","#24878e","#23888e","#23898e","#238a8d","#228b8d","#228c8d","#228d8d","#218e8d","#218f8d","#21908d","#21918c","#20928c","#20928c","#20938c","#1f948c","#1f958b","#1f968b","#1f978b","#1f988b","#1f998a","#1f9a8a","#1e9b8a","#1e9c89","#1e9d89","#1f9e89","#1f9f88","#1fa088","#1fa188","#1fa187","#1fa287","#20a386","#20a486","#21a585","#21a685","#22a785","#22a884","#23a983","#24aa83","#25ab82","#25ac82","#26ad81","#27ad81","#28ae80","#29af7f","#2ab07f","#2cb17e","#2db27d","#2eb37c","#2fb47c","#31b57b","#32b67a","#34b679","#35b779","#37b878","#38b977","#3aba76","#3bbb75","#3dbc74","#3fbc73","#40bd72","#42be71","#44bf70","#46c06f","#48c16e","#4ac16d","#4cc26c","#4ec36b","#50c46a","#52c569","#54c568","#56c667","#58c765","#5ac864","#5cc863","#5ec962","#60ca60","#63cb5f","#65cb5e","#67cc5c","#69cd5b","#6ccd5a","#6ece58","#70cf57","#73d056","#75d054","#77d153","#7ad151","#7cd250","#7fd34e","#81d34d","#84d44b","#86d549","#89d548","#8bd646","#8ed645","#90d743","#93d741","#95d840","#98d83e","#9bd93c","#9dd93b","#a0da39","#a2da37","#a5db36","#a8db34","#aadc32","#addc30","#b0dd2f","#b2dd2d","#b5de2b","#b8de29","#bade28","#bddf26","#c0df25","#c2df23","#c5e021","#c8e020","#cae11f","#cde11d","#d0e11c","#d2e21b","#d5e21a","#d8e219","#dae319","#dde318","#dfe318","#e2e418","#e5e419","#e7e419","#eae51a","#ece51b","#efe51c","#f1e51d","#f4e61e","#f6e620","#f8e621","#fbe723","#fde725"];

// Build scales
const x = d3.scaleBand()
            .domain(d3.range(numcols))
            .range([0, width]);

const y = d3.scaleBand()
            .domain(d3.range(numrows))
            .range([0, height]);

var colorMap = d3.scaleLinear()
.domain(linspace(minValue, maxValue, viridisColor.length))
.range(viridisColor)

// Create the SVG container
const svg = d3
  .select(container)
  .append("svg")
  .attr("width", width + margin.left + margin.right)
  .attr("height", height + margin.top + margin.bottom)
  .append("g")
  .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

// Add a background to the SVG, the outer border of the heat map
// TODO: make the heat map fill the container more, smaller background
const background = svg
  .append("rect")
  .style("stroke", "black")
  .attr("width", width)
  .attr("height", height);

// Create the cells of the heat map
var heatMap = svg
  .selectAll()        // doesn't seem to need anything inside ()
  .data(cell_data);

// list from https://jacksonlab.agronomy.wisc.edu/2016/05/23/15-level-colorblind-friendly-palette/
// re-ordered and those that conflict with viridis removed, plus approximate color names
color_list = [
  "#920000",  //  0 reddish
  "#ff0000",  // -1 red
  "#ff6db6",  // -2 dark pink
  "#ffb6db",  // -3 pink
  "#490092",  // -4 purple
  "#b66dff",  // -5 violet
  "#6db6ff",  // -6 sky blue
  "#b6dbff",  // -7 light blue
  "#924900",  // -8 brown
  "#db6d00"]  // -9 and beyond orange

// Function to color one cell
function color_cell(cd) {
  if ((cd.r != cd.c) && (cd.d == null)) {
      return "#C0C0C0";       // gray color if no distance here
  } else if (cd.d == null) {
      return "";
  } else if (cd.d < 0) {
    return color_list[Math.min(color_list.length-1,Math.abs(Math.round(cd.d)))];
  } else {
    return colorMap(cd.d);  // scale distance into colormap
  }
}

// Add attributes to the cells of the heat map
heatMap
  .enter()
  .append("rect")
  .attr("x", function(cd) { return x[cd.c]; })
  .attr("y", function(cd) { return y[cd.r]; })
  //.attr("class", "bordered")
  .attr("width", x.bandwidth())
  .attr("height", y.bandwidth())
  .attr("transform", (cd) => {
    return "translate(" + x(cd.c) + "," + y(cd.r) + ")";
  })
  .style("fill", color_cell)
  .attr("orig_color", (cd) => {return color_cell(cd);})
  .attr("id", (cd) => {
    return "r" + cd.r + "c" + cd.c;  // use row and column to make a cell id
  })
  .on("click", function(cd) {
    // click
    // dblclick is a different event handler

    if (d3.event.ctrlKey) {
      console.log("Control-click on row: " + cd.r + " column: " + cd.c + " discrepancy: " + cd.d);

      row    = cd.r;
      column = cd.c;

      if (row < column) {
        // superimpose consecutive instances above diagonal
        for (i = row; i <= column; i++) {
          $('#' + i).jmolToggle();
        }

      } else if (row == column) {
        // display diagonal instance
        $('#' + row).jmolToggle();

      } else if (row > column) {
        // sumperimpose two instances below diagonal
        $('#' + row).jmolToggle();
        $('#' + column).jmolToggle();
      }
    } else {
      console.log("Click on row: " + cd.r + " column: " + cd.c + " discrepancy: " + cd.d);

      // It's enough to do this for one model
      $.jmolTools.models[1].hideAll();
      //$.jmolTools.models[1].uncolorAll();

      row    = cd.r;
      column = cd.c;

      if (row < column) {
        // superimpose consecutive instances above diagonal
        for (i = row; i <= column; i++) {
          $('#' + i).jmolShow();
        }

      } else if (row == column) {
        // display diagonal instance
        $('#' + row).jmolShow();

      } else if (row > column) {
        // sumperimpose two instances below diagonal
        $('#' + row).jmolShow();
        $('#' + column).jmolShow();
      }
    }
  })
  .on("contextmenu", function(cd) {
    // right click, which is what ctrl-click generates on a Mac
    d3.event.preventDefault();  // don't put up the context menu
    console.log("Right click on row: " + cd.r + " column: " + cd.c + " discrepancy: " + cd.d);
    row    = cd.r;
    column = cd.c;

    if (row < column) {
      // superimpose consecutive instances above diagonal
      for (i = row; i <= column; i++) {
        $('#' + i).jmolToggle();
      }

    } else if (row == column) {
      // display diagonal instance
      $('#' + row).jmolToggle();

    } else if (row > column) {
      // sumperimpose two instances below diagonal
      $('#' + row).jmolToggle();
      $('#' + column).jmolToggle();
    }
  })
  .append("title").text(function(cd) {
    if ((cd.r == cd.c) && (cd.d == null)) {
      return rowLabelsData[cd.r] + ':' + columnLabelsData[cd.c] + ' = 0';  // just use zero
    } else if ((cd.r != cd.c) && (cd.d == null)) {
      return rowLabelsData[cd.r] + ':' + columnLabelsData[cd.c] + ' not available';
    } else {
      return rowLabelsData[cd.r] + ':' + columnLabelsData[cd.c] + ' = ' + cd.d;
    }
  });


const labels = svg.append("g").attr("class", "labels");

// append gradient bar
var defs = svg.append('defs')

//Append a linearGradient element to the defs and give it a unique id
var linearGradient = defs.append("linearGradient")
  .attr("id", "linear-gradient");

//Horizontal gradient
linearGradient
  .attr("x1", "0%")
  .attr("y1", "0%")
  .attr("x2", "100%")
  .attr("y2", "0%");

function linspace(start, end, n) {
  var out = [];
  var delta = (end - start) / (n - 1);

  var i = 0;
  while(i < (n - 1)) {
    out.push(start + (i * delta));
    i++;
  }

  out.push(end);
  return out;
}

var pct = linspace(0, 1, viridisColor.length)

// console.log(pct)
// console.log(typeof pct[1])
var colourPct = d3.zip(pct, viridisColor);

colourPct.forEach(function(d) {
  linearGradient.append('stop')
    .attr('offset', d[0])
    .attr('stop-color', d[1])
    .attr('stop-opacity', 1);
});

svg.append("rect")
  .attr('x1', 0)
  .attr('y1', height)
  .attr("width", width)
  .attr("height", 15)
  .attr("transform", "translate(" + 0 + "," + (height + 5) + ")")
  .style("fill", "url(#linear-gradient)");

var legendScale = d3.scaleLinear()
    .domain([minValue, maxValue])
    .range([0, width])

// create an axis for the legend
var legendAxis = d3.axisBottom(legendScale)
  .ticks(5)
  .tickFormat(d3.format(".2f"));

svg.append("g")
  .attr("class", "legend axis")
  //.attr("height", 80)
  .text('Discrepancy')
  .attr("transform", "translate(" + 0 + "," + (height + 21) + ")")
  .call(legendAxis);


// Label the rows if the number of rows is not too large
// TODO: use a smaller font size for larger number of rows
if (rowLabelsData.length <= 200) {
  const rowLabels = labels
    .selectAll(".row-label")
    .data(rowLabelsData)
    .enter()
    .append("g")
    .attr("class", "row-label")
    .attr("transform", (d, i) => {
      return "translate(" + 0 + "," + y(i) + ")";
    });

  rowLabels
    .append("line")
    .style("stroke", "black")
    .style("stroke-width", "1px")
    .attr("x1", width)
    .attr("x2", width+4)
    .attr("y1", y.bandwidth() / 2)
    .attr("y2", y.bandwidth() / 2);


  const font_size = Math.min(10,Math.round(450/rowLabelsData.length));
  const font_and_size = font_size.toString() + 'px consolas';

  // find the maximum width of the row labels
  // var max_width = 0;
  // for (i=0; i<rowLabelsData.length; i++) {
  //   max_width = Math.max(max_width,rowLabelsData[i].length);
  // }
  // console.log("max_width: "+max_width)

  // put the x label on the right side of the heat map
  // .bandwidth refers to coordinates of the cell
  rowLabels
    .append("text")
    .attr("x", width + 5)
    .attr("y", y.bandwidth() / 2)
    .attr("dy", ".32em")             // helps with vertical centering
    .attr("text-anchor", "beginning")      // apparently "end" right justifies
    .style("font", font_and_size)    // set font and size, like "6px consolas"
    .text((d, i) => {
      return d;
    });

  //console.log(rowLabels)
  }

