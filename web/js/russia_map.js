// Variables
var width = 980, height = 500, active = d3.select(null);

// Behavior by zooming map
var zoom = d3.behavior.zoom().translate([0, 0]).scale(1).scaleExtent([1, 8]).on("zoom", function(){
    g_russia.style("stroke-width", 1 / d3.event.scale + "px");
    g_russia.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
});

// Init projection map
var projection = d3.geo.albers().rotate([-105, 0]).center([-10, 65]).parallels([52, 64]).scale(700).translate([width / 2, height / 2]);
var path = d3.geo.path().projection(projection);

// Init svg
var canvas = d3.select(".map-default-index").append("svg").attr("width", width).attr("height", height);

// Init active background
canvas.append("rect").attr("class", "background").attr("width", width).attr("height", height).attr("stroke-opacity", "0").attr("fill-opacity", "0").style("fill","red").on("click", cancel);

// Add group map
var g_russia = canvas.append("g").attr("class", "map");

// Render hover title
render_title();

// Render map
d3.json("/map/default/json", function(error, rus){
    if (error) return console.error(error);

    // Tmp parent properties
    window.tmp_data = rus.objects.russia.properties;

    window.data_russia = rus.objects.russia.properties;
    var boundary = topojson.feature(rus[0], rus[0].objects.boundary).features;
    var data_r = topojson.feature(rus, rus.objects.region).features;
    var data_c = topojson.feature(rus, rus.objects.city).features;
    var data_d = topojson.feature(rus, rus.objects.district170915).features;

    render_regions(data_r);
    render_district(data_d);
    render_boundary(boundary);
    render_cities(data_c);

    render_inform(data_russia.name, null, 1, data_russia);
});

// Cancel select path
function cancel()
{
    if(d3.select(".regions").style("display") != 'none'){
        d3.select(".regions").classed("fixed", false);
        update_inform(tmp_data.name, null, 1, tmp_data);
    }else{
        d3.select(".districts").classed("fixed", false);
        update_inform(tmp_data.NAME, null, 2, tmp_data);
    }

    d3.selectAll("path,circle").classed("active", false);
}

// Zooming region
function zooming(d) {
    active.classed("active", false);
    active = d3.select(this).classed("active", true);

    tmp_data = d.properties;

    var bounds = path.bounds(d),
        dx = bounds[1][0] - bounds[0][0],
        dy = bounds[1][1] - bounds[0][1],
        x = (bounds[0][0] + bounds[1][0]) / 2,
        y = (bounds[0][1] + bounds[1][1]) / 2,
        scale = .9 / Math.max(dx / width, dy / height),
        translate = [width / 2 - scale * x, height / 2 - scale * y];

    g_russia.transition().duration(750).call(zoom.translate(translate).scale(scale).event);

    d3.selectAll(".regions, .boundary, .city, text").style("display", "none");

    d3.selectAll(".district_" + d.properties.REG_ID + ", .city_" + d.properties.REG_ID).style("display", "block");

    d3.selectAll("circle").attr("r", function(d){ return (6/scale); }).attr("stroke-width", function(d) {return (7/scale)/3;});

    update_inform(d.properties.NAME, null, 2, tmp_data);
}

// Reset zooming region
function reset() {
    active.classed("active", false);
    active = d3.select(null);

    tmp_data = data_russia;

    g_russia.transition().duration(750).call(zoom.translate([0, 0]).scale(1).event);

    d3.selectAll(".regions, .boundary, .city, text").style("display", "block");

    d3.selectAll(".district").style("display", "none");

    d3.selectAll(".city, text").style("display", function(d){return (d.properties.PLACE == 'federal' || d.properties.NAME == 'Сургут') ? "block" : "none";});

    d3.selectAll("circle").attr("r", 5).attr("stroke-width", function(d) {return 7/3;});

    update_inform(data_russia.name, null, 1, data_russia);
}

// Rendering block information
function render_inform(name, select, level, json) {
    render_header(name, select, level, json);
    render_footer(json);
}

// Update block information
function update_inform(name, select, level, json) {
    render_header(name, select, level, json);
    update_footer(json);
}

// Rendering hover title
function render_title() {
    var label = d3.select(".map-default-index").append("div").attr("id", "label");
}

// Rendering header
function render_header(name, selector, level, json) {

    switch(level)
    {
        case 1:
            d3.select(".header").remove();
            var header = d3.select(".map-default-index").append("div").attr("class", "header").style("left", "15px");
            var button_back = header.append("img").attr("class", "flag").attr("src", (json.img) ? json.img : "");
            var header_title = header.append("div").attr("class", "header_title").html(name);
            break;
        case 2:
            d3.select(".header").remove();
            var header = d3.select(".map-default-index").append("div").attr("class", "header");
            var button_back = header.append("img").attr("class", "button-back").attr("src", "/button-back.png").on("click", reset);
            var gerb = header.append("img").attr("class", "header_gerb").attr("src", json.img);
            var header_title = header.append("div").attr("class", "header_title").html(name);
            break;
        default:
            d3.select(".header").remove();
            var header = d3.select(".map-default-index").append("div").attr("class", "header").style("left", "15px");
            var button_back = header.append("img").attr("class", "flag").attr("src", json.img);
            var header_title = header.append("div").attr("class", "header_title").html(name);
            break;
    }
}

// Rendering footer
function render_footer(json) {
    d3.select(".footer").remove();
    var footer = d3.select(".map-default-index").append("div").attr("class", "footer");
    var footer_inform = footer.append("div").attr("class", "inform");
    var footer_left = footer_inform.append("div").attr("class", "footer_left");
    var footer_right = footer_inform.append("div").attr("class", "footer_right");

    if(json){
        var ul_left = footer_left.append("ul").attr("class", "title").html('<p><span class="left">Система:</span><span class="right">фактического контроля строительства</span></p>');
        render_li(ul_left, "Строятся:", json.construct_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
        render_li(ul_left, "На сумму:", json.construct_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
        render_li(ul_left, "Проектируются:", json.design_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
        render_li(ul_left, "На сумму:", json.design_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
        render_li(ul_left, "Строят:", json.construct_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=2" + ((json.kladr_code) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));
        render_li(ul_left, "Проектируют:", json.design_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=3" + ((json.kladr_code) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));

        var ul_right = footer_right.append("ul").attr("class", "title").html('<p><span class="left">Система:</span><span class="right">стратегического контроля управления</span>');
        render_li(ul_right, "Показатель:", "");
        render_li(ul_right, "Показатель:", "");
        render_li(ul_right, "Показатель:", "");
        render_li(ul_right, "Показатель:", "");
    }

    var button = footer.append("div").attr("class", "button_more").html("Полная информация").on("click", more);
}

// Update footer
function update_footer(json) {
    var ul_left = d3.select(".footer_left ul.title");
    ul_left.selectAll("li").remove();
    render_li(ul_left, "Строятся:", json.construct_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
    render_li(ul_left, "На сумму:", json.construct_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
    render_li(ul_left, "Проектируются:", json.design_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
    render_li(ul_left, "На сумму:", json.design_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
    render_li(ul_left, "Строят:", json.construct_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=2" + ((json.kladr_code) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));
    render_li(ul_left, "Проектируют:", json.design_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=3" + ((json.kladr_code) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));

    var ul_right = d3.select(".footer_right .title");
    ul_right.selectAll("li").remove();
    render_li(ul_right, "Показатель:", "");
    render_li(ul_right, "Показатель:", "");
    render_li(ul_right, "Показатель:", "");
    render_li(ul_right, "Показатель:", "");
}

// Render li
function render_li(selector, caption, value, link) {
    var li_left = selector.append("li").attr("class", "statistic");

    li_left.append("div").attr("class", "left").html(caption);

    if(value && link) {
        li_left.append("div").attr("class", "right").html('<a target="_blank" href="' + link + '">' + value + '</a>');
    } else if(value){
        li_left.append("div").attr("class", "right").html(value);
    } else {
        li_left.append("div").attr("class", "right").html("информация закрыта");
    }
}

// More information
function more() {
    var overflow =  d3.select(".title").style("overflow");

    if(overflow == 'hidden') {
        d3.selectAll(".title").classed('title full', true);
    } else {
        d3.selectAll(".title").classed({'title':true,'full':false});
    }
}

// Rendering boundary
function render_boundary(boundary)
{
    var g_boundary = g_russia.append("g").attr("class", "boundary");
    g_boundary.selectAll("path").data(boundary).enter().append("path").attr("d", path).attr("stroke-width", 1.2).attr("stroke","#a1d568");
}

// Rendering regions
function render_regions(data){

    var g_regions = g_russia.append("g").attr("class", "regions");

    g_regions.selectAll("path")
        .data(data)
        .enter()
        .append("path")
        .attr("d", path)
        .attr("class", "region")
        .attr("region_id", function(d){ return d.properties.REG_ID; })
        .on("mousemove", function(d){
            d3.select("#label").transition().duration(300).style("opacity", 1);
            d3.select("#label").style("left", (d3.event.layerX - 10) + 'px').style("top", (d3.event.layerY - 45) + 'px').text(d.properties.name);

            if(!d3.select(".regions").classed("fixed"))
                update_inform(d.properties.NAME, this, 1, d.properties);
        })
        .on("mouseout", function(d){
            d3.select("#label").transition().duration(300).style("opacity", 0);

            if(!d3.select(".regions").classed("fixed"))
                update_inform(data_russia.name, null, 1, data_russia);
        })
        .on("dblclick", zooming)
        .on("click", function(d){

            if(d3.select(this).classed("active")){
                d3.selectAll("path,circle").classed("active", false);
                update_inform(data_russia.name, null, 1, data_russia);
                d3.select(this).classed("active", false);
                d3.select(".regions").classed("fixed", false);
            }
            else
            {
                d3.selectAll("path,circle").classed("active", false);
                d3.select(this).classed("active", true);
                var region_id = d.properties.REG_ID;
                update_inform(d.properties.NAME, this, 1, d.properties);
                d3.select(".regions").classed("fixed", true);
            }
        });
}

// Rendering districts
function render_district(data){
    var g_districts = g_russia.append("g").attr("class", "districts");

    g_districts.selectAll("path")
        .data(data)
        .enter()
        .append("path")
        .attr("d", path)
        .attr("class", function(d){ return "district district_" + d.properties.REG_ID; })
        .on("mousemove", function(d){
            var region_name = d.properties.NAME;

            d3.select("#label")
                .transition()
                .duration(300)
                .style("opacity", 1);
            d3.select("#label")
                .style("left", (d3.event.layerX - 10) + 'px')
                .style("top", (d3.event.layerY - 45) + 'px')
                .text(region_name);
            if(!d3.select(".districts").classed("fixed")) {
                var region_id = d.properties.REG_ID;
                update_inform(d.properties.NAME, this, 2, d.properties);
            }
        })
        .on("mouseout", function(d){
            d3.select("#label")
                .transition()
                .duration(300)
                .style("opacity", 0);
            if(!d3.select(".districts").classed("fixed")) {
                var region_id = d.properties.REG_ID;
                update_inform(tmp_data.NAME, this, 2, tmp_data);
            }
        })
        .on("click", function(d){
            if(d3.select(this).classed("active")){
                region_id = d.properties.REG_ID;
                update_inform(tmp_data.NAME, null, 2, tmp_data);
                d3.selectAll("path,circle").classed("active", false);
                d3.select(".districts").classed("fixed", false);
            }
            else{
                update_inform(d.properties.NAME, this, 2, d.properties);
                d3.selectAll("path,circle").classed("active", false);
                d3.select(this).classed("active", true);
                d3.select(".districts").classed("fixed", true);
            }
        });
}

// Rendering cities
function render_cities(data){

    var g_cities = g_russia.append("g").attr("class", "cities");

    var city = g_cities.selectAll("g.city")
        .data(data)
        .enter()
        .append("g")
        .attr("transform", function(d) { return "translate(" + projection([d.geometry.coordinates[0], d.geometry.coordinates[1]]) + ")"; })
        .attr("class", function(d){return "city_" + d.properties.REG_ID;})
        .style("display", function(d){return (d.properties.PLACE == 'federal' || d.properties.NAME == 'Сургут') ? "block" : "none";})
        .classed({"city":true,"federal":( function(d){ return (d.properties.PLACE == 'federal') ? true : false})});

    city.append("circle")
        .attr("r", 5)
        .attr("stroke-width", 2)
        .style("opacity", 0.75)
        .on("mousemove", function(d){
            var region_name = d.properties.NAME;

            d3.select("#label")
                .transition()
                .duration(300)
                .style("opacity", 1);
            d3.select("#label")
                .style("left", (d3.event.layerX - 10) + 'px')
                .style("top", (d3.event.layerY - 45) + 'px')
                .text(region_name);

            if(d3.select(".regions").style("display") != 'none'){
                if(!d3.select(".regions").classed("fixed")) {
                    var region_id = d.properties.REG_ID;
                    update_inform(d.properties.NAME, this, 1, d.properties);
                }
            }else{
                if(!d3.select(".districts").classed("fixed")) {
                    var region_id = d.properties.REG_ID;
                    update_inform(d.properties.NAME, this, 2, d.properties);
                }
            }

        })
        .on("mouseout", function(d){
            d3.select("#label")
                .transition()
                .duration(300)
                .style("opacity", 0);
            if(d3.select(".regions").style("display") != 'none'){
                if(!d3.select(".regions").classed("fixed")) {
                    var region_id = d.properties.REG_ID;
                    update_inform(tmp_data.NAME, this, 1, tmp_data);
                }
            }else{
                if(!d3.select(".districts").classed("fixed")) {
                    var region_id = d.properties.REG_ID;
                    update_inform(tmp_data.NAME, this, 2, tmp_data);
                }
            }

        })
        .on("click", function(d){
            if(d3.select(".regions").style("display") != 'none'){
                if(d3.select(this).classed("active")) {
                    d3.select(".regions").classed("fixed", false);
                    update_inform(data_russia.NAME, null, 1, data_russia);
                    d3.selectAll("path,circle").classed("active", false);
                }else {
                    update_inform(d.properties.NAME, null, 1, d.properties);
                    d3.select(".regions").classed("fixed", true);
                    d3.selectAll("path,circle").classed("active", false);
                    d3.select(this).classed("active", true);
                }
            }else{
                if(d3.select(this).classed("active")) {
                    update_inform(tmp_data.NAME, null, 2, tmp_data);
                    d3.selectAll("path,circle").classed("active", false);
                    d3.select(".districts").classed("fixed", false);
                }else {
                    update_inform(d.properties.NAME, null, 2, d.properties);
                    d3.selectAll("path,circle").classed("active", false);
                    d3.select(this).classed("active", true);
                    d3.select(".districts").classed("fixed", true);
                }
            }
        })
        .on("dblclick", function(d){
            if(d.properties.NAME == 'Сургут') window.open('http://surgut2030.usirf.ru', '_blank');
        })
        .style("fill", function(d){ return (d.properties.NAME == 'Сургут') ? "red" : ""; });

    city.append("text")
        .attr("class", ".city_text")
        .attr("y", -8)
        .text(function(d) { return d.properties.NAME; })
        .style("fill", "#000")
        .style("display", function(d){return (d.properties.PLACE == 'federal' || d.properties.NAME == 'Сургут') ? "block" : "none";});
}