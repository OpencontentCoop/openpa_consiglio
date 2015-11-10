var Verbale = $('#verbale');
var Odg = $('#odg_list');
var Votazioni = $('#votazioni');
var Presenze = $('#presenze');

//var timeoutId;
//$(document).on('input propertychange change insertText', 'textarea', function (e) {
//    var name = $(e.currentTarget).attr('name');
//    if (e.type == 'insertText') {
//        Verbale.saveVerbale(name);
//    } else {
//        clearTimeout(timeoutId);
//        timeoutId = setTimeout(function () {
//            Verbale.saveVerbale(name);
//        }, 3000);
//    }
//});

$(document).on('click', 'a.save-verbale', function (e) {
    e.preventDefault();
    var name = $(e.currentTarget).prev().attr('name');
    Verbale.saveVerbale(name);
});

$(document).on('click', 'a.show-verbale', function (e) {
    e.preventDefault();
    var verbaleId = $(e.currentTarget).data('verbale_id');
    Verbale.showVerbale(verbaleId);
    e.preventDefault();
});

$(document).on('click', 'a.seduta_start_stop', function (e) {
    e.preventDefault();
    var button = $(e.currentTarget);
    var container = button.parent();
    $.get(button.data('action_url'), function () {
        container.load( container.data('load_url'), function () {
            Odg.reload();
            var text = "\n" + button.data('add_to_verbale') + ' ' + currentDate() + "\n";
            Verbale.showVerbale(CurrentSedutaId,text);
        });
        clearErrors();
    });
});

$(document).on('click', 'a.punto_start_stop', function (e) {
    e.preventDefault();
    var button = $(e.currentTarget);
    var puntoId = button.data('punto_id');
    $.get(button.data('action_url'), function () {
        var text = "\n" + button.data('add_to_verbale') + ' ' + currentDate() + "\n";
        Verbale.showVerbale(puntoId,text);
        Odg.reload();
        clearErrors();
    }).fail(function (response, status, xhr) {
        handelResponseError(response, status, xhr);
    });
});

$(document).on('click', '.partecipante .actions a', function (e) {
    e.preventDefault();
    var current = $(e.currentTarget);
    $.ajax({
        url: current.data('action_url'),
        method: 'GET',
        error: function (response, status, xhr) {
            handelResponseError(response, status, xhr);
        }
    });
});

$(document).on('click', '.partecipante .stato-votazione a.mark_invalid', function (e) {
    e.preventDefault();
    var current = $(e.currentTarget);
    var partecipante = Presenze.getPartecipante( current.parents( '.partecipante' ).data( 'partecipante' ) );
    partecipante.removeVotoPartecipante(current.data());    
});

$(document).on('change', '#message-point', function (e) {
    $('#popolaTestoVotazione').show();
});
$(document).on('click', '#popolaTestoVotazione', function (e) {
    var value = $('#message-point').find('option:selected').data('text');
    $('#message-text').val(value);
    e.preventDefault();
});

$(document).on('click', '.start_votazione', function (e) {
    $(e.currentTarget).startVotazione();
    e.preventDefault();
});

$(document).on('click', '.stop_votazione', function (e) {
    $(e.currentTarget).stopVotazione();
    e.preventDefault();
});

$(document).on('click', '.remove_votazione', function (e) {
    $(e.currentTarget).removeVotazione();
    e.preventDefault();
});

$(document).on('click', '.launch_monitor_votazione', function (e) {
    $.ajax({url: $(this).data('action_url'), method: 'GET'});
    e.preventDefault();
});

$(document).on('click', '.launch_monitor_presenze', function (e) {
    $.ajax({url: $(this).data('action_url'), method: 'GET'});
    e.preventDefault();
});

$(document).on('click', '.launch_monitor_punto', function (e) {
    $.ajax({url: $(this).data('action_url'), method: 'GET'});
    e.preventDefault();
});

$(document).ready(function () {
    $("#loading").addClass( 'text-danger' ).show();
    Presenze.sortPartecipanti();
});

$(document).ajaxSend(function () {
    $("#loading").show();
}).ajaxComplete(function () {
    $("#loading").hide();
});


var socket = io(SocketUrl + ':' + SocketPort);

socket.on('connect', function () {
    $("#loading").removeClass( 'text-danger' ).hide();
});

socket.on('presenze', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        var partecipante = Presenze.getPartecipante( data.user_id );
        partecipante.setPartecipante( data );
        Presenze.sortPartecipanti();
    }
});

socket.on('voto', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        var partecipante = Presenze.getPartecipante( data.user_id );
        partecipante.setVotoPartecipante(data);
        Presenze.sortPartecipanti();
    }
});
