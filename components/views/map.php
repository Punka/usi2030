<?php
use app\assets\MapAsset;
MapAsset::register($this);

$db = new yii\db\Connection([
    'dsn' => 'pgsql:host=psdb1.cdii5kanexo4.eu-west-1.rds.amazonaws.com;port=5432;dbname=poiskstroek20150829',
    'username' => 'postgres',
    'password' => 'CepDosoufoowwib9',
    'charset' => 'utf8',
]);

//$posts = $db->createCommand('SELECT * FROM objects')->queryAll();

//echo '<pre>';
//print_r($posts);
//echo '</pre>';
?>

<style>
    .map-default-index {
        margin:0;
        padding: 0;
        background-color: #f4f4f4;
        font: bold 12px Trebuchet MS;
        padding-bottom: 25px;
        position: relative;

        -moz-user-select: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        user-select: none;
    }

    .map-default-index svg {
        background-color: #f4f4f4;
        fill: #d1d7dc;
        stroke: #60666b;
        margin-top: 55px;
    }

    .map-default-index .header {
        display: table;
        position: absolute;
        left: -10px;
        top: 10px;
    }

    .map-default-index .header_title {
        color: #303030;
        display: table-cell;
        vertical-align: middle;
        font-size: 30px;
        padding-left: 15px;
    }

    .map-default-index .button-back, .map-default-index .flag {
        display: table-cell;
        cursor: pointer;
    }

    .map-default-index .footer, .map-default-index .footer .inform {
        padding: 0;
        margin: 0;
        display: table;
        width: 100%;
    }

    .map-default-index .footer_left, .map-default-index .footer_right {
        display: table-cell;
        width: 50%;
    }

    .map-default-index .footer .title {
        font-size: 20px;
        padding: 0 30px;
        text-transform: uppercase;
        list-style-type: none;

        overflow: hidden;
        height: 150px;
    }

    .map-default-index .footer .title.full {
        overflow: auto;
        height: 100%;
    }

    .map-default-index .footer .statistic {
        border-bottom: 1px solid #d1d7dc;
        display: table;
        width: 100%;
    }

    .map-default-index .footer .statistic .left, .map-default-index .footer .statistic .right {
        display: table-cell;
        font-size: 16px;
        font-weight: normal;
        text-transform: none;
        line-height: 1.3;
        padding-top: 10px;
    }

    .map-default-index .footer .statistic .left {
        color: #303030;
        font-weight: normal;
    }

    .map-default-index .footer .statistic .right {
        color: #287fc3;
        font-weight: bold;
        text-align: right;
    }

    .map-default-index .footer .button_more {
        background-color: #3f95b9;
        color: #fff;
        cursor: pointer;
        display: table;
        font-size: 16px;
        font-weight: normal;
        margin: 15px auto 0;
        padding: 5px 40px;
        text-transform: uppercase;
    }

    .map-default-index g.county {
        //stroke: #fff;
    }

    .map-default-index path:hover, .map-default-index path.active {
        cursor: pointer;
        fill: #a1d568;
    }

    .map-default-index circle.city {
        fill: #3f92b9;
        stroke: #fff;
    }

    .map-default-index circle.city:hover {
        stroke: #3f92b9;
        fill: #fff;
    }

    #label {
        color: #fff;
        position: absolute;
        top: 0;
        left: 0;
        width: auto;
        height: auto;
        padding: 10px;
        background-color: #3f95b9;
        opacity: 0;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
        -webkit-box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.4);
        -moz-box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.4);
        box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.4);
        pointer-events: none;
    }

    g.city {
        display: none;
    }

    g .city.federal {
        display: block;
    }

    path.district {
        display: none;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/1.6.19/topojson.min.js"></script>

<script>

    var width = 1140, height = 500, active = d3.select(null);

    var projection = d3.geo.albers().rotate([-105, 0])
        .center([-10, 65])
        .parallels([52, 64])
        .scale(700)
        .translate([width / 2, height / 2]);

    var zoom = d3.behavior.zoom()
        .translate([0, 0])
        .scale(1)
        .scaleExtent([1, 8])
        .on("zoom", zoomed);

    var path = d3.geo.path().projection(projection);

    var t = projection.translate();
    var s = projection.scale();

    var canvas = d3.select(".map-default-index")
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        //.call(d3.behavior.zoom().on("zoom", redraw));


    render();

    var g_russia = canvas.append("g").attr("class", "russia");

    function redraw() {
        var tx = t[0] * d3.event.scale + d3.event.translate[0];
        var ty = t[1] * d3.event.scale + d3.event.translate[1];
        projection.translate([tx, ty]);

        projection.scale(s * d3.event.scale);

        // redraw the map
        g_russia.selectAll("path").attr("d", path);
    }

    function render_cities(data){

        var g_cities = g_russia.append("g").attr("class", "cities");


        var city = g_cities.selectAll("g.city")
            .data(data)
            .enter()
            .append("g")
            .attr("transform", function(d) { return "translate(" + projection([d.geometry.coordinates[0], d.geometry.coordinates[1]]) + ")"; })
            .attr("class", function(d){return (d.properties.PLACE == 'federal') ? "city federal" : "city"; });

        city.append("circle")
            .attr("r", 5)
            .attr("class",  function(d){ return "city city_" + d.properties.REG_ID; })
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

                d3.selectAll(".region").style("fill", function(c){return (c.properties.C_ID == d.properties.C_ID && c.properties.NAME != d.properties.NAME) ? "lime" : ""; })
            })
            .on("mouseout", function(d){
                d3.select("#label")
                    .transition()
                    .duration(300)
                    .style("opacity", 0);

                d3.selectAll(".region").style("fill", "#d1d7dc");
            });

        city.append("text")
            .attr("class", ".city_text")
            .attr("y", -8)
            .text(function(d) { return d.properties.NAME; })
            .style("fill", "#000")
            .style("display", function(d){return (d.properties.PLACE == 'federal') ? "block" : "none";});
    }

    function render_regions(data){
        var g_regions = g_russia.append("g").attr("class", "regions");

        g_regions.selectAll("path")
            .data(data)
            .enter()
            .append("path")
            .attr("d", path)
            .attr("class", "region")
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

                d3.selectAll(".region").style("fill", function(c){return (c.properties.C_ID == d.properties.C_ID && c.properties.NAME != d.properties.NAME) ? "lime" : ""; })
            })
            .on("mouseout", function(d){
                d3.select("#label")
                    .transition()
                    .duration(300)
                    .style("opacity", 0);

                d3.selectAll(".region").style("fill", "#d1d7dc");
            })
            .on("click", clicked);
    }

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

                d3.selectAll(".region").style("fill", function(c){return (c.properties.C_ID == d.properties.C_ID && c.properties.NAME != d.properties.NAME) ? "lime" : ""; })
            })
            .on("mouseout", function(d){
                d3.select("#label")
                    .transition()
                    .duration(300)
                    .style("opacity", 0);

                d3.selectAll(".region").style("fill", "#d1d7dc");
            })
    }

    d3.json("/json/russia_map.json", function(error, rus) {
        if (error) return console.error(error);

        console.log(rus);

        var data_r = topojson.feature(rus, rus.objects.region).features;
        var data_c = topojson.feature(rus, rus.objects.city).features;
        var data_d = topojson.feature(rus, rus.objects.district).features;


        render_regions(data_r);

        render_district(data_d);

        render_cities(data_c);
    });



    function clicked(d) {

        if (active.node() === this) return reset();
        active.classed("active", false);
        active = d3.select(this).classed("active", true);

        var bounds = path.bounds(d),
            dx = bounds[1][0] - bounds[0][0],
            dy = bounds[1][1] - bounds[0][1],
            x = (bounds[0][0] + bounds[1][0]) / 2,
            y = (bounds[0][1] + bounds[1][1]) / 2,
            scale = .9 / Math.max(dx / width, dy / height),
            translate = [width / 2 - scale * x, height / 2 - scale * y];

        g_russia.transition()
            .duration(750)
            .call(zoom.translate(translate).scale(scale).event);

        d3.selectAll("g.city").style("display", "block");
        d3.selectAll("g.federal text").style("display", "none");

        d3.select("g.regions").style("display", "none");
        d3.selectAll(".district_" + d.properties.REG_ID).style("display", "block");

        d3.selectAll("g.city circle").style("display", " none");
        d3.selectAll(".city_" + d.properties.REG_ID).style("display", "block");

        render_header_2(d);

    }

    function zoomed() {
        g_russia.style("stroke-width", 1.5 / d3.event.scale + "px");
        g_russia.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
    }

    function reset() {
        active.classed("active", false);
        active = d3.select(null);

        g_russia.transition()
            .duration(750)
            .call(zoom.translate([0, 0]).scale(1).event);

        d3.selectAll("g.city").style("display", "none");
        d3.selectAll("g.federal").style("display", "block");
        d3.selectAll("g.federal circle").style("display", "block");
        d3.selectAll("g.federal text").style("display", "block");

        d3.select("g.regions").style("display", "block");
        d3.selectAll(".district").style("display", "none");

        render_header_1();
    }

    function render() {
        render_header_1();
        render_title();
        render_footer();
    }

    function render_title() {
        var label = d3.select(".map-default-index").append("div").attr("id", "label");
    }

    function render_header_1() {
        d3.select(".header").remove();
        var header = d3.select(".map-default-index").append("div").attr("class", "header").style("left", "15px");
        var button_back = header.append("img").attr("class", "flag").attr("src", "/flag_0.png");
        var header_title = header.append("div").attr("class", "header_title").html("Российская Федерация");
    }

    function render_header_2(d) {
        d3.select(".header").remove();
        var header = d3.select(".map-default-index").append("div").attr("class", "header");
        var button_back = header.append("img").attr("class", "button-back").attr("src", "/button-back.png").on("click", reset);
        var header_title = header.append("div").attr("class", "header_title").html(d.properties.NAME);
    }

    function render_footer() {
        var footer = d3.select(".map-default-index").append("div").attr("class", "footer");
        var footer_inform = footer.append("div").attr("class", "inform");
        var footer_left = footer_inform.append("div").attr("class", "footer_left");
        var footer_right = footer_inform.append("div").attr("class", "footer_right");

        var ul_left = footer_left.append("ul").attr("class", "title").html("Текущее строительство");
        render_li(ul_left, "Показатель", "Значение");
        render_li(ul_left, "Показатель", "Значение");
        render_li(ul_left, "Показатель", "Значение");
        render_li(ul_left, "Показатель", "Значение");
        render_li(ul_left, "Показатель", "Значение");
        render_li(ul_left, "Показатель", "Значение");

        var ul_right = footer_right.append("ul").attr("class", "title").html("Стратегическое планирование");
        render_li(ul_right, "Показатель", "Значение");
        render_li(ul_right, "Показатель", "Значение");
        render_li(ul_right, "Показатель", "Значение");

        var button = footer.append("div").attr("class", "button_more").html("Полная информация").on("click", more);
    }

    function render_li(selector, left, right) {
        var li_left = selector.append("li").attr("class", "statistic");
        li_left.append("div").attr("class", "left").html(left);
        li_left.append("div").attr("class", "right").html(right);
    }

    function more() {
        var overflow =  d3.select(".title").style("overflow");
        if(overflow == 'hidden') {
            d3.selectAll(".title").classed('title full', true);
        } else {
            d3.selectAll(".title").classed({'title':true,'full':false});
        }
    }
</script>
