var Modals = [
    {
        name: 'creaVotazione',
        title: 'Nuova votazione',
        fields: ['shortText', 'text', 'puntoId'],
        action: ActionBaseUrl + 'creaVotazione',
        resetForm: true,
        onShow: null,
        onSent: function (data, modal) {
            $('#votazioni').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:votazioni');
            modal.modal('hide');
        }
    },
    {
        name: 'startVotazione',
        title: 'Avvia votazione',
        fields: ['idVotazione'],
        action: ActionBaseUrl + 'startVotazione',
        resetForm: true,
        onShow: function (modal, button) {
            modal.find('#currentVotazione').val(button.data('votazione'));
            modal.find('.modal-title').html(button.data('votazione_title'));
            var votazione = $('#votazione_in_progress');
            votazione.find('.voto').hide();        
            votazione.find('a.mark_invalid').hide().data('voto_id','');              
        },
        onSend: function (values, modal) {
            var currentSettings = modal.data('currentSettings');
            modal.find('button.btn-primary').html('<i class="fa fa-spinner fa-spin"></i> Attendere');
            if (currentSettings.action == ActionBaseUrl + 'stopVotazione') {
                stopTimer();
            }
        },
        onSent: function (data, modal) {
            var current = modal.data('current')
            var currentSettings = modal.data('currentSettings');
            if (currentSettings.action == ActionBaseUrl + 'startVotazione') {
                startTimer();
                modal.find('button.btn-primary').html('Chiudi votazione');
                modal.find('#cancelVotazioneButton').hide();
                currentSettings.action = ActionBaseUrl + 'stopVotazione';
                modal.data('currentSettings', currentSettings);
            }
            else {
                modal.modal('hide');
                modal.find('button.btn-primary').html('Apri votazione');
                modal.find('#cancelVotazioneButton').show();
                currentSettings.action = ActionBaseUrl + 'startVotazione';
                modal.data('currentSettings', currentSettings);
            }
            $('#votazioni').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:votazioni');
        }
    },
    {
        name: 'risultatiVotazione',
        title: 'Risultati votazione',
        onShow: function (modal, button) {
            modal.find('.modal-body').html('');
            var idVotazione = button.data('votazione');
            modal.find('.modal-body').load(VotazioneDataBaseUrl + idVotazione + '/parts:risultato_votazione');
        }
    }
];

var timer;
var startTimer = function () {
    var timerContainer = $("#timer");
    var sec = 0;
    function pad(val) {
        return val > 9 ? val : "0" + val;
    }
    timer = setInterval(function () {
        timerContainer.find(".seconds").html(pad(++sec % 60));
        timerContainer.find(".minutes").html(pad(parseInt(sec / 60, 10)));
    }, 1000);
    timerContainer.show();
};
var stopTimer = function () {
    clearInterval(timer);
    var timerContainer = $("#timer").hide();
    timerContainer.find(".seconds").html('00');
    timerContainer.find(".minutes").html('00');
};

var currentDate = function () {
    var currentdate = new Date();
    return currentdate.getDate() + "/"
    + (currentdate.getMonth() + 1) + "/"
    + currentdate.getFullYear() + " @"
    + currentdate.getHours() + ":"
    + currentdate.getMinutes();
};

var showVerbale = function (id) {
    var verbale = $('#verbale');
    verbale.find('div.textarea-container').hide();
    var textarea = verbale.find('textarea[name="Verbale[' + id + ']"]');
    textarea.parent('div').show();
    textarea.focus();
    return textarea;
};

var timeoutId;
$(document).on('input propertychange change insertText', 'textarea', function (e) {
    if (e.type == 'insertText'){
        saveVerbale(e);
    }else {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(function () {
            saveVerbale(e);
        }, 3000);
    }
});
var saveVerbale = function (e) {
    var currentTextareaName = $(e.currentTarget).attr('name');
    var values = [];
    var verbale = $('#verbale');
    verbale.find('textarea').each(function (i, v) {
        var that = $(this);
        values.push({name:that.attr('name'), value:that.val()});
    });
    $.ajax({
        url: ActionBaseUrl + 'saveVerbale',
        method: 'POST',
        data: values,
        success: function (data) {
            verbale.load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:verbale',function(){
                var textarea = verbale.find('textarea[name="' + currentTextareaName + '"]');
                if( textarea.parent('div').is(':visible') ) textarea.focusEnd();
            });
            clearErrors();
        },
        error: function (response, status, xhr) {
            handelResponseError(response, status, xhr);
        },
        dataType: 'json'
    });
};

var handelResponseError = function (response, status, xhr) {
    if (status == 'error') {
        var $container = $('<div class="alert alert-danger" />');
        $.each(response.responseJSON.error_messages, function (i, v) {
            $container.append('<p>' + response.responseJSON.error_messages[i] + '</p>');
        });
        $('#alert_area').html($container);
    }
};
var clearErrors = function () {
    $('#alert_area').html('');
};

$('.modal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var current = button.data('action'); // Extract info from data-* attributes
    var modal = $(this);
    var currentActionSettings = [];
    $.each(Modals, function (i, v) {
        if (v.name == current) {
            currentActionSettings = v;
            if (currentActionSettings.resetForm == true)
                modal.find('form')[0].reset();
            modal.find('.modal-title').html(currentActionSettings.title);
        }
    });
    modal.data('current', current);
    modal.data('currentSettings', currentActionSettings);
    if (jQuery.isFunction(currentActionSettings.onShow))
        currentActionSettings.onShow(modal, button);
});

$(document).on('click', '.modal button.btn-primary', function (e) {
    var currentModal = $(e.currentTarget).parents('.modal');
    var currentAction = currentModal.data('current');
    var currentActionSettings = currentModal.data('currentSettings');
    if (typeof currentActionSettings.action == 'string') {
        var values = [];
        $.each(currentActionSettings.fields, function (fieldIndex, fieldName) {
            values.push({
                name: fieldName,
                value: currentModal.find('*[name="' + fieldName + '"]').val()
            });
        });
        if (jQuery.isFunction(currentActionSettings.filterPostData))
            values = currentActionSettings.filterPostData(values, currentModal);
        if (jQuery.isFunction(currentActionSettings.onSend))
            currentActionSettings.onSend(values, currentModal);
        $.ajax({
            url: currentActionSettings.action,
            method: 'POST',
            data: values,
            success: function (data) {
                if (jQuery.isFunction(currentActionSettings.onSent))
                    currentActionSettings.onSent(data, currentModal);
                clearErrors();
            },
            error: function (response, status, xhr) {
                currentModal.modal('hide');
                handelResponseError(response, status, xhr);
                if (jQuery.isFunction(currentActionSettings.onError))
                    currentActionSettings.onError(currentModal);
            },
            dataType: 'json'
        });
    }
});

$(document).on('click', '#seduta_startstop_button a.btn', function (e) {
    var button = $(e.currentTarget);
    $.get(button.data('url'), function () {
        $('#punto_startstop_button').html('');
        $('#seduta_startstop_button').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:seduta_startstop_button', function () {
            $('#punto_startstop_button').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:punto_startstop_button');
            var verbale = showVerbale(CurrentSedutaId);
            if (button.data('action') == 'start') {
                verbale.focusEnd().insertAtCursor("Inizio trattazione " + currentDate() + "\n");
            } else {
                verbale.focusEnd().insertAtCursor("\nFine trattazione " + currentDate());
            }
        });
        clearErrors();
    });
});

$(document).on('click', '#odg_list a, #odg_title a', function (e) {
    var verbaleId = $(e.currentTarget).data('verbale_id');
    showVerbale(verbaleId);
    e.preventDefault();
});

$(document).on('click', '#punto_startstop_button a.btn', function (e) {
    var button = $(e.currentTarget);
    var puntoId = button.data('punto_id');
    $.get(button.data('url'), function () {
        var verbale = showVerbale(puntoId);
        if (button.data('action') == 'start') {
            verbale.focusEnd().insertAtCursor("Inizio trattazione " + currentDate() + "\n");
        } else {
            verbale.focusEnd().insertAtCursor("\nFine trattazione " + currentDate());
        }
        $('#odg_list').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:odg_list');
        $('#punto_startstop_button').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:punto_startstop_button');
        clearErrors();
    }).fail(function (response, status, xhr) {
        handelResponseError(response, status, xhr);
    });
});

$(document).on('click', '#presenzeTemplate .user_buttons a', function (e) {
  var current = $(e.currentTarget);
  var action = current.data('action');
  var user = current.data('user_id');
  $.ajax({
      url: ActionBaseUrl+action+'?uid='+user,
      method: 'GET',
      error: function (response, status, xhr) {
        handelResponseError(response, status, xhr);
      }
  });
});

$(document).on('click', '#votazione_in_progress .user_buttons a.mark_invalid', function (e) {
  var current = $(e.currentTarget);
  var action = current.data('action');
  var user = current.data('user_id');
  var voto = current.data('voto_id');
  clearErrors();
  $.ajax({
      url: ActionBaseUrl+action+'?uid='+user+'&vid='+voto,
      method: 'GET',
      success: function (data) {
        var votazioneUser = $('#votazione_in_progress').find('.user-' + user);
        votazioneUser.find('.voto').hide();
        votazioneUser.find('a.mark_invalid').hide();
      },
      error: function (response, status, xhr) {
        handelResponseError(response, status, xhr);
      }
  });
});

$(document).on('change', '#message-point', function (e) {
    $('#popolaTestoVotazione').show();
});

$(document).on('click', '#popolaTestoVotazione', function (e) {
    var value = $('#message-point').find('option:selected').data('text');
    $('#message-text').val( value );
    e.preventDefault();
});

$(document).on('click', '.stopVotazione', function (e) {
    $.ajax({
      url: ActionBaseUrl + 'stopVotazione',
      data: {idVotazione:$(e.currentTarget).data('votazione')},
      method: 'POST',
      success: function (data) {
        $('#votazioni').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:votazioni');
      }
    });
    e.preventDefault();
});

$(document).ready(function () {
    $('#page').hide();
});

var socket = io(SocketUrl+':'+SocketPort);

socket.on('connect', function () {
    $('#page').show();
});

socket.on('presenze', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        $('#presenze_button').load(SedutaDataBaseUrl + ':consiglio:cruscotto_seduta:presenze_button');
        var user = $('#presenzeTemplate').find('.user-' + data.user_id);
        var inOutClass = data.in_out ? 'btn-success' : 'btn-danger';
        var opacity = data.has_checkin ? 1 : 0.4;
        user.find('.name').css({'opacity': opacity});
        user.find('.type').removeClass('btn-success').removeClass('btn-danger').hide();
        if (data.in_out == false && data.type == 'checkin') {
            //code
        }else{
            user.find('.'+data.type).addClass(inOutClass).show();
        }
        $('#votazione_in_progress').find('.user-' + data.user_id ).css({'opacity': opacity});        
    }
});

socket.on('voto', function (data) {
    if (data.seduta_id == CurrentSedutaId) {
        var votazioneUser = $('#votazione_in_progress').find('.user-' + data.user_id);
        votazioneUser.find('.voto').show();
        if (data.anomaly) {
          votazioneUser.find('a.mark_invalid').show().data('voto_id',data.id);
        }
    }
});

$(document).ajaxSend(function() {
    $("#loading" ).show();
}).ajaxComplete(function() {
    $("#loading" ).hide();
}).ajaxError(function() {
    $("#loading" ).hide();
});

jQuery.fn.extend({
    setCursorPosition: function (position) {
        if (this.length == 0) return this;
        return $(this).setSelection(position, position);
    },

    setSelection: function (selectionStart, selectionEnd) {
        if (this.length == 0) return this;
        input = this[0];

        if (input.createTextRange) {
            var range = input.createTextRange();
            range.collapse(true);
            range.moveEnd('character', selectionEnd);
            range.moveStart('character', selectionStart);
            range.select();
        } else if (input.setSelectionRange) {
            input.focus();
            input.setSelectionRange(selectionStart, selectionEnd);
        }

        return this;
    },

    focusEnd: function () {
        this.setCursorPosition(this.val().length);
        return this;
    },

    getCursorPosition: function () {
        var el = $(this).get(0);
        var pos = 0;
        if ('selectionStart' in el) {
            pos = el.selectionStart;
        } else if ('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
    },

    insertAtCursor: function (myValue) {
        return this.each(function (i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos) + myValue +
                this.value.substring(endPos, this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
            $(this).trigger('insertText');
        })
    }
});
