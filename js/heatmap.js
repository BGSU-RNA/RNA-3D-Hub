  // put this into a function to generate the x/y-axis data labels
  // get the unique values of the ife's
  var lookup = {};
  var items = data;
  var ife_nr = [];

  for (var item, i = 0; item = items[i++];) {
      var name = item.ife1;

      if (!(name in lookup)) {
        lookup[name] = 1;
        ife_nr.push(name);
      }
  }

  // Calculate the size of ife_nr array
  ife_nr_size = ife_nr.length;

  // Set the dimensions of the canvas
  var margin = {top: 90, right: 0, bottom: 200, left: 90},
    width = 600 - margin.left - margin.right,
    height = (660 - margin.top - margin.bottom) + 150,
    gridSize = Math.floor(width / ife_nr_size),
    legendElementWidth = Math.floor(width / 6);

      // the unary operator (+) converts a numeric string into a number
      data.forEach(function(d) {
        ife1 = d.ife1;
        ife1_index = +d.ife1_index
        ife2 = d.ife2;
        ife2_index = +d.ife2_index
        discrepancy = +d.discrepancy;
      });

      // At the moment, the values are hard-coded. Need to find a better alternative
      // Might need to use quantile scale
      var colorScale = d3.scale.linear()
        .domain([0.0,1.0,2.0,3.0,4.0,5.0])
        .range(['#ffffb2', '#fed976','#feb24c','#fd8d3c','#f03b20','#bd0026'])

      // Set the svg container
      var svg = d3.select("#chart").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        // read on this
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      // Draw the x-axis label
      var dayLabels = svg.selectAll(".dayLabel")
        .data(ife_nr)
        .enter().append("text")
          .text(function(d) { return d; })
          .attr("x", 0)
          .attr("y", function(d, i) {
          return i * gridSize;
          })
          .style("text-anchor", "end")
          .attr("transform", "translate(-5," + gridSize / 1.5 + ")")
          .attr("class", function(d, i) {
            return ((i >= 0)
            ? "dayLabel mono axis axis-workweek" : "dayLabel mono axis");
          });

      // Draw the y-axis label
      // Need to draw this vertically (the data elements can be large!)
    /*  var timeLabels = svg.selectAll(".timeLabel")
        .data(ife_nr)
        .enter().append("text")
        //.attr(style="writing-mode: tb; glyph-orientation-vertical: 0;")
        .text(function(d) {
          return d;
        })
        .attr("x", function(d, i) {
          return  (i * gridSize);
        })
        .attr("y", 0)
        .style("text-anchor", "middle")
        .attr("transform", "translate(" + gridSize / 2 + '-5' + ")")
        .attr("class", function(d, i) {
          return ((i >= 0) ? "timeLabel mono axis axis-worktime" : "timeLabel mono axis");
        }); */

      // Create the paired elements
      var heatMap = svg.selectAll(".ife2_index")
        .data(data, function(d) { return d.ife1_index+':'+d.ife2_index; });

      // Draw the grid to make the heatmap
      heatMap.enter().append("rect")
        .attr("x", function(d) { return d.ife2_index * gridSize; })
        .attr("y", function(d) { return d.ife1_index * gridSize; })
        .attr("rx", 4)
        .attr("ry", 4)
        //.attr("class", "bordered")
        .attr("width", gridSize)
        .attr("height", gridSize)
        .style("fill", function(d) {
            return colorScale(d.discrepancy);
        });

      // Show the value of discrepancy between two iefe's when the user hovers over a heatmap grid
      heatMap.append("title").text(function(d) {
        return d.ife1 + ':' + d.ife2 + ' = ' + d.discrepancy;
      });
      //
      heatMap.exit().remove();

      var legend = svg.selectAll(".legend")
        .data(colorScale.domain())
      legend.enter().append("g")
        .attr("class", "legend");

      // Draw the legend
      legend.append("rect")
        .attr("x", function(d, i) {
          return legendElementWidth * i;
        })
        .attr("y", height)
        .attr("width", legendElementWidth)
        .attr("height", 12 )
        .style("fill", function(d) {
          console.log(colorScale.domain());
          return colorScale(d);
        });

      // Add text to the legend
      legend.append("text")
        .attr("class", "mono")
        .text(function(d) {
          return (d);
        })
        .attr("x", function(d, i) {
          return legendElementWidth * i;
        })
        .attr("y", height + 28);
      legend.exit().remove();
