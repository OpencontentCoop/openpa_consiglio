<div id="alert_area">
    {if count( $errors )}
        <div class="alert alert-danger">
            {foreach $errors as $error}
                <p>{$error|wash()}</p>
            {/foreach}
        </div>
    {/if}
</div>

<div class="clearfix">
    <div class="content-title">
        <h3>{$seduta.object.name}</h3>
    </div>
    <span id="seduta_startstop_button">
        {include uri="design:consiglio/cruscotto_seduta/seduta_startstop_button.tpl" post=$seduta}
    </span>
    <span id="punto_startstop_button">
        {include uri="design:consiglio/cruscotto_seduta/punto_startstop_button.tpl" post=$seduta}
    </span>
    <span id="presenze_button">
        {include uri="design:consiglio/cruscotto_seduta/presenze.tpl" post=$seduta}
    </span>
</div>

<hr/>

<div class="row">
    <div id="sidebar-area" class="col col-md-3">
        <div class="widget">

            <div class="widget_title">
                <h3>Ordine del giorno</h3>
            </div>
            <div class="widget_content" id="odg_list">
                {include uri="design:consiglio/cruscotto_seduta/odg_list.tpl" post=$seduta}
            </div>
        </div>
    </div>

    <div class="col col-md-6" id="verbale">
        {include uri="design:consiglio/cruscotto_seduta/verbale.tpl" post=$seduta}
    </div>

    <div id="extra-area" class="col col-md-3">
        <div class="widget" id="votazioni">
            {include uri="design:consiglio/cruscotto_seduta/votazioni.tpl" post=$seduta}
        </div>
    </div>

</div>

<div class="modal fade" id="creaVotazioneTemplate" tabindex="-1" role="dialog"
     aria-labelledby="previewLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="previewLabel">New message</h4>
            </div>
            <div class="modal-body">
                <form action="">
                    <div class="form-group">
                        <label for="recipient-name" class="control-label">Titolo breve:</label>
                        <input type="text" class="form-control" name="shortText" id="recipient-name">
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="control-label">Testo della votazione:</label>
                        <textarea class="form-control" name="text" id="message-text"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary">Salva</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="startVotazioneTemplate" role="dialog" data-backdrop="static" aria-labelledby="previewLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="previewLabel"></h4>
            </div>
            <div class="modal-body">
                <form action="">
                    <input id="currentVotazione" type="hidden" name="idVotazione" value="0" />
                </form>

                <div class="row" id="votazione_in_progress">
                {def $registro_presenze = $seduta.registro_presenze}
                {foreach $seduta.partecipanti as $partecipante}
                    <div class="col-xs-2 user_voto user-{$partecipante.object_id}" style="opacity: .4">
                        {if $registro_presenze.hash_user_id[$partecipante.object_id]}
                        {content_view_gui content_object=$partecipante.object view="politico_box"}
                        {/if}
                    </div>
                {/foreach}
                {undef $registro_presenze}
                </div>

            </div>
            <div class="modal-footer">
                <h4 id="timer" class="pull-left" style="display: none;"><strong><span class="minutes">00</span>:<span class="seconds">00</span></strong></h4>
                <button id="cancelVotazioneButton" type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary">Apri votazione</button>
            </div>
        </div>
    </div>
</div>

<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>
{literal}
<script>
    var CurrentSedutaId = {/literal}{$seduta.object_id}{literal};
    var DataBaseUrl = "{/literal}{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}{literal}/";
    var ActionBaseUrl = "{/literal}{concat('consiglio/cruscotto_seduta/',$seduta.object_id)|ezurl(no)}{literal}/";
    var Modals = [
        {
            name: 'creaVotazione',
            title: 'Nuova votazione',
            fields: ['shortText', 'text'],
            action: ActionBaseUrl+'creaVotazione',
            resetForm: true,
            onShow: null,
            onSent: function(data,modal){
                $('#votazioni').load( DataBaseUrl + ':consiglio:cruscotto_seduta:votazioni');
                modal.modal('hide');
            }
        },
        {
            name: 'startVotazione',
            title: 'Avvia votazione',
            fields: ['idVotazione'],
            action: ActionBaseUrl+'startVotazione',
            resetForm: true,
            onShow: function(modal,button){
                modal.find('#currentVotazione').val( button.data('votazione') );
                modal.find('.modal-title').html( button.data('votazione_title') );
                $('#votazione_in_progress').find('.user_voto').css({'opacity':0.4});
            },
            onSend: function(values,modal){
                var currentSettings = modal.data('currentSettings');
                modal.find('button.btn-primary').html('<i class="fa fa-spinner fa-spin"></i> Attendere');
                if ( currentSettings.action == ActionBaseUrl+'stopVotazione' ) {
                   stopTimer();
                }
            },
            onSent: function(data,modal){
                var current = modal.data('current')
                var currentSettings = modal.data('currentSettings');
                if ( currentSettings.action == ActionBaseUrl+'startVotazione' ) {
                    startTimer();
                    modal.find('button.btn-primary').html('Chiudi votazione');
                    modal.find('#cancelVotazioneButton').hide();
                    currentSettings.action = ActionBaseUrl + 'stopVotazione';
                    modal.data('currentSettings', currentSettings);
                }
                else
                {
                    modal.modal('hide');
                    modal.find('button.btn-primary').html('Apri votazione');
                    modal.find('#cancelVotazioneButton').show();
                    currentSettings.action = ActionBaseUrl + 'startVotazione';
                    modal.data('currentSettings', currentSettings);
                }
                $('#votazioni').load( DataBaseUrl + ':consiglio:cruscotto_seduta:votazioni');
            }
        }
    ];

    var timer;
    var startTimer = function(){
        var timerContainer = $("#timer");
        var sec = 0;
        function pad ( val ) { return val > 9 ? val : "0" + val; }
        timer = setInterval( function(){
            timerContainer.find(".seconds").html(pad(++sec%60));
            timerContainer.find(".minutes").html(pad(parseInt(sec/60,10)));
        }, 1000);
        timerContainer.show();
    };
    var stopTimer = function(){
        clearInterval ( timer );
        var timerContainer = $("#timer").hide();
        timerContainer.find(".seconds").html('00');
        timerContainer.find(".minutes").html('00');
    };
    var handelResponseError = function(response, status, xhr){
        if ( status == 'error' ){
            var $container = $('<div class="alert alert-danger" />');
            $.each( response.responseJSON.error_messages, function(i,v){
                $container.append('<p>'+response.responseJSON.error_messages[i]+'</p>');
            });
            $('#alert_area').html( $container );
        }
    };
    var clearErrors = function(){
        $('#alert_area').html('');
    };
    $('.modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var current = button.data('action'); // Extract info from data-* attributes
        var modal = $(this);
        var currentActionSettings = [];
        $.each(Modals,function(i,v){
            if(v.name == current){
                currentActionSettings = v;
                if ( currentActionSettings.resetForm == true )
                    modal.find('form')[0].reset();
                modal.find('.modal-title').html( currentActionSettings.title );
            }
        });
        modal.data('current', current);
        modal.data('currentSettings', currentActionSettings);
        if ( jQuery.isFunction( currentActionSettings.onShow ) )
            currentActionSettings.onShow( modal, button );
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
            if ( jQuery.isFunction( currentActionSettings.filterPostData ) )
                values = currentActionSettings.filterPostData(values,currentModal);
            if ( jQuery.isFunction( currentActionSettings.onSend ) )
                currentActionSettings.onSend(values,currentModal);
            $.ajax({
                url: currentActionSettings.action,
                method: 'POST',
                data: values,
                success: function (data) {
                    if ( jQuery.isFunction( currentActionSettings.onSent ) )
                        currentActionSettings.onSent(data,currentModal);
                    clearErrors();
                },
                error: function(response, status, xhr) {
                    currentModal.modal('hide');
                    handelResponseError(response, status, xhr);
                    if ( jQuery.isFunction( currentActionSettings.onError ) )
                        currentActionSettings.onError(currentModal);
                },
                dataType: 'json'
            });
        }
    });
    $(document).on( 'click', '#seduta_startstop_button a.btn', function(e){
        var action = $(e.currentTarget);
        $.get(action.data('url'), function() {
            $('#punto_startstop_button').html('');
            $('#seduta_startstop_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:seduta_startstop_button', function(){
                $('#punto_startstop_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:punto_startstop_button');
                $('#verbale').load( DataBaseUrl + ':consiglio:cruscotto_seduta:verbale');
            });
            clearErrors();
        });
    });
    $(document).on( 'click', '#punto_startstop_button a.btn', function(e){
        var action = $(e.currentTarget);
        $.get(action.data('url'), function() {
            $('#punto_startstop_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:punto_startstop_button',function(){
                $('#odg_list').load( DataBaseUrl + ':consiglio:cruscotto_seduta:odg_list');
                $('#verbale').load( DataBaseUrl + ':consiglio:cruscotto_seduta:verbale');
            });
            clearErrors();
        }).fail(function(response, status, xhr) {
            handelResponseError(response, status, xhr);
        });
    });
    //$('#page').hide();
    var socket = io('localhost:8000');
    socket.on('connect', function(){
        //$('#page').show();
    });
    socket.on('presenze',function(data){
        if ( data.seduta_id == CurrentSedutaId ){
            $('#presenze_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:presenze');
        }
    });
    socket.on('voto',function(data){
        if ( data.seduta_id == CurrentSedutaId ){
            $('#votazione_in_progress').find('.user-' + data.user_id).css({'opacity':1});
        }
    });
</script>
{/literal}