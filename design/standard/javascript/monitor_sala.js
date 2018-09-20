$(document).ready(function () {
    $('#page').hide();
    $('#disconnected').hide();
});
var socket = io(SocketUrl);

socket.on('connect', function () {
    $('#page').show();
    $('#disconnected').hide();
});

socket.on('connect_error', function (error) {
    $('#page').hide();
    $('#disconnected').show();
});

socket.on('disconnect', function () {
    $('#page').hide();
    $('#disconnected').show();
});

socket.on('presenze', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        var inOutClass = data.in_out ? 'btn-success' : 'btn-danger';
        var opacity = data.is_in ? 1 : 0.4;
        var user = $('#presenze').find('.user-' + data.user_id);
        user.find('.name').css({'opacity': opacity});
        user.find('.type').removeClass('btn-success').removeClass('btn-danger').hide();
        user.find('.'+data.type).addClass(inOutClass).show();
    }
});

socket.on('start_punto', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').show();
        $('#text').show().find( 'h1.text-content').html('<strong>Punto ' + data.numero + '</strong><br />' + data.oggetto);
        $('#text').show().find('.alert').hide();
    }
});

socket.on('show_punto', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').show();
        $('#text').show().find( 'h1.text-content').html('<strong>Punto ' + data.numero + '</strong><br />' + data.oggetto);
        $('#text').show().find('.alert').hide();
    }
});

socket.on('stop_punto', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').show();
        $('#text').hide();
        $('#detail').hide();
    }
});

socket.on('start_seduta', function (data) {
    if (data.id == CurrentSedutaId) {
        $('#seduta h2 small').html( 'Seduta in corso' );
    }
});
socket.on('stop_seduta', function (data) {
    if (data.id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').show();
        $('#seduta h2 small').html( 'Seduta non in corso' );
        $('#text').show().find( 'h1.text-content').html('Arrivederci!');
    }
});

socket.on('start_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        startTimer();
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').hide();
        $('#text').show().find('.alert').show().find( 'h1').html('<strong>Aperta votazione:</strong><br /> ' + data.short_text + '<br /><small>'+data.text+'</smal>');
    }
});

socket.on('stop_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        stopTimer();
        //$('#text').show().find('.alert').addClass('alert-danger').show().find( 'h1').html('<strong>Votazione conclusa</strong>');
        $('#text').show().find('.alert').removeClass('alert-danger').hide();
        $('#detail').load(VotazioneDataBaseUrl + data.id + '/parts:risultato_votazione_monitor' + '?time=' + Date.now()).show();
    }
});

socket.on('real_stop_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#text').show().find('.alert').removeClass('alert-danger').hide();
        $('#detail').load(VotazioneDataBaseUrl + data.id + '/parts:risultato_votazione_monitor' + '?time=' + Date.now()).show();
    }
});

socket.on('show_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#text').hide();
        $('#detail').html('<div class="text-center"><i class="fa fa-gear fa-spin fa-3x"></i></div>');
        $('#detail').load(VotazioneDataBaseUrl + data.id + '/parts:risultato_votazione_monitor' + '?time=' + Date.now()).show();
    }
});

socket.on('show_presenze', function (data) {
    if (data.id == CurrentSedutaId) {
        $('#presenze').show();
        $('#text').hide();
        $('#detail').hide();
    }
});

socket.on('show_verbale', function (data) {    
    if (data.id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#text').hide();
        var identifier = data.show_verbale_part;
        $('#detail').load(SedutaDataBaseUrl + ':consiglio:monitor_sala:verbale' + '?time=' + Date.now(), function(){
            if (identifier == 'all'){
                $('#detail .verbalePart').show();
            }else{
                $('#detail .verbalePart').hide();
                $('#detail #'+identifier).show();
            }
        }).show();
    }
});
