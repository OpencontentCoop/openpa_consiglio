$(document).ready(function () {
    $('#page').hide();
});

var socket = io(SocketUrl+':'+SocketPort);

socket.on('connect', function () {
    $('#page').show();
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
        $('#text').show().find( 'h1').html('<strong>Punto ' + data.numero + '</strong><br />' + data.oggetto);
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
        $('#text').show().find( 'h1').html('Arrivederci!');        
    }
});

socket.on('start_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').hide();
        $('#text').show().find('.alert').show().find( 'h1').html('<strong>Aperta votazione:</strong><br /> ' + data.short_text + '<br /><small>'+data.text+'</smal>');
    }
});

socket.on('stop_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#text').show().find('.alert').addClass('alert-danger').show().find( 'h1').html('<strong>Chiusa votazione:</strong><br /> ' + data.short_text);
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
