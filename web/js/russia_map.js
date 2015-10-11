$(function(){
	/* настройки */
	var width = 1000;
	var height = 500;
	window.level = 1;
	window.data = null;
	window.parent = null;
	
	/* переводим координаты в проекцию albers с уже заданных масштабированием, смещением, и т.д. (наиболее подходящая) */
	var projection = d3.geo.albers().rotate([-105, 0]).center([-10, 65]).parallels([52, 64]).scale(Math.max(width/1.4, height/1.4)).translate([width / 2, height / 2]);
	
	/* преобразуем географич. координаты в координаты понятные SVG */
	var path = d3.geo.path().projection(projection).pointRadius(5);
	
	/* поведения для приближения (zoom)) */
	var zoom = d3.behavior.zoom().scaleExtent([1, 60]).size([width, height]).on("zoom", scrollZoom);
	var zoom_reset = d3.behavior.zoom().translate([0, 0]).scale(1).scaleExtent([1, 8]).on("zoom", function(){
		group_russia.style("stroke-width", 1 / d3.event.scale + "px");
		group_russia.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
	});
	
	/* рендеринг svg элемента с настройками */
	var svg = d3.select("#map").append("svg")
        .attr("width", width)
        .attr("height", height)
		.call(zoom).on("dblclick.zoom", null);
	
	/* задний фон карты */
	svg.append('rect').attr('class', 'background').style("width", width).style("height", height).on("click", cancel);
	
	/* создаем группу Россия, которая будет в себе содержать дочерние группы */
	var group_russia = svg.append("g").attr("id", "russia");
	
	/* создаем группу для отрисовки регионов */
	var group_region = group_russia.append("g").attr("id", "region");
	
	/* создаем группу для отрисовки границ */
	var group_boundary = group_russia.append("g").attr("id", "boundary");
	
	/* создаем группу для отрисовки районов */
	var group_district = group_russia.append("g").attr("id", "district");
	
	/* создаем группу для отрисовки городов */
	var group_city = group_russia.append("g").attr("id", "city");
	
	/* tooltip */
	var tooltip = d3.select("#map").append("div")
		.attr("class", "tooltip")
		.style("display", "none");
		
	/* получаем json данные от сервера */
	d3.json("/json/map/russia_final.json", function (error, russia) {
		if (error) {
			console.log(error);
			return;
		}
		
		/* первая выгрузка данных по РФ */
		update(100);
		
		/* группируем данные по типам */
		var boundary = topojson.feature(russia, russia.objects.boundary).features;
		var region = topojson.feature(russia, russia.objects.region).features;
		var district = topojson.feature(russia, russia.objects.district).features;
		var city = topojson.feature(russia, russia.objects.city).features;
		
		/* рисуем все регионы */
		group_region.selectAll(".region").data(region).enter()
			.append("path")
			.attr("class", "region")
			.attr('d', path)
			.on("mousemove", showTooltip)
			.on("mouseout", hideTooltip)
			.on("click", select)
			.on("dblclick", ZoomIn);
			
		/* рисуем границы округов */
		group_boundary.selectAll(".boundary").data(boundary).enter()
			.append("path")
			.attr("class", "boundary")
			.attr("d", path)
			.attr("stroke-width", 1.2);
		
		/* рисуем все районы */
		group_district.selectAll(".district").data(district).enter()
			.append("path")
			.attr("class", function(d){return "district reg_id_" + d.properties.kld_subjcode})
			.attr('d', path)
			.on("mousemove", showTooltip)
			.on("mouseout", hideTooltip)
			.on("click", select);
			
		/* рисуем все города */
		var g_city = group_city.selectAll(".city")
			.data(city)
			.enter()
			.append("g")
			.attr("class", "city");
		
		g_city.append("path")
			.attr("class", function(d){return "reg_id_" + d.properties.kld_subjcode})
			.classed({"federal":(function(d){return(d.properties.place == "federal")?true:false}),"strategy":(function(d){return(d.properties.strategy)?true:false}),"adm":(function(d){return(d.properties.adm)?true:false})})
			.attr('d', path)
			.on("mousemove", showTooltip)
			.on("mouseout", hideTooltip)
			.on("click", select)
			.on("dblclick", function(d){
				if(d.properties.name == 'Сургут') window.open('http://surgut2030.usirf.ru', '_blank');
			});
			
		g_city.append("text")
			.attr("class", "text")
			.attr("transform", function(d) { return "translate(" + projection([d.geometry.coordinates[0], d.geometry.coordinates[1]]) + ")"; })
			.attr("y", -8)
			.text(function(d){return d.properties.name;})
			.classed({"federal":(function(d){return(d.properties.place == "federal")?true:false}),"strategy":(function(d){return(d.properties.strategy)?true:false})})
			.style("display", function(d){ return (d.properties.place == "federal" || d.properties.strategy) ? "block" : "none";});
		
		renderLegend();
	});
	
	/* приближения карты колесиком */
	function scrollZoom() {
		/* запрещаем зумминг для первого уровня */
		if(window.level == 1) return;
		
		/* получаем текущее смещение и масштаб */
		var t = d3.event.translate;
		var s = d3.event.scale;
		
		/* получаем наибольшее значение по ширине и по высоте (координаты смещения)*/
		t[0] = Math.max(Math.min(t[0], 0), width * (1 - s));
		t[1] = Math.max(Math.min(t[1], 0), height * (1 - s));
		
		/* задаем новое смещение */
		zoom.translate(t);
		
		/* применяем смещение */
		group_russia.style("stroke-width", 1/s).attr("transform", "translate(" + t + ")scale(" + s + ")");
		
		/* при масштабировании размер маркеров (городов) не изменяется */
		group_city.selectAll(".city path").attr('d', path.pointRadius(5/s)).style("stroke-width", 2/s);
	}
	
	/* приближение по ширине региона через двойной клик (zoom in) */
	function ZoomIn(d) {
		window.parent = window.data;
		group_russia.classed("fix", false);
		
		/* переход на следующий уровень */
		window.level = 2;
		
		/* скрываем все субъекты, и отображаем районы и города выбранного субъекта */
		d3.selectAll("path,.text").classed("active", false).style("display", "none");
		group_district.selectAll(".reg_id_" + d.properties.kld_subjcode).style("display", "block");
		group_city.selectAll(".reg_id_" + d.properties.kld_subjcode).style("display", "block");
		
		var bounds = path.bounds(d),
			/* получаем размеры, и смещение выбранного субъекта */
			dx = bounds[1][0] - bounds[0][0],
			dy = bounds[1][1] - bounds[0][1],
			x = (bounds[0][0] + bounds[1][0]) / 2,
			y = (bounds[0][1] + bounds[1][1]) / 2,
			/* получаем масштаб и смещение на основании полученных размеров субъекта */
			scale = .9 / Math.max(dx / width, dy / height),
			translate = [width / 2 - scale * x, height / 2 - scale * y];
		
		/* применяем полученный масштаб и смещение с продолжительностью 750 мс */
		group_russia.transition().duration(750).call(zoom.translate(translate).scale(scale).event);
	}
	
	/* сброс приближения (zoom out) */
	function ZoomOut(d) {
		/* вазврат на первый уровень */
		window.level = 1;
		
		/* отображаем все субъекты, и скрываем районы и города */
		d3.selectAll("path").classed("active", false).style("display", "none");
		group_region.selectAll(".region").style("display", "block");
		group_boundary.selectAll(".boundary").style("display", "block");
		group_city.selectAll(".federal").style("display", "block");
		group_city.selectAll(".strategy").style("display", "block");
		group_russia.classed("fix", false);
		
		/* обновляем информацию */
		update(100);
		
		/* масштабируем в исходное состояние с продолжительность 750 мс */
		group_russia.transition().duration(750).call(zoom_reset.translate([0, 0]).scale(1).event);

		/* при масштабировании размер маркеров (городов) не изменяется */
		group_city.selectAll(".city path").attr('d', path.pointRadius(5)).style("stroke-width", 2);
	}
	
	/* отобразить всплываещую подсказку */
	function showTooltip(d) {
		/* fix чтоб на выбранный элемент шел только один запрос */
		if(d.properties.kladr_code && window.data.kladr_code !== d.properties.kladr_code) {
			/* обновляем информацию */
			update(d.properties.kladr_code, "hover");
		}
		
		/* получаем координаты курсора мыши */
		var mouse = d3.mouse(svg.node());
				
		/* получаем имя элемента, если оно есть */
		if(!window.data.name) return;
				
		/* отображаем подсказку и именем */
		tooltip.style("display", "block")
			.style("left", (mouse[0] - 10) + "px")
			.style("top", (mouse[1] + 45) + "px")
			.html(window.data.name);
		
		if(group_russia.classed("fix") == false) {
			d3.select(".adm.reg_id_" + d.properties.kld_subjcode).style("display", "block");
		}
	}
	
	/* спрятать всплышающую подсказку */
	function hideTooltip() {
		tooltip.style("display", "none");
		
		/* обновляем информацию */
		update(window.parent.kladr_code);
		
		if(group_russia.classed("fix") == false) {
			d3.selectAll(".adm").style("display", "none");
		}
	}
	
	/* выбираем (фиксируем) какой нибудь элемент на карте */
	function select(d) {
		if(d3.select(this).classed("active")) {
			d3.selectAll("path").classed("active", false);
			
			group_russia.classed("fix", false);
		} else {
			d3.selectAll("path").classed("active", false);
			d3.select(this).classed("active", true);
			
			if(d.properties.cid) {
				d3.selectAll(".adm").style("display", "none");
				d3.select(".adm.reg_id_" + d.properties.kld_subjcode).style("display", "block");
			}
			
			group_russia.classed("fix", true);
			
			/* обновляем информацию */
			update(data.kladr_code, "click");
		}
	}
	
	/* отмена селекта */
	function cancel() {
		d3.selectAll("path").classed("active", false);
		group_russia.classed("fix", false);
		
		/* обновляем информацию */
		update(window.parent.kladr_code);
	}
	
	/* рендеринг (отрисовка) шапки */
	function renderHeader(data) {
		d3.select("#header").remove();
		
		if(window.level > 1) {
			var header = d3.select("#map").append("div").attr("id", "header");
			header.append("img").attr("class", "back").attr("src", "/button-back.png").on("click", ZoomOut);
			header.append("img").attr("class", "flag").attr("src", (data.img) ? data.img : "");
			header.append("p").attr("class", "header-title").html(data.name);
		} else {
			var header = d3.select("#map").append("div").attr("id", "header");
			header.append("img").attr("class", "flag").attr("src", (data.img) ? data.img : "");
			header.append("p").attr("class", "header-title").html(data.name);
		}
	}
	
	/* рендеринг (отрисовки) футера */
	function renderFooter(data) {
		if(d3.select("#footer")[0][0] == null)
		{
			var footer = d3.select("#map").append("div").attr("id", "footer");
			var footer_inform = footer.append("div").attr("class", "inform");
			var footer_left = footer_inform.append("div").attr("class", "footer-left");
			footer_left.append("ul").attr("class", "footer-title").html('<p><span class="left">Система:</span><span class="right">фактического контроля строительства</span></p>');
			var footer_right = footer_inform.append("div").attr("class", "footer-right");
			footer_right.append("ul").attr("class", "footer-title").html('<p><span class="left">Система:</span><span class="right">стратегического контроля управления</span>');
		
			footer.append("div").attr("class", "more").html("Полная информация").on("click", more);
		}
		
		var ul_left = d3.select(".footer-left ul.footer-title");
		ul_left.selectAll("li").remove();
		render_li(ul_left, "Строятся:", data.construct_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((data.kladr_code && data.kladr_code != 100) ? "&ObjectFilter[region]=" + data.kladr_code : ''));
		render_li(ul_left, "На сумму:", data.construct_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=1&ObjectFilter[stage]=3" + ((data.kladr_code && data.kladr_code != 100) ? "&ObjectFilter[region]=" + data.kladr_code : ''));
		render_li(ul_left, "Проектируются:", data.design_count, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((data.kladr_code && data.kladr_code != 100) ? "&ObjectFilter[region]=" + data.kladr_code : ''));
		render_li(ul_left, "На сумму:", data.design_sum, "http://poiskstroek.ru/objects?ObjectFilter[type]=2&ObjectFilter[stage]=3" + ((data.kladr_code && data.kladr_code != 100) ? "&ObjectFilter[region]=" + data.kladr_code : ''));
		render_li(ul_left, "Строят:", data.construct_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=2" + ((data.kladr_code && data.kladr_code != 100) ? "&OrganizationFilter[region]=" + data.kladr_code : ''));
		render_li(ul_left, "Проектируют:", data.design_companies, "http://poiskstroek.ru/companies?OrganizationFilter[type]=3" + ((data.kladr_code && data.kladr_code != 100) ? "&OrganizationFilter[region]=" + data.kladr_code : ''));
		
		var ul_right = d3.select(".footer-right ul.footer-title");
		ul_right.selectAll("li").remove();
		render_li(ul_right, "Показатель:", "");
		render_li(ul_right, "Показатель:", "");
		render_li(ul_right, "Показатель:", "");
		render_li(ul_right, "Показатель:", "");
	}
	
	/* генерация элементов списка атрибутов */
	function render_li(selector, caption, value, link) {
		var li_left = selector.append("li");

		li_left.append("div").attr("class", "left").html(caption);

		if(value && link) {
			li_left.append("div").attr("class", "right").html('<a target="_blank" href="' + link + '">' + value + '</a>');
		} else if(value){
			li_left.append("div").attr("class", "right").html(value);
		} else {
			li_left.append("div").attr("class", "right").html("информация закрыта");
		}
	}

	/* раскрытие списка атрибутов */
	function more() {
		var overflow =  d3.selectAll(".footer-title").style("overflow");

		if(overflow == 'hidden') {
			d3.selectAll(".footer-title").classed('title full', true);
			d3.select(".more").html("Скрыть информацию");
		} else {
			d3.selectAll(".footer-title").classed({'title':true,'full':false});
			d3.select(".more").html("Полная информация");
		}
	}
	
	/* обновление информации  */
	function update(kladr_code, event) {
		d3.json("/map/default/data/" + kladr_code, function(error, data) {
			if (error) {
				console.log(error);
				return;
			}
			
			if(kladr_code == 100) window.parent = data;
			window.data = data;
			
			if(group_russia.classed("fix") == false || event == "click") {
				renderHeader(data);
				renderFooter(data);
			}
		});
	}
	
	/* отрисовка (рендеринг) легенды */
	function renderLegend() {
		var color_domain = [1, 2, 3];
		var ext_color_domain = [0, 1, 2];
		var legend_labels = ["Города окружного значения", "Города со стратегией", "Города федерального значения"];          
		var color = d3.scale.threshold()
			.domain(color_domain)
			.range(["#3f92b9", "red", "green"]);
		
		var legend = svg.selectAll("g.legend")
			.data(ext_color_domain)
			.enter().append("g")
			.attr("class", "legend");
			
		var ls_w = 1, ls_h = 20;

		legend.append("circle")
			.attr("r", 5)
			.attr("cx", 35)
			.attr("cy", function(d, i){ return height - (i*ls_h) - 2*ls_h + 12;})
			.attr("width", ls_w)
			.attr("height", ls_h)
			.attr("stroke", "navy")
			.attr("stroke-width", 2)
			.style("fill", function(d, i) { return color(d); })
			.style("opacity", 0.8);

		legend.append("text")
			.attr("x", 50)
			.attr("y", function(d, i){ return height - (i*ls_h) - ls_h - 4;})
			.text(function(d, i){ return legend_labels[i]; });
	}
	
	d3.selection.prototype.dblTap = function(callback) {
		var last = 0;
		return this.each(function() {
			d3.select(this).on("touchstart", function(e) {
				if ((d3.event.timeStamp - last) < 500) {
					//Touched element
					return callback(e);
				}
				last = d3.event.timeStamp;
			});
		});
	}
	
	d3.selectAll(".region").dblTap(function() {
		alert(1);
		//ZoomIn(d);
    });
});