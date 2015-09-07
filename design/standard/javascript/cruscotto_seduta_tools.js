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

$('.modal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var current = button.data('modal_configuration'); // Extract info from data-* attributes
    var modal = $(this);
    var currentActionSettings = button.data();
    $.each(Modals, function (i, v) {
        if (v.name == current) {
            currentActionSettings = $.extend( {}, currentActionSettings, v );
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
    if (typeof currentActionSettings.action_url == 'string') {
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
            url: currentActionSettings.action_url,
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

var handelResponseError = function (response, status, xhr) {
    if (status == 'error') {
        var $container = $('<div class="alert alert-danger alert-dismissible" />');
        $container.append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
        $.each(response.responseJSON.error_messages, function (i, v) {
            $container.append('<p>' + response.responseJSON.error_messages[i] + '</p>');
        });
        $('#alert_area').html($container);
    }
};
var clearErrors = function () {
    $('#alert_area').html('');
};


jQuery.fn.sortElements = (function () {

    var sort = [].sort;

    return function (comparator, getSortable) {

        getSortable = getSortable || function () {
            return this;
        };

        var placements = this.map(function () {

            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,

            // Since the element itself will change position, we have
            // to have some way of storing its original position in
            // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );

            return function () {

                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }

                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);

            };

        });

        return sort.call(this, comparator).each(function (i) {
            placements[i].call(getSortable.call(this));
        });

    };

})();

jQuery.fn.extend({

    getVotazione: function(id){
        return $(this).find("[data-votazione='" + id + "']");
    },

    startVotazione: function(id){
        var self = $(this);
        var content = self.html();
        self.html('<i class="fa fa-spinner fa-spin"></i> Attendere');
        $.ajax({
            url: self.data('action_url'),
            method: 'POST',
            data: {idVotazione:self.data('votazione')},
            success: function (data) {
                startTimer();
                Presenze.startVotoPartecipanti();
                var text = "\n" + self.data('add_to_verbale') + ' ' + currentDate() + "\n";
                Verbale.showVerbale(self.data('verbale'),text);
                Votazioni.reload();
                clearErrors();
            },
            error: function (response, status, xhr) {
                handelResponseError(response, status, xhr);
                self.html(content);
            },
            dataType: 'json'
        });
    },

    stopVotazione: function(id){
        $(this).addClass('btn-danger').html('<i class="fa fa-spinner fa-spin"></i> Attendere');
        stopTimer();
        var text = "\n" + $(this).data('add_to_verbale') + ' ' + currentDate() + "\n";
        Verbale.showVerbale($(this).data('verbale'),text);
        $.ajax({
            url: $(this).data('action_url'),
            method: 'POST',
            data: {idVotazione:$(this).data('votazione')},
            success: function (data) {
                Votazioni.reload();
                Presenze.resetVotoPartecipanti();
                clearErrors();
            },
            error: function (response, status, xhr) {
                Votazioni.reload();
                handelResponseError(response, status, xhr);
            },
            dataType: 'json'
        });
    },

    removeVotazione: function(id){
        $.ajax({
            url: $(this).data('action_url'),
            method: 'POST',
            data: {idVotazione:$(this).data('votazione')},
            success: function (data) {
                Votazioni.reload();
                clearErrors();
            },
            error: function (response, status, xhr) {
                Votazioni.reload();
                handelResponseError(response, status, xhr);
            },
            dataType: 'json'
        });
    },

    getPartecipante: function(id){
        return $(this).find("[data-partecipante='" + id + "']");
    },

    setPartecipante: function(data){
        var offClass = 'blurred';
        var stato = $(this).find('.stato-presenza');
        var checkin = stato.find('.checkin');
        var beacons = stato.find('.beacons');
        var manual = stato.find('.manual');
        var statoOnClass = 'text-success';
        var statoOffClass = 'text-muted';
        if ( data.has_checkin )
            checkin.removeClass( statoOffClass ).addClass( statoOnClass );
        else
            checkin.removeClass( statoOnClass ).addClass( statoOffClass  );
        if ( data.has_beacons )
            beacons.removeClass( statoOffClass ).addClass( statoOnClass );
        else
            beacons.removeClass( statoOnClass ).addClass( statoOffClass  );
        if ( data.has_manual )
            manual.removeClass( statoOffClass ).addClass( statoOnClass );
        else
            manual.removeClass( statoOnClass ).addClass( statoOffClass  );
        if ( data.is_in )
            $(this).removeClass( offClass );
        else
            $(this).addClass( offClass );
        $(this).data('last_update', data.created_timestamp );
    },

    setVotoPartecipante: function(data){
        var stato = $(this).find('.stato-votazione');
        if (data.anomaly) {
            stato.addClass( 'voto-anomalo' );
            stato.find('a.mark_invalid').show().data('voto_id', data.id);
        }else{
            stato.addClass( 'ha-votato' );
        }
        $(this).data('last_update', data.created_timestamp );
    },
    
    removeVotoPartecipante: function(data){      
      clearErrors();
      var self = $(this);
      $.ajax({
          url: data.action_url + '&vid=' + data.voto_id,
          method: 'GET',
          success: function (data) {
            self.startVotoPartecipante();
          },
          error: function (response, status, xhr) {
              handelResponseError(response, status, xhr);
          }
      });
    },

    startVotoPartecipante: function(){
        var stato = $(this).find('.stato-votazione');
        stato.removeClass( 'voto-anomalo' ).removeClass( 'ha-votato' ).addClass( 'deve-votare' );
        stato.find('a.mark_invalid').hide(); 
    },
    
    startVotoPartecipanti: function(){
        $('tr.partecipante', $(this)).each( function(){
            $(this).startVotoPartecipante()
        });
    },
    
    resetVotoPartecipante: function(){        
        var stato = $(this).find('.stato-votazione');
        stato.removeClass( 'ha-votato' ).removeClass( 'deve-votare' ).removeClass( 'voto-anomalo' );
        stato.find('a.mark_invalid').hide();        
    },

    resetVotoPartecipanti: function(){
        $('tr.partecipante', $(this)).each( function(){
            $(this).resetVotoPartecipante();
        });
    },

    sortPartecipanti: function(){
        var rowPartecipante = $('tr.partecipante', $(this));
        if ( rowPartecipante.length > 0 ) {
            rowPartecipante.sortElements(function (a, b) {
                return $(a).data('last_update') > $(b).data('last_update') ? -1 : 1;
            });
            $('span.totale-presenze').html(rowPartecipante.not('.blurred').length);
        }
    },

    reload: function(){
        var self = $(this);
        $(this).load($(this).data('load_url'));
    },

    showVerbale: function(id,text){
        $(this).find('div.textarea-container').hide();
        var textarea = $(this).find('textarea[name="Verbale[' + id + ']"]');
        textarea.parent('div').show();
        textarea.focus();
        if ( text )
            textarea.focusEnd().insertAtCursor(text);
        return $(this);
    },

    saveVerbale: function(textAreaName ){
        var values = [];
        $(this).find('textarea').each(function (i, v) {
            var that = $(this);
            values.push({name: that.attr('name'), value: that.val()});
        });
        var self = $(this);
        $.ajax({
            url: $(this).data('save_url'),
            method: 'POST',
            data: values,
            success: function (data) {
                self.loadVerbale(textAreaName);
                clearErrors();
            },
            error: function (response, status, xhr) {
                handelResponseError(response, status, xhr);
            },
            dataType: 'json'
        });
    },

    loadVerbale: function(textAreaName){
        var self = $(this);
        $(this).load($(this).data('load_url'), function () {
            var textarea = self.find('textarea[name="' + textAreaName + '"]');
            if (textarea.parent('div').is(':visible')) textarea.focusEnd();
        });
    },

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
