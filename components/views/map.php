<?php
use app\assets\MapAsset;
MapAsset::register($this);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/1.6.19/topojson.min.js"></script>

<script>

    var width = 960, height = 500;

    var canvas = d3.select(".map-default-index")
        .append("svg")
        .attr("width", width)
        .attr("height", height);

    d3.json("http://punka.ru/json/russia.json", function(error, rus) {
        if (error) return console.error(error);

        var region = topojson.feature(rus, rus.objects.region).features;
        var district = topojson.feature(rus, rus.objects.district).features;
        var city = topojson.feature(rus, rus.objects.city).features;
        var krim = topojson.feature(rus, rus.objects.krim).features;
        console.log(district);
        var projection = d3.geo.albers().rotate([-105, 0])
            .center([-10, 65])
            .parallels([52, 64])
            .scale(700)
            .translate([width / 2, height / 2]);
        var path = d3.geo.path().projection(projection);

        var group1 = canvas.append("g").attr("class", "region");

        var region = group1.selectAll("path")
            .data(region)
            .enter()
            .append("path")
            .attr("d", path)
            .attr("fill", "steelblue")
            .attr("stroke", "#000");

        var group2 = canvas.append("g").attr("class", "district");

        var district = group2.selectAll("path")
            .data(district)
            .enter()
            .append("path")
            .attr("d", path)
            .attr("fill", " green")
            .attr("stroke", "red")
            .attr("name", function(d){return d.name;})
            .attr("okrug", function(d){ return d.properties.ADM3_NAME;})
            .attr("district", function(d){ return d.properties.NAME;})
            .attr("region", function(d){ return d.properties.ADM4_NAME;});

        var group3 = canvas.append("g").attr("class", "krim");

        var krim = group3.selectAll("path")
            .data(krim)
            .enter()
            .append("path")
            .attr("d", path)
            .attr("fill", " green")
            .attr("stroke", "red")
           // .attr("name", function(d){return d.name;});

        var group4 = canvas.selectAll("g").data(city).enter().append("g").attr("class", "city");

        var city = group4.append("path")
            .attr("d", path)
            .attr("fill", " red")
            .attr("stroke", "red")
            .attr("city", function(d){ return d.properties.NAME;})
            .attr("district", function(d){ return d.properties.A_DSTRCT;})
            .attr("region", function(d){ return d.properties.A_RGN;});

        group4.append("text")
            .attr("x", function(d){ return path.centroid(d)[0]; })
            .attr("y", function(d){ return path.centroid(d)[1]; })
            .attr("text-anchor", "moddle")
            .text(function(d){ return d.properties.NAME; });

        /*canvas.append("path")
            .datum(region)
            .attr("d", path)
            .attr("fill", "steelblue")
            .attr("stroke", "green");
*/
        /*canvas.append("path")
            .datum(topojson.feature(rus, rus.objects.city))
            .attr("d", d3.geo.path().projection(d3.geo.albers().rotate([-105, 0]).center([-10, 65]).parallels([52, 64]).scale(700).translate([width/2, height/2])))
            .attr("class", "region")
            .attr("fill", "steelblue")
            .attr("fill-stroke", 1);*/
    });

    /*d3.json("http://punka.ru/json/district.geojson", function(data){
        console.log(data);
        var group = canvas.selectAll("g").data(data.features).enter().append("g");
        var projection = d3.geo.albers().rotate([-105, 0])
            .center([-10, 65])
            .parallels([52, 64])
            .scale(700)
            .translate([width / 2, height / 2]);
        var path = d3.geo.path().projection(projection);
        var areas = group.append("path")
            .attr("d", path)
            .attr("class", "area")
            .attr("fill", "steelblue");

    });*/
</script>
