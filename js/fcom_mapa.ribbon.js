var margin = {top: -5, right: -5, bottom: -5, left: -5},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom,
    padding = 5;

var zoom = d3.behavior.zoom()
    .scaleExtent([0.1, 1.5])
    .translate([width / 2, height / 2])
    .on("zoom", zoomed);

var drag = d3.behavior.drag()
    .origin(function(d) { return d; })
    .on("dragstart", dragstarted)
    .on("drag", dragged)
    .on("dragend", dragended);

var color = d3.scale.category20();
var force = d3.layout.force()
    .size([width, height]);

var svg = d3.select("#fcom-mapa").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.right + ")")
    .attr("id", "svg-container")
    //.call(zoom)
    ;
    
var rect = svg.append("rect")
    .attr("width", width)
    .attr("height", height)
    .style("fill", "none")
    .style("pointer-events", "all");
    
var container = svg.append("g");

var defaultZoom = 0.3;
zoom.scale(defaultZoom);
zoom.event(svg.transition().duration(500));
    
d3.json('fcom-tags/json/data', function(error, graph) {
  if (error) throw error;

  force
      .nodes(graph.nodes)
      .links(graph.links)
      .start()
    .friction(0.6)
    .linkDistance(0)
    .gravity(0.04)
    .theta(0.8)
    .alpha(0.1);
    //.start();

    force.linkDistance(function(link) {
        if (link.classname == 'ancla_tag')
        {
            return 0;
        }
        else if (link.classname == 'tag_articulo')
        {
            return 0;
        }
        else
        {
            return 50;
        }
    });
    
    force.linkStrength(function(link) {
        if (link.classname == 'ancla_tag')
        {
            return 1;
        }
        else if (link.classname == 'tag_articulo')
        {
            return 0.2;
        }
        else
        {
            return 0.005;
        }
    });
    
    
    
    force.nodes().forEach(function(node){if (node.classname ==="ancla") node.fixed = true; });
    
    force.start()

  var link = container.selectAll(".link")
      .data(graph.links)
    .enter().append("line")
      .attr("class", "link")
      .style("stroke-width", function(d) { return Math.sqrt(d.value); });
      
  var parent_nodes = graph.nodes.filter(function(obj){ return obj.classname==="ancla";});
  var tags = graph.nodes.filter(function(obj){ return obj.classname==="tag";});
  var articulos = graph.nodes.filter(function(obj){ return obj.classname==="articulo";});   
  
  //Anclas    
  var node_p = container.selectAll(".node")
      .data(parent_nodes)
    .enter().append("circle")
      .attr("class", "node-p")
      .attr("r", 20)
      .style("fill", "#23F")
      //.call(force.drag)
      ;
  //Tags    
  var node_h = container.selectAll(".node")
      .data(tags)
    .enter().append("circle")
      .attr("class", "node-h")
      .attr("r", 15)
      .style("fill", "#F3A")
      //.call(force.drag)
      ;
      
  var node_a = container.selectAll(".node")
      .data(articulos)
    .enter().append("g")
      .attr("class", function(d) { return "node-a "+d.medioClass;})
      .attr("transform", function(d) { return "translate("+d.x+","+d.y+")"; })
      .append("a")
          .attr("xlink:href", function(d){return d.path;})
      //.call(force.drag)
      ;
  // Caja completa 
  poly = "-164,-73 164,-73 164,12 132,73 -164,73" ;
  node_a
          .append("polygon")
          .attr("x",-164)
          .attr('y',-73)          
          .attr("points", poly)
          .style("fill", "#397BDE")
          ;
  //Titulo  
  node_a
    .append("text")
    .attr("x",-66)
    .attr("y",-61)
    .attr("text-anchor", "left")
    .attr("font-family", "sans-serif")
    .attr("font-size", "24px")
    .style("color", "white")
    .style("fill", "white")
    .each(function (d) {
        var lines = wordwrap(d.titulo, 16)

        for (var i = 0; i < lines.length && i < 5; i++) {
            if (i == 4)
            {
                d3.select(this).append("tspan").attr("dy",24).attr("x",-66).text(lines[i]+"...");
            }
            else 
            {
                d3.select(this).append("tspan").attr("dy",24).attr("x",-66).text(lines[i]);
            }
            
        }
    })
    .attr("xlink:href", function(d){return d.path;})
    ;
  //Fecha        
  //Día
  node_a
          .append("text")
          .attr("x", -150)
          .attr("y", -15)
          .attr("width", 106)
          .attr("height", 16)
          .style("color", "white")
          .style("fill", "white")
          .text(function(d){ return d.fecha.dia; })
          .attr("font-family", "sans-serif")
          .attr("font-size", "50px")
          ;
  //Mes
  node_a
          .append("text")
          .attr("x", -150)
          .attr("y", 23)
          .attr("width", 106)
          .attr("height", 16)
          .style("color", "white")
          .style("fill", "white")
          .text(function(d){ return d.fecha.mes; })
          .attr("font-family", "sans-serif")
          .attr("font-size", "32px")
          ;
  //Año
  node_a
          .append("text")
          .attr("x", -150)
          .attr("y", 57)
          .attr("width", 106)
          .attr("height", 16)
          .style("color", "white")
          .style("fill", "white")
          .text(function(d){ return d.fecha.agno; })
          .attr("font-family", "sans-serif")
          .attr("font-size", "24px")
          ;

  node_p.append("title")
      .text(function(d) { return d.name; });
      
  node_h.append("title")
      .text(function(d) { return d.name; });
      
  node_a.append("title")
      .text(function(d) { return d.name; });

  force.on("tick", function(e) {
        link.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });

        node_p.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
            
        node_h.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
            
        node_a.attr("x", function(d) { return d.x; })
            .attr("y", function(d) { return d.y; });

        var k = e.alpha * 0.1;
        var w = 1;
        nodes = force.nodes();
        nodes.forEach(function(node) {
            if (node.classname=="articulo")
            {
            node.fuerzas.forEach(function(fuerza)
                {
                    if (node.grupo_tag == fuerza.grupo)
                    {
                        w = node.grupo_weight;
                    }
                    else
                    {
                        w = 1;
                    }
                    node.x += (nodes[fuerza.nodo].x - node.x) * k * w;
                    node.y += (nodes[fuerza.nodo].y - node.y) * k * w;
                });
            }
            });
        node_a.attr("transform", function(d) { return "translate("+d.x+","+d.y+")"; });
        
    });
    force.start();
    
    setTimeout(function(){
        force.nodes().forEach(function(node){if (node.classname ==="ancla") node.fixed = false; });
        force.charge(function(node) {
            if (node.className === 'ancla')  return 40000
            else if (node.className === 'tag')  return 40000
            return -15000;
            });
        
        force.on("tick", function(e) {
        link.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });

        node_p.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
            
        node_h.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
            
        node_a.attr("x", function(d) { return d.x; })
            .attr("y", function(d) { return d.y; });

        var k = e.alpha * 0.1;
        var w = 1;
        nodes = force.nodes();
        nodes.forEach(function(node) {
            if (node.classname=="articulo")
            {
            node.fuerzas.forEach(function(fuerza)
                {
                    if (node.grupo_tag == fuerza.grupo)
                    {
                        w = node.grupo_weight;
                    }
                    else
                    {
                        w = 1;
                    }
                    node.x += (nodes[fuerza.nodo].x - node.x) * k * w;
                    node.y += (nodes[fuerza.nodo].y - node.y) * k * w;
                });
            }
            });
        node_a.each(collide(0.33)).attr("transform", function(d) { return "translate("+d.x+","+d.y+")"; });
        
    });
        
        force.start();
            },500);
    
    
});

function zoomed() {
  container.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}

function dragstarted(d) {
  d3.event.sourceEvent.stopPropagation();
  d3.select(this).classed("dragging", true);
}

function dragged(d) {
  //d3.select(this).attr("cx", d.x = d3.event.x).attr("cy", d.y = d3.event.y);
}

function dragended(d) {
  d3.select(this).classed("dragging", false);
}

function collide(alpha) {
  var quadtree = d3.geom.quadtree(nodes);
  return function(d) {
    
    var nx1 = d.x - (164+padding),
        nx2 = d.x + (164+padding),
        ny1 = d.y - (73+padding),
        ny2 = d.y + (73+padding),
        ancho = 164+padding, alto = 73+padding;
    
    quadtree.visit(function(quad, x1, y1, x2, y2) {
      if (quad.point && (quad.point !== d)) {
        var p = {x1: d.x-ancho , x2: d.x+ancho, y1: d.y-alto, y2: d.y+alto }
            q = {x1: quad.point.x-ancho , x2: quad.point.x+ancho, y1: quad.point.y-alto, y2: quad.point.y+alto };
   
        if (p.x1 <= q.x2 &&
              q.x1 <= p.x2 &&
              p.y1 <= q.y2 &&
              q.y1 <= p.y2)
        {
          var d_x = p.x1-q.x1,
              d_y = p.y1-q.y1,
              adx = Math.abs(d_x),
              ady = Math.abs(d_y),
              mdx = ancho*2, mdy = alto*2,
              l = Math.sqrt(d_x*d_x + d_y*d_y),
              lx = (adx - mdx) / l * alpha,
              ly = (ady - mdy) / l * alpha;
              alx = Math.abs(lx),
              aly = Math.abs(ly);

              // choose the direction with less overlap
              if (alx > aly  &&  aly > 0) lx = 0;
              else if (aly > alx  &&  alx > 0) ly = 0;
              
              d.x -= d_x *= lx;
              quad.point.x += d_x;
              d.y -= d_y *= ly;
              quad.point.y += d_y;

        }
      }
      return x1 > nx2 || x2 < nx1 || y1 > ny2 || y2 < ny1;
    });
  };
}



function wordwrap(text, max) {
    var regex = new RegExp(".{0,"+max+"}(?:\\s|$)","g");
    var lines = []

    var line
    while ((line = regex.exec(text))!="") {
        lines.push(line);
    } 

    return lines
}

//***********************************************************
// PAN-ZOOM CONTROL - FUNCTIONAL STYLE
//***********************************************************
var makePanZoomCTRL = function(id, width, height) {
var control = {}

var zoomMin = -9, // Levels of Zoom Out
    zoomMax =  5, // Levels of Zoom In
    zoomCur =   -7, // Current Zoom
    offsetX =   width/2, // Current X Offset (Pan)
    offsetY =   height/2; // Current Y Offset (Pan)

var transform = function () {
  var x = -((width  * zoomCur / 10) / 2)  + offsetX;
  var y = -((height * zoomCur / 10) / 2)  + offsetY;
  var s = (zoomCur / 10) + 1;
    
  /*d3.select(id).transition().duration(750)
    .attr("transform", "translate(" + x + " " + y + ") scale(" + s + ")");*/
  zoom.translate([x,y]);
  zoom.scale(s);
  zoom.event(svg.transition().duration(100));

};

control.pan = function (btnID) {
  offsetX = zoom.translate()[0]+width*zoomCur/20;
  offsetY = zoom.translate()[1]+height*zoomCur/20;
  
  if (btnID === "panLeft") {
    offsetX += 50;
  } else if (btnID === "panRight") {
    offsetX -= 50;
  } else if (btnID === "panUp") {
    offsetY += 50;
  } else if (btnID === "panDown") { 
    offsetY -= 50;
  }
  transform();
};

control.zoom = function (btnID) {
  zoomCur = zoom.scale()*10-10;
  if (btnID === "zoomIn") {
    if (zoomCur >= zoomMax) return;
    zoomCur++;
  } else if (btnID === "zoomOut") {
    if (zoomCur <= zoomMin) return;
    zoomCur--;
  }
  transform();
};
return control;
}

//***********************************************************
// INSTANTIATE PAN-ZOOM CONTROL (CREATE INSTANCE)
//***********************************************************
var panZoom = makePanZoomCTRL('#svg-container', width, height);

//***********************************************************
// SET BUTTON EVENT LISTENERS
//***********************************************************
d3.selectAll("#zoomIn, #zoomOut")
.on("click", function () {
  d3.event.preventDefault();
  var id = d3.select(this).attr("id");
  panZoom.zoom(id);
});

d3.selectAll("#panLeft, #panRight, #panUp, #panDown")
.on("click", function () {
  d3.event.preventDefault();
  var id = d3.select(this).attr("id");
  panZoom.pan(id);
});
