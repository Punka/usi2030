var map  = {
	width: null,
	height: null,
	canvas: null,
	active: null,
	projection: null,
	path: null,
	tmp_data: null,
	datas: null,
	fixed: null,
	g_russia: null,
	g_districts: null,
	g_cities: null,
	zoom: null,
	
	initSVG: function(width, height) {
		map.active = d3.select(null);
		map.width = width;
		map.height = height;
		
		map.canvas = d3.select(".map-default-index").append("svg").attr("width", map.width).attr("height", map.height);
		
		// Behavior by zooming map
		map.zoom = d3.behavior.zoom().translate([0, 0]).scale(1).scaleExtent([1, 8]).on("zoom", function(){
			map.g_russia.style("stroke-width", 1 / d3.event.scale + "px");
			map.g_russia.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
		});
		
		map.canvas.append("rect").attr("class", "background").attr("width", map.width).attr("height", map.height).attr("stroke-opacity", "0").attr("fill-opacity", "0").on("click", map.cancel);
		
		map.g_russia = map.canvas.append("g").attr("class", "map");
		
		map.projection = d3.geo.albers().rotate([-105, 0]).center([-10, 65]).parallels([52, 64]).scale(700).translate([map.width / 2, map.height / 2]);
		
		map.path = d3.geo.path().projection(map.projection);
		
		d3.select(".map-default-index").append("div").attr("id", "label");
	},
	
	renderInformation: function(data, level) {
		map.renderHeader(data, level);
		map.renderFooter(data);
	},
	
	label: function(name, d3) {
		if(name) {
			d3.select("#label").transition().duration(300).style("opacity", 1);
			d3.select("#label").style("left", (d3.event.layerX - 10) + 'px').style("top", (d3.event.layerY - 45) + 'px').text(name);
		} else {
			d3.select("#label").transition().duration(300).style("opacity", 0);
		}
	},
	
	renderHeader: function(json, level) {
		d3.select(".header").remove();
		var header = d3.select(".map-default-index").append("div").attr("class", "header").style("left", "15px");
		if(level > 1) {
			header.append("img").attr("class", "button-back").attr("src", "/button-back.png").on("click", map.reset);
			header.append("img").attr("class", "gerb").attr("src", (json.img) ? json.img : "");
		} else {
			header.append("img").attr("class", "flag").attr("src", (json.img) ? json.img : "");
		} 
		
		header.append("div").attr("class", "header_title").html(json.name);
	},
	
	renderFooter: function(json) {
		if(d3.select(".footer")[0][0] != null) {
			var ul_left = d3.select(".footer_left ul.title");
			ul_left.selectAll("li").remove();
			map.render_li(ul_left, "Строятся:", json.construct_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code && json.kladr_code != 100) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
			map.render_li(ul_left, "На сумму:", json.construct_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code && json.kladr_code != 100) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
			map.render_li(ul_left, "Проектируются:", json.design_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code && json.kladr_code != 100) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
			map.render_li(ul_left, "На сумму:", json.design_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code && json.kladr_code != 100) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
			map.render_li(ul_left, "Строят:", json.construct_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=2" + ((json.kladr_code && json.kladr_code != 100) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));
			map.render_li(ul_left, "Проектируют:", json.design_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=3" + ((json.kladr_code && json.kladr_code != 100) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));

			var ul_right = d3.select(".footer_right .title");
			ul_right.selectAll("li").remove();
			map.render_li(ul_right, "Показатель:", "");
			map.render_li(ul_right, "Показатель:", "");
			map.render_li(ul_right, "Показатель:", "");
			map.render_li(ul_right, "Показатель:", "");
		} else {
			d3.select(".footer").remove();
			var footer = d3.select(".map-default-index").append("div").attr("class", "footer");
			var footer_inform = footer.append("div").attr("class", "inform");
			var footer_left = footer_inform.append("div").attr("class", "footer_left");
			var footer_right = footer_inform.append("div").attr("class", "footer_right");

			if(json){
				var ul_left = footer_left.append("ul").attr("class", "title").html('<p><span class="left">Система:</span><span class="right">фактического контроля строительства</span></p>');
				map.render_li(ul_left, "Строятся:", json.construct_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
				map.render_li(ul_left, "На сумму:", json.construct_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
				map.render_li(ul_left, "Проектируются:", json.design_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
				map.render_li(ul_left, "На сумму:", json.design_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((json.kladr_code) ? "&ObjectFilter[region]=" + json.kladr_code : ''));
				map.render_li(ul_left, "Строят:", json.construct_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=2" + ((json.kladr_code) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));
				map.render_li(ul_left, "Проектируют:", json.design_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=3" + ((json.kladr_code) ? "&OrganizationFilter[region]=" + json.kladr_code : ''));

				var ul_right = footer_right.append("ul").attr("class", "title").html('<p><span class="left">Система:</span><span class="right">стратегического контроля управления</span>');
				map.render_li(ul_right, "Показатель:", "");
				map.render_li(ul_right, "Показатель:", "");
				map.render_li(ul_right, "Показатель:", "");
				map.render_li(ul_right, "Показатель:", "");
			}

			footer.append("div").attr("class", "button_more").html("Полная информация").on("click", this.more);
		}
	},
	
	// Render li
	render_li: function(selector, caption, value, link) {
		var li_left = selector.append("li").attr("class", "statistic");

		li_left.append("div").attr("class", "left").html(caption);

		if(value && link) {
			li_left.append("div").attr("class", "right").html('<a target="_blank" href="' + link + '">' + value + '</a>');
		} else if(value){
			li_left.append("div").attr("class", "right").html(value);
		} else {
			li_left.append("div").attr("class", "right").html("информация закрыта");
		}
	},

	// More information
	more: function() {
		var overflow =  d3.select(".title").style("overflow");

		if(overflow == 'hidden') {
			d3.selectAll(".title").classed('title full', true);
		} else {
			d3.selectAll(".title").classed({'title':true,'full':false});
		}
	},
	
	// Rendering boundary
	renderBoundary: function(boundary) {
		var g_boundary = d3.select(".map").append("g").attr("class", "boundary");
		g_boundary.selectAll("path").data(boundary).enter().append("path").attr("d", map.path).attr("stroke-width", 1.2).attr("stroke","#a1d568");
	},
	
	// Rendering regions
	renderRegion: function(data) {
		var g_regions = map.g_russia.append("g").attr("class", "regions");
		g_regions.selectAll("path")
			.data(data)
			.enter()
			.append("path")
			.attr("d", map.path)
			.attr("class", "region")
			.on("mousemove", function(d) {
				var kladr_code = d.properties.kladr_code;
				map.label(d.properties.name, d3);
				
				if(!d3.select(".regions").classed("fixed"))
				{
					d3.selectAll(".adm").style("display","none");
					d3.select(".adm.region_" + d.properties.kld_subjcode).style("display","block");
					map.renderInformation(map.datas[kladr_code], 1);
				}
					
			})
			.on("mouseout", function(d) {
				map.label(0, d3);
				if(!d3.select(".regions").classed("fixed"))
				{
					//if(d3.select(".regions").style("display") != 'none') d3.select(".adm.region_" + d.properties.kld_subjcode).style("display","none");
					map.renderInformation(map.datas[100], 1);
				}
			})
			.on("dblclick", this.zooming)
			.on("click", function(d) {
				if(d3.select(this).classed("active")){
					d3.selectAll("path,circle").classed("active", false);
					//d3.select(".adm").style("display","none");
					d3.select(this).classed("active", false);
					d3.select(".regions").classed("fixed", false);
					map.renderInformation(map.datas[100], 1);
				}
				else
				{
					d3.selectAll("path,circle").classed("active", false);
					d3.selectAll(".adm").style("display","none");
					d3.select(".adm.region_" + d.properties.kld_subjcode).style("display","block");
					d3.select(this).classed("active", true);
					d3.select(".regions").classed("fixed", true);
					var kladr_code = d.properties.kladr_code;
					map.renderInformation(map.datas[kladr_code], 1);
				}
			});
	},
	
	// Rendering districts
	renderDistrict: function(data){
		map.g_districts = map.g_russia.append("g").attr("class", "districts");

		map.g_districts.selectAll("path")
			.data(data)
			.enter()
			.append("path")
			.attr("d", map.path)
			.attr("class", function(d){ return "district region_" + d.properties.kld_subjcode; })
			.on("mousemove", function(d){
				var kladr_code = d.properties.kladr_code;
				map.label(d.properties.name, d3);
				if(!d3.select(".districts").classed("fixed")) {
					map.renderInformation(map.datas[kladr_code], 2);
				}
			})
			.on("mouseout", function(d){
				map.label(0, d3);
				if(!d3.select(".districts").classed("fixed")) {
					map.renderInformation(map.tmp_data, 2);
				}
			})
			.on("click", function(d){
				if(d3.select(this).classed("active")) {
					d3.selectAll("path,circle").classed("active", false);
					d3.select(".districts").classed("fixed", false);
					map.renderInformation(map.tmp_data, 2);
				} else {
					d3.selectAll("path,circle").classed("active", false);
					d3.select(this).classed("active", true);
					d3.select(".districts").classed("fixed", true);
					var kladr_code = d.properties.kladr_code;
					map.renderInformation(map.datas[kladr_code], 2);
				}
			});
	},

	// Rendering cities
	renderCity: function(data){
		
		map.g_cities = map.g_russia.append("g").attr("class", "cities");

		var city = map.g_cities.selectAll("g.city")
			.data(data)
			.enter()
			.append("g")
			.attr("transform", function(d) { return "translate(" + map.projection([d.geometry.coordinates[0], d.geometry.coordinates[1]]) + ")"; })
			.attr("class", function(d){return "region_" + d.properties.kld_subjcode;})
			.classed({"city":true,"federal":( function(d){ return (d.properties.place == 'federal' || d.properties.name == 'Сургут') ? true : false}),"adm":(function(d){return (d.properties.adm) ? true : false})});
			
		city.append("circle")
			.on("mousemove", function(d){	
				d3.select(".adm.region_" + d.properties.kld_subjcode).style("display","block");
				var kladr_code = d.properties.kladr_code;
				map.label(d.properties.name, d3);
				if(d3.select(".regions").style("display") != 'none'){
					if(!d3.select(".regions").classed("fixed")) {
						map.renderInformation(map.datas[kladr_code], 1);
					}
				} else {
					if(!d3.select(".districts").classed("fixed")) {
						map.renderInformation(map.datas[kladr_code], 2);
					}
				}
			})
			.on("mouseout", function(d){
				d3.select(".adm.region_" + d.properties.kld_subjcode).style("display","block");
				console.log(d);
				map.label(0, d3);
				if(d3.select(".regions").style("display") != 'none'){
					if(!d3.select(".regions").classed("fixed")) {
						map.renderInformation(map.tmp_data, 1);
					}
				} else {
					if(!d3.select(".districts").classed("fixed")) {
						map.renderInformation(map.tmp_data, 2);
					}
				}
			})
			.on("click", function(d){
				var kladr_code = d.properties.kladr_code;
				var level = (d3.select(".regions").style("display") != 'none') ? 1 : 2;
				var selector = (d3.select(".regions").style("display") != 'none') ? ".regions" : ".districts";
				if(d3.select(this).classed("active")) {
					d3.select(selector).classed("fixed", false);
					d3.selectAll("path,circle").classed("active", false);
					map.renderInformation(map.tmp_data, level);
				} else {
					d3.select(selector).classed("fixed", true);
					d3.selectAll("path,circle").classed("active", false);
					d3.select(this).classed("active", true);
					map.renderInformation(map.datas[kladr_code], level);
				}
			})
			.on("dblclick", function(d){
				if(d.properties.name == 'Сургут') window.open('http://surgut2030.usirf.ru', '_blank');
			})
			.style("fill", function(d){ return (d.properties.name == 'Сургут') ? "red" : ""; });

		city.append("text")
			.attr("class", ".city_text")
			.attr("y", -8)
			.text(function(d) { return d.properties.name; })
			.style("fill", "#000")
			.style("display", function(d){return (d.properties.place == 'federal' || d.properties.name == 'Сургут') ? "block" : "none";});
	},
	
	renderMap: function(path) {
		d3.json(path, function(error, rus){
			if (error) return console.error(error);
			
			var boundary = topojson.feature(rus, rus.objects.boundary).features;
			var data_r = topojson.feature(rus, rus.objects.region).features;
			var data_c = topojson.feature(rus, rus.objects.city).features;
			var data_d = topojson.feature(rus, rus.objects.district).features;
			
			map.renderRegion(data_r);
			map.renderDistrict(data_d);
			map.renderCity(data_c);
			map.renderBoundary(boundary);
			
			d3.json("/map/default/data", function(error, data){
				if (error) return console.error(error);
				
				map.tmp_data = data[100];
				map.datas = data;
				
				map.renderInformation(map.datas[100], 1);
			});
		});
	},
	
	// Cancel select path
	cancel: function() {
		if(d3.select(".regions").style("display") != 'none'){
			d3.select(".regions").classed("fixed", false);
			map.renderInformation(map.tmp_data, 1);
		}else{
			d3.select(".districts").classed("fixed", false);
			map.renderInformation(map.tmp_data, 2);
		}

		d3.selectAll("path,circle").classed("active", false);
	},

	// Zooming region
	zooming: function(d) {
		map.active.classed("active", false);
		map.active = d3.select(this).classed("active", true);
		
		var kladr_code = d.properties.kladr_code;
		map.tmp_data = map.datas[kladr_code];

		var bounds = map.path.bounds(d),
			dx = bounds[1][0] - bounds[0][0],
			dy = bounds[1][1] - bounds[0][1],
			x = (bounds[0][0] + bounds[1][0]) / 2,
			y = (bounds[0][1] + bounds[1][1]) / 2,
			scale = .9 / Math.max(dx / map.width, dy / map.height),
			translate = [map.width / 2 - scale * x, map.height / 2 - scale * y];
		
		map.g_russia.transition().duration(750).call(map.zoom.translate(translate).scale(scale).event);

		d3.selectAll(".regions, .boundary, .city, text").style("display", "none");

		d3.selectAll(".region_" + d.properties.kld_subjcode).style("display", "block");

		d3.selectAll("circle").style("r", function(d){ return (6/scale); }).style("stroke-width", function(d) {return (7/scale)/3;});

		map.renderInformation(map.tmp_data, 2);
	},

	// Reset zooming region
	reset: function() {
		map.active.classed("active", false);
		map.active = d3.select(null);

		map.tmp_data = map.datas[100];

		map.g_russia.transition().duration(750).call(map.zoom.translate([0, 0]).scale(1).event);

		d3.selectAll(".regions, .boundary, .city, text").style("display", "block");

		d3.selectAll(".district").style("display", "none");

		d3.selectAll(".city, text").style("display", function(d){return (d.properties.place == 'federal' || d.properties.name == 'Сургут') ? "block" : "none";});

		d3.selectAll("circle").style("r", 5).style("stroke-width", function(d) {return 7/3;});

		map.renderInformation(map.datas[100], 1);
	}
};

map.initSVG(980, 500);
map.renderMap("/json/map/russia_final.json");