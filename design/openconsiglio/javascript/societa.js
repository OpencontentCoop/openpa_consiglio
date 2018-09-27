$.ftcSocieta = {
  render: function(settings){
    Highcharts.setOptions(Highcharts.theme);
    var colors = Highcharts.getOptions().colors;
    
    var renderInfo = function(container, info, attributes){
      var $infoContainer = $( '#'+container );
      var $table = $( '<table class="table" />' );    
      $.each( info, function(index){
        if (this.value !==null && $.inArray(index, attributes) > -1 ) {
          var $row = $( '<tr />' ).addClass(index);
          $( '<th>').appendTo( $row ).html(this.label);
          $( '<td>'+this.value.join(", ")+'</td>').appendTo( $row );
          $row.appendTo( $table );
        }        
      });
      $table.appendTo( $infoContainer );
    };
    
    var renderMap = function(container, geoJson){
      var tiles = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 18,attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'});
      var map = L.map(container).addLayer(tiles)
      map.scrollWheelZoom.disable();		
      if (geoJson.features) {
        var markers = L.markerClusterGroup();
        var geoJsonLayer = L.geoJson(geoJson);
        markers.addLayer(geoJsonLayer);
        map.addLayer(markers);
        map.fitBounds(markers.getBounds());        
        markers.on('click', function (a) {      
          var popup = new L.Popup({maxHeight:360});
          popup.setLatLng(a.layer.getLatLng());
          var content = '<h5>'+a.layer.feature.properties.name+'</h5>';
          if (a.layer.feature.properties.indirizzo_completo != null) {
            content += '<p><i class="fa fa-map-marker"></i> '+a.layer.feature.properties.indirizzo_completo+'</p>';
          }
          if (a.layer.feature.properties.telefono != null) {
            content += '<p><i class="fa fa-phone-square"></i> '+a.layer.feature.properties.telefono+'</p>';
          }
          if (a.layer.feature.properties.e_mail != null) {
            content += '<p><i class="fa fa-envelope"></i> '+a.layer.feature.properties.e_mail+'</p>';
          }
          popup.setContent(content);
          map.openPopup(popup);
        });        
      }
      var mapContainer = $('#'+container);
      var listContainer = $('#'+container+'_list');
      if (listContainer.length > 0 && geoJson.features && geoJson.features.length > 2) {
        var table = $('<table class="table table-striped"></table>');
        $.each(geoJson.features, function(){
            var row = $('<tr></tr>');
            var content = '<p><b>'+this.properties.name+'</b></p>';
            if (this.properties.indirizzo_completo != null) {
              content += '<p><i class="fa fa-map-marker"></i> '+this.properties.indirizzo_completo+'</p>';
            }
            if (this.properties.telefono != null) {
              content += '<p><i class="fa fa-phone-square"></i> '+this.properties.telefono+'</p>';
            }
            if (this.properties.e_mail != null) {
              content += '<p><i class="fa fa-envelope"></i> '+this.properties.e_mail+'</p>';
            }
            var column = $('<td>'+content+'</td>');
            row.append(column);
            table.append(row);
        });
        listContainer.append(table);
        listContainer.css('max-height', mapContainer.height());
        listContainer.css('overflow-y', 'auto');
        listContainer.hide();
        var panelTitle = listContainer.parents('.panel').find('.panel-heading');
        var toggleListMap = $('<span class="pull-right label label-info"></span>');
        toggleListMap.css('cursor','pointer');
        var toggleListTitle = 'Visualizza lista';
        var toggleMapTitle = 'Visualizza mappa';
        toggleListMap.text(toggleListTitle);
        toggleListMap.on('click', function(){
            if ($(this).text() == toggleListTitle) {
                mapContainer.hide();
                listContainer.show();
                $(this).text(toggleMapTitle)
            }else{
                mapContainer.show();
                listContainer.hide();
                $(this).text(toggleListTitle)
            }
        });
        panelTitle.prepend(toggleListMap);
      }
    };
    
    var renderBilancio = function(container,bilancio){
      var series = [
          {
              name: bilancio.data.patrimonio.chart_label,
              type: bilancio.data.patrimonio.chart_type,
              color: colors[1],
              zIndex: 0,
              data: bilancio.data.patrimonio.data
          },
          {
              name: bilancio.data.utile.chart_label,
              type: bilancio.data.utile.chart_type,
              color: colors[7],
              zIndex: 2,
              data: bilancio.data.utile.data
          }
      ];
      
      if (bilancio.data.fatturato) {
        series.push({
          name: bilancio.data.fatturato.chart_label,
          type: bilancio.data.fatturato.chart_type,
          color: colors[0],
          zIndex: 1,
          data: bilancio.data.fatturato.data
        });
      }

      return new Highcharts.Chart({
        chart: {renderTo: container, zoomType: 'xy'},
        title: {text: bilancio.data.title},
        legend: {
            labelFormatter: function () {
                return this.name;
            }
        },
        xAxis: {categories: bilancio.data.categories},
        yAxis: {
            labels: {
                formatter: function () {
                    var value = this.value * bilancio.data.lfactor;
                    if (value == value.toFixed(2)) return value; else return value.toFixed(2);
                },
                style: {color: '#000000'}
            },
            title: {text: bilancio.data.llabel, style: {color: '#000000'}}
        },
        tooltip: {
            enabled: true,
            formatter: function () {
                var lfactor = bilancio.data.lfactor;
                var rfactor = bilancio.data.rfactor;
                var suffix;
                nStr = this.y + '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + '.' + '$2');
                }
                var retval = x1 + x2;
                var str = this.series.name;
                if (this.series.name == bilancio.data.fatturato.chart_label) {
                    retval = this.y * bilancio.data.lfactor;
                    if (bilancio.data.lfactor == 1) suffix = " mila euro";
                    else suffix = " mln di euro";
                } else {
                    retval = this.y * bilancio.data.rfactor;
                    if (bilancio.data.rfactor == 1) suffix = " mila euro";
                    else suffix = " mln di euro";
                }
                if (retval != retval.toFixed(2)) retval = retval.toFixed(2);
                return '<b>' + this.series.name + ' ' + this.x + '</b><br/>' + retval + suffix;
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: false,
                    formatter: function () {
                        nStr = this.y + '';
                        x = nStr.split('.');
                        x1 = x[0];
                        x2 = x.length > 1 ? '.' + x[1] : '';
                        var rgx = /(\d+)(\d{3})/;
                        while (rgx.test(x1)) {
                            x1 = x1.replace(rgx, '$1' + '.' + '$2');
                        }
                        return x1 + x2;
                    }
                },
                enableMouseTracking: true
            }
        },
        labels: {
            items: [{
              html: '',
              style: { left: '0', top: '0', color: '#bbb', 'font-size': '10px' }
          }]
        },
        series: series
      });
      
    };
    
    var renderGaranzie = function(container,data){      
      var series = [
          {
              name: data.chart_label,
              type: data.chart_type,
              color: colors[0],              
              data: data.data
          }
      ];      
      return new Highcharts.Chart({
        chart: {renderTo: container, zoomType: 'xy'},
        title: {text: data.title, x: -20},
        legend: {
            labelFormatter: function () {
                return this.name;
            }
        },
        xAxis: {categories: data.categories},
        yAxis: {
            labels: {
                formatter: function () {
                    return this.value;
                },
                style: {color: '#000000'}
            },
            title: {text: data.title, style: {color: '#000000'}}
        },
        tooltip: {
          enabled: true,
          formatter: function() {
                nStr = this.y + '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                  x1 = x1.replace(rgx, '$1' + '.' + '$2');
                }
            return '<b>'+ this.series.name +'</b><br/>'+
              this.x +': '+ (x1 + x2);
          }
        },
        plotOptions: {
          line: {
            dataLabels: {
              enabled: false,
              formatter: function() {
                nStr = this.y + '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                  x1 = x1.replace(rgx, '$1' + '.' + '$2');
                }
                return x1 + x2;
              }
            },
            enableMouseTracking: true
          }
        },
        labels: {
            items: [{
              html: '',
              style: { left: '0', top: '0', color: '#bbb', 'font-size': '10px' }
          }]
        },
        series: series
      });
      
    };
    
    var renderRaccoltaEPrestiti = function(container,data){
      
      var series = [];
      if (data.diretta.has_data != 0) {
        series.push({
          name: data.diretta.chart_label,
          color: '#CDD64F',
          data: data.diretta.data
        });
      }
      
      if (data.indiretta.has_data != 0) {
        series.push({
          name: data.indiretta.chart_label,
          color: '#EBFFA8',
          data: data.indiretta.data
        });
      }
      
      if (data.prestiti.has_data != 0) {
        series.push({
          name: data.prestiti.chart_label,
          color: colors[2],
          type: data.prestiti.chart_type,
          data: data.prestiti.data
        });
      }
      
      return new Highcharts.Chart({
        chart: {renderTo: container,type: 'column'},
        title: {text: data.title, x: -60},
        xAxis: {categories: data.categories},
        yAxis: {
          min: 0,
          title: { // Primary Axis
            text: data.llabel,
            style: {color: '#ffffff'}
          },
          stackLabels: {
            enabled: false,
            style: {
              fontWeight: 'bold'
            },
            labels: {
              formatter: function () {
                value = this.value * data.lfactor;
                if (value == value.toFixed(2))
                  return value;
                else
                  return value.toFixed(2);
              },
              style: {
                color: '#ffffff'
              }
            }
          },
          labels: {
            formatter: function () {
              value = this.value * data.lfactor;
              if (value == value.toFixed(2))
                return value;
              else return value.toFixed(2);
            },
            style: {
              color: '#ffffff'
            }
          }
        },
        legend: {
          align: 'right',
          x: -100,
          verticalAlign: 'top',
          y: 20,
          floating: true,
          backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
          borderColor: '#CCC',
          borderWidth: 1,
          shadow: false
        },
        tooltip: {
          enabled: true,
          formatter: function () {
            var lfactor = 1 * data.lfactor;
            var rfactor = 1 * data.rfactor;
            var suffix;
            nStr = this.y + '';
            x = nStr.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
              x1 = x1.replace(rgx, '$1' + '.' + '$2');
            }
            var retval = x1 + x2;
            var str = this.series.name;
            if (this.series.name == data.prestiti.chart_label) {
              retval = this.y * rfactor;
              if (rfactor == 1) {
                suffix = " mila €";
              } else {
                suffix = " mln di €";
              }
            } else {
              retval = this.y * lfactor;
              if (lfactor == 1) {
                suffix = " mila €";
              } else {
                suffix = " mln di €";
              }
            }
            if (retval != retval.toFixed(2)) {
              retval = retval.toFixed(2);
            }
            totnStr = this.point.stackTotal + '';
            z = totnStr.split('.');
            z1 = z[0];
            z2 = z.length > 1 ? '.' + z[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(z1)) {
              z1 = z1.replace(rgx, '$1' + '.' + '$2');
            }
            var total = z1 + z2;
            total = this.point.stackTotal * lfactor;
            if (total != total.toFixed(2)) {
              total = total.toFixed(2);
            }
            if (this.series.name == data.prestiti.chart_label) {
              return '<b>' + this.series.name + ' ' + this.x + '</b><br/>' + retval + suffix;
            }
            var oth = 'Raccolta Diretta';
            if (this.series.name == 'Raccolta Diretta') {
              oth = 'Raccolta Indiretta';
            }
            var othval = (total * 1 - retval * 1) == (total * 1 - retval * 1).toFixed(2) ? (total * 1 - retval * 1) : (total * 1 - retval * 1).toFixed(2);
            return '<b>' + 'Anno' + ' ' + this.x + '</b><br/>' + this.series.name + ': ' + retval + suffix +
                '<br>' + oth + ': ' + othval + suffix +
                '<br/><b>Totale: ' + total + suffix + '</b>';
          }
        },
        plotOptions: {
          column: {
            stacking: 'normal',
            dataLabels: {
              enabled: false
            }
          }
        },
        labels: {
          items: [{
            html: '',
            style: {
              left: '100px',
              top: '240px',
              color: 'white'
            }
          }]
        },
        series: series
      });
    };
    
    var renderBarChart = function(container, data, color){
      new Highcharts.Chart({
          chart: {
              renderTo: container,
              zoomType: 'xy'
          },
          title: {
              text: ''
          },
          legend: {
              labelFormatter: function() { return this.name; }
          },
          xAxis: {
              categories: data.categories
          },
          yAxis: {
              labels: {
                  formatter: function() { return this.value; },
                  style: { color: color }
              },
              title: {
                  text: data.llabel,
                  style: { color: color}
              }
          },
          tooltip: {
              enabled: true,
              formatter: function() {
                  nStr = this.y + '';
                  x = nStr.split('.');
                  x1 = x[0];
                  x2 = x.length > 1 ? '.' + x[1] : '';
                  var rgx = /(\d+)(\d{3})/;
                  while (rgx.test(x1)) {
                      x1 = x1.replace(rgx, '$1' + '.' + '$2');
                  }
                  return '<b>'+ this.series.name + '</b><br/>' + this.x +': '+ (x1 + x2);
              }
          },
          plotOptions: {
              line: {
                  dataLabels: {
                      enabled: false,
                      formatter: function() {
                          nStr = this.y + '';
                          x = nStr.split('.');
                          x1 = x[0];
                          x2 = x.length > 1 ? '.' + x[1] : '';
                          var rgx = /(\d+)(\d{3})/;
                          while (rgx.test(x1)) {
                              x1 = x1.replace(rgx, '$1' + '.' + '$2');
                          }
                          return x1 + x2;
                      }
                  },
                  enableMouseTracking: true
              }
          },
          labels: {
              items: [{
                  html: '',
                  style: { left: '0', top: '0', color: '#bbb', 'font-size': '10px' }
              }]
          },
          series: [
              {
                  name: data.chart_label,
                  type: data.chart_type,
                  color: color,
                  data: data.data
              }
          ]
      });
    };
    
    var renderPieChart = function(container, data){
      var browserData = [];
      var versionsData = [];
      for (var i = 0; i < data.data.data.length; i++) {
        // add browser data
        browserData.push({
          name: data.data.categories[i],
          y: data.data.data[i].y,
          color: data.data.data[i].color
        });
        // add version data
        for (var j = 0; j < data.data.data[i].drilldown.data.length; j++) {
          var brightness = 0.2 - (j / data.data.data[i].drilldown.data.length) / 5 ;
          versionsData.push({
            name: data.data.data[i].drilldown.categories[j],
            link: data.data.data[i].drilldown.links[j],
            y: data.data.data[i].drilldown.data[j],
            color: Highcharts.Color(data.data.data[i].color).brighten(brightness).get()
          });
        }
      }
      return new Highcharts.Chart({
        chart: {
          renderTo: container,
          type: 'pie'
        },
        title: {
          text: data.data.title
        },
        yAxis: {
          title: {
            text: data.name
          }
        },
        plotOptions: {
          pie: {
            shadow: false
          }
        },
        tooltip: {
          formatter: function() {
            return '<strong>'+ this.point.name +'</strong>';
          }
        },
        legend: {
          layout: 'vertical',
          align: 'left',
          verticalAlign: 'top',
          x: 10,
          y: 10,
                labelFormatter: function() {
                    return this.name;
                }
            },labels: {
                items: [{
            html: '', // 'Powered by <a href="http://shop.highsoft.com/faq#what-is-commercial-website">"Highcharts JS"</a>',
                    style: {
              left: '100px',
                        top: '260px',
                        color: 'white'
                    }
                }]
        },
        series: [{
          name: 'Organi',
          data: browserData,
          showInLegend: true,
          size: '60%',
          dataLabels: {
            formatter: function() {
              return null; // this.y > 0 ? this.point.name : null;
            },
            color: 'white',
            distance: -30
          }
        }, {
          name: 'Componenti',
          data: versionsData,
          innerSize: '60%',
          dataLabels: {
            formatter: function() {
              // display only if larger than 1
              return this.y > 0 ? '<strong><a href="'+this.point.link+'">'+ this.point.name +'</a></strong> '  : null;
            },
            color: '#000000'
          }
        }]
      });
    };
    
    var renderEmptyData = function(container){
      $('#'+container).find('.panel-body').html(
        '<p class="text-center"><em>Non ci sono informazioni da visualizzare</em></p>'
      );
    };
    
    var renderContents = function(container, contents){      
      var mainlist = $('<ul>'),
        mainli, list, li, a;
      $.each(contents, function(index,value){
        mainli = $('<p><strong>'+index+'</strong></p>');
        list = $('<ul class="list list-inline">');
        $.each(value, function(){          
          li = $('<li>');        
          a = $('<a>').attr('href', this.url_alias).html(this.name);
          li.append(a);
          list.append(li);          
        })
        mainli.append(list)
        mainlist.append(mainli);
      });      
      $('#'+container).html(mainlist);
    };
    
    $.getJSON(settings.endpoint, function(response) {
      
      renderInfo(settings.mainInfoContainer, response.info, settings.infoAttributiGenerici);
      renderInfo(settings.leftInfoContainer, response.info, settings.infoAttributiRiferimenti);
      renderInfo(settings.rightInfoContainer, response.info, settings.infoAttributiAltridati);
      
      if (response.geoJson.features.length > 0) {
        renderMap(settings.mapContainer, response.geoJson);
      }else{
        renderEmptyData(settings.mapContainer);
      }
      
      if (response.data.bilancio.data.has_data) {
        renderBilancio(settings.bilancioContainer, response.data.bilancio);
      }else{
        renderEmptyData(settings.bilancioContainer);
      }
      
      if (response.data.soci.data.has_data) {
        renderBarChart(settings.sociContainer, response.data.soci.data, colors[0]);
      }else{
        renderEmptyData(settings.sociContainer);
      }
      
      if (response.data.lavoratori.data.has_data) {
        renderBarChart(settings.lavoratoriContainer, response.data.lavoratori.data, colors[7]);
      }else{
        renderEmptyData(settings.lavoratoriContainer);
      }
      
      if (response.data.cda.data.has_data) {
        renderPieChart(settings.cdaContainer, response.data.cda);
      }else{
        renderEmptyData(settings.cdaContainer);
      }
      
      if (response.data.sindacale.data.has_data) {
        renderPieChart(settings.sindacaleContainer, response.data.sindacale);
      }else{
        renderEmptyData(settings.sindacaleContainer);
      }
      
      if (settings.contentContainer) {
        renderContents(settings.contentContainer, response.contents);
      }
      
      if (response.data.garanzie.data.has_data) {
        renderGaranzie(settings.garanzieContainer, response.data.garanzie.data, colors[3]);        
      }else{
        renderEmptyData(settings.garanzieContainer);
      }
      
      if (response.data.raccolta_e_prestiti.data.has_data) {        
        renderRaccoltaEPrestiti(settings.raccoltaContainer, response.data.raccolta_e_prestiti.data, colors[3]);
      }else{        
        renderEmptyData(settings.raccoltaContainer);
      }
      
      $(window).trigger("resize");
    });
  }
};