$(document).ready(function () {
    $('#page').hide();
});

var socket = io(SocketUrl+':'+SocketPort);

socket.on('connect', function () {
    $('#page').show();
});

socket.on('presenze', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze')
            .find('.user-' + data.user_id)
            .css({'opacity': data.in_out ? 1 : 0.4});
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

socket.on('stop_seduta', function (data) {
    if (data.id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').show();
        $('#text').show().find( 'h1').html('Arrivederci!');
    }
});

socket.on('start_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#detail').hide();
        $('#text').show().find('.data').hide();
        $('#text').show().find('.alert').show().find( 'h1').html('<strong>Aperta votazione:</strong><br /> ' + data.short_text);
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
        $('#detail').load(VotazioneDataBaseUrl + data.id + '/parts:risultato_votazione').show();        
    }
});