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
        $('#text').show().find( 'h1').html(data.oggetto);
    }
});

socket.on('stop_punto', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').show();
        $('#text').hide();
    }
});

socket.on('stop_seduta', function (data) {
    if (data.id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#text').show().find( 'h1').html('Arrivederci!');
    }
});

socket.on('start_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze').hide();
        $('#text').show().find('.alert').show().find( 'h1').html('Votazione ' + data.short_text);
    }
});

socket.on('stop_votazione', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#text').show().find('.alert').hide();
    }
});