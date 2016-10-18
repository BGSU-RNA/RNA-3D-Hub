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
  var margin = {top: 20, right: 0, bottom: 70, left: 90},
    width = 600 - margin.left - margin.right,
    height = (600 - margin.top - margin.bottom),
    gridSize = Math.floor(width / ife_nr_size),
    legendElementWidth = Math.floor(width/6);

   var viridisColor = ["#440154","#440256","#450457","#450559","#46075a","#46085c","#460a5d","#460b5e","#470d60","#470e61","#471063","#471164","#471365","#481467","#481668","#481769","#48186a","#481a6c","#481b6d","#481c6e","#481d6f","#481f70","#482071","#482173","#482374","#482475","#482576","#482677","#482878","#482979","#472a7a","#472c7a","#472d7b","#472e7c","#472f7d","#46307e","#46327e","#46337f","#463480","#453581","#453781","#453882","#443983","#443a83","#443b84","#433d84","#433e85","#423f85","#424086","#424186","#414287","#414487","#404588","#404688","#3f4788","#3f4889","#3e4989","#3e4a89","#3e4c8a","#3d4d8a","#3d4e8a","#3c4f8a","#3c508b","#3b518b","#3b528b","#3a538b","#3a548c","#39558c","#39568c","#38588c","#38598c","#375a8c","#375b8d","#365c8d","#365d8d","#355e8d","#355f8d","#34608d","#34618d","#33628d","#33638d","#32648e","#32658e","#31668e","#31678e","#31688e","#30698e","#306a8e","#2f6b8e","#2f6c8e","#2e6d8e","#2e6e8e","#2e6f8e","#2d708e","#2d718e","#2c718e","#2c728e","#2c738e","#2b748e","#2b758e","#2a768e","#2a778e","#2a788e","#29798e","#297a8e","#297b8e","#287c8e","#287d8e","#277e8e","#277f8e","#27808e","#26818e","#26828e","#26828e","#25838e","#25848e","#25858e","#24868e","#24878e","#23888e","#23898e","#238a8d","#228b8d","#228c8d","#228d8d","#218e8d","#218f8d","#21908d","#21918c","#20928c","#20928c","#20938c","#1f948c","#1f958b","#1f968b","#1f978b","#1f988b","#1f998a","#1f9a8a","#1e9b8a","#1e9c89","#1e9d89","#1f9e89","#1f9f88","#1fa088","#1fa188","#1fa187","#1fa287","#20a386","#20a486","#21a585","#21a685","#22a785","#22a884","#23a983","#24aa83","#25ab82","#25ac82","#26ad81","#27ad81","#28ae80","#29af7f","#2ab07f","#2cb17e","#2db27d","#2eb37c","#2fb47c","#31b57b","#32b67a","#34b679","#35b779","#37b878","#38b977","#3aba76","#3bbb75","#3dbc74","#3fbc73","#40bd72","#42be71","#44bf70","#46c06f","#48c16e","#4ac16d","#4cc26c","#4ec36b","#50c46a","#52c569","#54c568","#56c667","#58c765","#5ac864","#5cc863","#5ec962","#60ca60","#63cb5f","#65cb5e","#67cc5c","#69cd5b","#6ccd5a","#6ece58","#70cf57","#73d056","#75d054","#77d153","#7ad151","#7cd250","#7fd34e","#81d34d","#84d44b","#86d549","#89d548","#8bd646","#8ed645","#90d743","#93d741","#95d840","#98d83e","#9bd93c","#9dd93b","#a0da39","#a2da37","#a5db36","#a8db34","#aadc32","#addc30","#b0dd2f","#b2dd2d","#b5de2b","#b8de29","#bade28","#bddf26","#c0df25","#c2df23","#c5e021","#c8e020","#cae11f","#cde11d","#d0e11c","#d2e21b","#d5e21a","#d8e219","#dae319","#dde318","#dfe318","#e2e418","#e5e419","#e7e419","#eae51a","#ece51b","#efe51c","#f1e51d","#f4e61e","#f6e620","#f8e621","#fbe723","#fde725"];

      // the unary operator (+) converts a numeric string into a number
      data.forEach(function(d) {
        ife1 = d.ife1;
        ife1_index = +d.ife1_index
        ife2 = d.ife2;
        ife2_index = +d.ife2_index
        discrepancy = +d.discrepancy;
      });


      var domainMax = d3.max(data, function(d) {return +d.discrepancy;});

      var colorScale = d3.scaleLinear()
        .domain(linspace(0, domainMax, viridisColor.length))
        .range(viridisColor)
        .clamp(true);
      
      //var colorScale = d3.scaleSequential(d3.interpolateViridis)
                         //.domain([0, domainMax])
                         //.clamp(0,2);

      // Set the svg container
      var svg = d3.select("#chart").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      // Draw the y-axis label
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
          //
          .attr("class", function(d, i) {
            return ((i >= 0)
            ? "dayLabel mono axis axis-workweek" : "dayLabel mono axis");
          });

      // Draw the x-axis label
      // Need to draw this vertically (the data elements can be large!)
      var timeLabels = svg.selectAll(".timeLabel")
        .data(ife_nr)
        .enter().append("text")
        .text(function(d) {
          return d;
        })
        .attr("x", function(d, i) {
          return  (i * gridSize);
        })
        .attr("y", 0)
        //.style("text-anchor", "middle")
        //.attr("transform", "translate(" + gridSize/2 + '-5' + ")")
        .attr("transform", "rotate(90 " + (i * gridSize) + " 0)")
        .attr("class", function(d, i) {
          return ((i >= 0) ? "timeLabel mono axis axis-worktime" : "timeLabel mono axis");
        });

      // Create the paired elements
      var heatMap = svg.selectAll(".ife2_index")
        .data(data, function(d) { return d.ife1_index+':'+d.ife2_index; });

      // Draw the grid to make the heatmap
      heatMap.enter().append("rect")
        .attr("x", function(d) { return d.ife2_index * gridSize; })
        .attr("y", function(d) { return d.ife1_index * gridSize; })
        //.attr("class", "bordered")
        .attr("width", gridSize)
        .attr("height", gridSize)
        .style("fill", function(d) {
          if ((d.ife1 != d.ife2) && (d.discrepancy == null)) {
              return "#808080";
          } else {
              return colorScale(d.discrepancy);
          }
        })
        .append("title")
        .text(function(d) {

          //return d.discrepancy;

          if ((d.ife1 == d.ife2) && (d.discrepancy == null)) {
            d.discrepancy = 0;
            return d.ife1 + ':' + d.ife2 + ' = ' + d.discrepancy;
          } else if ((d.ife1 != d.ife2) && (d.discrepancy == null)) {
              return 'No discrepancy value is computed between ' + d.ife1 + ' and ' + d.ife2;
          } else {
              return d.ife1 + ':' + d.ife2 + ' = ' + d.discrepancy;
          }


        });

      heatMap.exit().remove();

      // append gradient bar
      var defs = svg.append('defs')
      //.attr("transform", "translate(" + (width) + "," + (height+50) + ")");

      //Append a linearGradient element to the defs and give it a unique id
      var linearGradient = defs.append("linearGradient")
        .attr("id", "linear-gradient");

      //Horizontal gradient
      linearGradient
        .attr("x1", "0%")
        .attr("y1", "0%")
        .attr("x2", "100%")
        .attr("y2", "0%");

      // programatically generate the gradient for the legend
      // this creates an array of [pct, colour] pairs as stop
      // values for legend
      var pct = linspace(0, 100, viridisColor.length).map(function(d) {
        return Math.round(d) + '%';
      });

      var colourPct = d3.zip(pct, viridisColor);

      colourPct.forEach(function(d) {
        linearGradient.append('stop')
          .attr('offset', d[0])
          .attr('stop-color', d[1])
          .attr('stop-opacity', 1);
      });

      //Draw the rectangle and fill with gradient
      svg.append("rect")
        .attr('x1', 0)
        .attr('y1', height)
        .attr("width", width)
        .attr("height", 15)
        .attr("transform", "translate(" + 0 + "," + (height + 5) + ")")
        .style("fill", "url(#linear-gradient)");

      // create a scale and axis for the legend
      var legendScale = d3.scaleLinear()
        .domain([0, domainMax])
        .range([0, width])
        .clamp(true);

      var legendAxis = d3.axisBottom()
        .scale(legendScale)
        //.tickValues(d3.range(0, 0.2))
        .ticks(3)
        .tickFormat(d3.format(".2f"));

      svg.append("g")
        .attr("class", "legend axis")
        //.attr("height", 80)
        .attr("transform", "translate(" + 0 + "," + (height + 17) + ")")
        .call(legendAxis);

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