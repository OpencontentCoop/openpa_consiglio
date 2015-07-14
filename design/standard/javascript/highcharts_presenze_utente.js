$(function () {
    $('.presenze_utente_pie_container').each( function(){
        var data = $(this).data();

        var options = {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: 0,
                plotShadow: false
            },
            title: {
                text: data.title,
                align: 'center',
                verticalAlign: 'middle',
                y: 80
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: false,
                        distance: -50,
                        style: {
                            fontWeight: 'bold',
                            color: 'white',
                            textShadow: '0px 1px 2px black'
                        }
                    },
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%']
                }
            },
            series: [{
                type: 'pie',
                name: data.title,
                innerSize: '50%',
                data: []
            }]
        };
        $.getJSON(data.url, function(result) {
            options.series[0].data = result;
            var chart = new Highcharts.Chart(options);
        });
        $(this).highcharts(options);
    });
});
