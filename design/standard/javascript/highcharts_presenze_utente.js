$(function () {
    $('.presenze_utente_pie_container').each( function(){
        var self = $(this);
        var data = self.data();        
        $.getJSON(data.url, function(result) {
            self.highcharts({
                chart: {
                    type: 'bar'
                },
                title: {
                    text: '',                    
                },                  
                xAxis: {
                    categories: result.anni
                },       
                yAxis: {
                    min: 0,   
                    title: {
                        text: ''
                    }             
                },
                legend: {
                    reversed: true
                },
                plotOptions: {
                    series: {
                        stacking: 'percent'
                    }
                },
                series: [{
                    name: 'Presente',
                    data: result.presenze
                }, {
                    name: 'Assente',
                    data: result.assenze
                }]           
            });
        });        
    });
});
