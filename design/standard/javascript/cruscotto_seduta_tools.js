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
    if (currentActionSettings && typeof currentActionSettings.action_url == 'string') {
        var values = [];
        $.each(currentActionSettings.fields, function (fieldIndex, fieldName) {
            var field = currentModal.find('*[name="' + fieldName + '"]');
            var fieldValue = field.val();
            if ( field.attr( 'type' ) == 'radio' )
                fieldValue = currentModal.find('*[name="' + fieldName + '"]:checked').val();
            values.push({
                name: fieldName,
                value: fieldValue
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
                handleResponseError(response, status, xhr);
                if (jQuery.isFunction(currentActionSettings.onError))
                    currentActionSettings.onError(currentModal);
            },
            dataType: 'json'
        });
    }
});

var handleResponseError = function (response, status, xhr) {
    if (status == 'error') {
        var $container = $('<div class="alert alert-danger alert-dismissible" style="margin-bottom: 0;" />');
        $container.append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
        $.each(response.responseJSON.error_messages, function (i, v) {
            $container.append('<p><strong>' + response.responseJSON.error_messages[i] + '</strong></p>');
        });
        $('#alert_area').html($container);
    }
};
var clearErrors = function () {
    $('#alert_area').html('');
};
var showTotaleVotanti = function(){
    TotaleVotanti.show();
};
var hideTotaleVotanti = function(){
    TotaleVotanti.hide();
    TotaleVotanti.html('0');
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

    startVotazione: function(){
        var self = $(this);
        var content = self.html();
        self.html('<i class="fa fa-spinner fa-spin"></i> Attendere');
        $.ajax({
            url: self.data('action_url'),
            method: 'POST',
            data: {idVotazione:self.data('votazione')},
            success: function (data) {
                showTotaleVotanti();
                startTimer();
                Presenze.startVotoPartecipanti();
                var text = "\n" + self.data('add_to_verbale') + ' ' + currentDate() + "\n";
                Verbale.addToVerbale(self.data('verbale'),text);
                Votazioni.reload();
                clearErrors();
            },
            error: function (response, status, xhr) {
                handleResponseError(response, status, xhr);
                hideTotaleVotanti();
                self.html(content);
            },
            dataType: 'json'
        });
    },

    stopVotazione: function(){
        var self = $(this);
        self.addClass('btn-danger').html('<i class="fa fa-spinner fa-spin"></i> Attendere');
        stopTimer();
        var text = "\n" + self.data('add_to_verbale') + ' ' + currentDate() + "\n";
        Verbale.addToVerbale(self.data('verbale'),text);
        var idVotazione = self.data('votazione');
        $.ajax({
            url: self.data('action_url'),
            method: 'POST',
            data: {idVotazione:idVotazione},
            success: function (data) {
                hideTotaleVotanti();
                Votazioni.reload();
                Presenze.resetVotoPartecipanti();
                clearErrors();
                $('a#viewVotazione-'+idVotazione).trigger('click');

            },
            error: function (response, status, xhr) {
                hideTotaleVotanti();
                Votazioni.reload();
                handleResponseError(response, status, xhr);
            },
            dataType: 'json'
        });
    },

    removeVotazione: function(){
        var self = $(this);
        if ( confirm("Confermi rimozione?") ) {
            $.ajax({
                url: self.data('remove_action_url'),
                method: 'POST',
                data: {idVotazione: self.data('remove_votazione')},
                success: function (data) {
                    Votazioni.reload();
                    clearErrors();
                },
                error: function (response, status, xhr) {
                    Votazioni.reload();
                    handleResponseError(response, status, xhr);
                },
                dataType: 'json'
            });
        }
    },

    getPartecipante: function(id){
        return $(this).find("[data-partecipante='" + id + "']");
    },

    setPartecipante: function(data){
        var self = $(this);
        var offClass = 'blurred';
        var stato = self.find('.stato-presenza');
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
            self.removeClass( offClass );
        else
            self.addClass( offClass );
        self.data('last_update', data.timestamp );
    },

    setVotoPartecipante: function(data){
        var self = $(this);
        var stato = self.find('.stato-votazione');
        if (data.anomaly) {
            stato.addClass( 'voto-anomalo' );
            stato.find('a.mark_invalid').show().data('voto_id', data.id);
        }else{
            stato.addClass( 'ha-votato' );
        }
        self.data('last_update', data.created_timestamp );
        var totale = parseInt( TotaleVotanti.text() ) + 1;
        TotaleVotanti.html( totale );
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
              handleResponseError(response, status, xhr);
          }
      });
    },

    startVotoPartecipante: function(){
        var stato = $(this).find('.stato-votazione');
        stato.removeClass( 'voto-anomalo' ).removeClass( 'ha-votato' ).addClass( 'deve-votare' );
        stato.find('a.mark_invalid').hide(); 
    },
    
    startVotoPartecipanti: function(){
        var self = $(this);
        $('tr.partecipante', self).each( function(){
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

    showVerbale: function(id){        
        if (id === 'all'){
            $(this).find('tr.verbaleRow').show();
        }else{
            $(this).find('tr.verbaleRow').hide();
            var current = $(this).find("tr.verbaleRow[data-verbale_id='" + id + "']").show();   
            // var input = current.find('#verbaleField-'+id);   
            // if (input.prop("tagName") == 'INPUT'){
            //     input.focus();
            // }else{                
            //     input.summernote('editor.focus');
            // }      
        }        
        return $(this);
    },

    addToVerbale: function(identifier,text){                
        console.log(identifier, text);        
        // var input = $(this).find('#verbaleField-'+identifier);
        // if (!input.is(':disabled')){                
        //     if (input.prop("tagName") == 'INPUT'){
        //         input.val(text);
        //     }else{                
        //         input.summernote('pasteHTML', text);
        //     }
        // }                
        return $(this);
    },

    saveVerbale: function(id){
        var values = [];
        $(this).find('.verbaleField').each(function (i, v) {
            var that = $(this);
            values.push({name: that.attr('name'), value: that.val()});
        });
        var self = $(this);
        $.ajax({
            url: $(this).data('save_url'),
            method: 'POST',
            data: values,
            success: function (data) {
                self.loadVerbale(id);
                clearErrors();
            },
            error: function (response, status, xhr) {
                handleResponseError(response, status, xhr);
            },
            dataType: 'json'
        });
    },

    loadVerbale: function(id){
        var self = $(this);
        $(this).load($(this).data('load_url'), function () {            
            $('textarea.verbaleField').summernote({
                "toolbar":[
                    ['style',  ['bold', 'italic', 'underline']],                
                    ['para',   ['ul', 'ol']],
                    ['insert', ['table', 'hr']]
                ]
            });
            $('.resetVerbale').on('click', function(e){                                            
                var that = $(this);
                var identifier = that.data('verbale_id');
                $.get(self.data('load_url'), function (data) {                    
                    var text = $(data).find('#defaultVerbale-'+identifier).val();
                    var input = that.parents('tr').find('#verbaleField-'+identifier);
                    if (!input.is(':disabled')){                
                        if (input.prop("tagName") == 'INPUT'){
                            input.val(text);
                        }else{                
                            input.summernote('code', text);
                        }
                    }
                });                            
                e.preventDefault();
            });
            if (id){ self.showVerbale(id);}
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
