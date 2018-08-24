var Verbale = $('#verbale');
var Odg = $('#odg_list');
var Votazioni = $('#votazioni');
var Presenze = $('#presenze');
var TotaleVotanti = $('#totale-votanti');

$(document).on('click', 'a.save-verbale', function (e) {
    e.preventDefault();    
    var verbaleId = $(e.currentTarget).data('verbale_id');
    Verbale.saveVerbale(verbaleId);
});

$(document).on('click', 'a.add-timeholder', function (e) {
    e.preventDefault();
    var text = "\n" + currentDate() + "\n";
    var verbaleId = $(e.currentTarget).data('verbale_id');
    Verbale.addToVerbale(verbaleId,text);
});

$(document).on('click', 'a.load-verbale', function (e) {    
    Verbale.loadVerbale();
    e.preventDefault();
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
    var actionName = button.text();
    $.confirm({
        text: 'Confermi l\'azione "' + actionName + '"?',
        confirmButton: "Confermo",
        cancelButton: "Annulla",
        confirm: function() {
            $.get(button.data('action_url'), function () {
                container.load( container.data('load_url'), function () {
                    Odg.reload();
                    // var text = "\n" + button.data('add_to_verbale') + ' ' + currentDate() + "\n";
                    // Verbale.addToVerbale(CurrentSedutaId,text).showVerbale(CurrentSedutaId);
                    Verbale.showVerbale('all');
                });
                clearErrors();
            });
        },
        cancel: function() {
            // nothing to do
        }
    });
});

$(document).on('click', 'a.punto_start_stop', function (e) {
    e.preventDefault();
    var button = $(e.currentTarget);
    var puntoId = button.data('punto_id');
    var actionName = button.text() + ' ' + button.parents('.list-group-item').find('a.show-verbale').text();

    $.confirm({
        text: 'Confermi l\'azione "' + actionName + '"?',
        confirmButton: "Confermo",
        cancelButton: "Annulla",
        confirm: function() {
            $.get(button.data('action_url'), function () {
                var text = "\n" + button.data('add_to_verbale') + ' ' + currentDate() + "\n";
                Verbale.addToVerbale(puntoId,text).showVerbale(puntoId);                
                Odg.reload();
                clearErrors();
            }).fail(function (response, status, xhr) {
                handleResponseError(response, status, xhr);
            });
        },
        cancel: function() {
            // nothing to do
        }
    });
});

$(document).on('click', '.partecipante .actions a', function (e) {
    e.preventDefault();
    var current = $(e.currentTarget);
    var actionName = current.attr('title') + ' ' + current.parents('.partecipante').find('.nome').text();
    $.confirm({
        text: 'Confermi l\'azione "' + actionName + '"?',
        confirmButton: "Confermo",
        cancelButton: "Annulla",
        confirm: function() {
            $.ajax({
                url: current.data('action_url'),
                method: 'GET',
                error: function (response, status, xhr) {
                    handleResponseError(response, status, xhr);
                }
            });
        },
        cancel: function() {
            // nothing to do
        }
    });
});

$(document).on('click', '.partecipante .stato-votazione a.mark_invalid', function (e) {
    e.preventDefault();
    var current = $(e.currentTarget);
    var partecipante = Presenze.getPartecipante( current.parents( '.partecipante' ).data( 'partecipante' ) );
    var actionName = 'Annulla voto di  ' + current.parents('.partecipante').find('.nome').text();
    $.confirm({
        text: 'Confermi l\'azione "' + actionName + '"?',
        confirmButton: "Confermo",
        cancelButton: "Annulla",
        confirm: function() {
            partecipante.removeVotoPartecipante(current.data());
        },
        cancel: function() {
            // nothing to do
        }
    });
    e.preventDefault();
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
    var currentTarget = $(e.currentTarget);
    var actionName = currentTarget.data('add_to_verbale');
    $.confirm({
        text: 'Confermi l\'azione "' + actionName + '"?',
        confirmButton: "Confermo",
        cancelButton: "Annulla",
        confirm: function() {
            currentTarget.startVotazione();
        },
        cancel: function() {
            // nothing to do
        }
    });
    e.preventDefault();
});

$(document).on('click', '.stop_votazione', function (e) {
    var currentTarget = $(e.currentTarget);
    var actionName = currentTarget.data('add_to_verbale');
    $.confirm({
        text: 'Confermi l\'azione "' + actionName + '"?',
        confirmButton: "Confermo",
        cancelButton: "Annulla",
        confirm: function() {
            currentTarget.stopVotazione();
        },
        cancel: function() {
            // nothing to do
        }
    });
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

$(document).on('click', '.launch_monitor_verbale', function (e) {
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
    Verbale.loadVerbale();  
});

$(document).ajaxSend(function () {
    $("#loading").show();
}).ajaxComplete(function () {
    $("#loading").hide();
});


var socket = io(SocketUrl + ':' + SocketPort);

socket.on('connect', function () {
    $('#disconnected').hide();
    $('#page').show();
    $("#loading").removeClass( 'text-danger' ).hide();
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

