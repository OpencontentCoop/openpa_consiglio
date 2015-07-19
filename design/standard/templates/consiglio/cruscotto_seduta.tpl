<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>
{literal}
<script>
    var CurrentSedutaId = {/literal}{$seduta.object_id}{literal};
    var DataBaseUrl = "{/literal}{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}{literal}/";
    var socket = io('localhost:8000');
    socket.on('connect', function(){
    });
    socket.on('presenze',function(data){
        if ( data.seduta_id == CurrentSedutaId ){
            $('#presenze_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:presenze');
        }
    });

    var forms = [
        {
            name: '@tega',
            fields: ['name', 'body'],
            action: 'string',
            onSend: function(data,container){console.log(data);container.modal('hide')}
        },
        {
            name: '@getbootstrap',
            fields: ['body'],
            action: 'strong',
            filterPostData: function(data){data.push({name:'pippo',value:'pluto'});return data},
            onSend: function(data,container){console.log(data);container.modal('hide')}
        }
    ];
    $('.modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var current = button.data('whatever') // Extract info from data-* attributes
        var modal = $(this);
        var currentActionSettings = [];
        $.each(forms,function(i,v){if(v.name == current)currentActionSettings = v});
        if(typeof currentActionSettings.action != "undefined") {
            $.get(currentActionSettings.action, {current: current}, function (data) {
                var currentSettings = {fields: []};
                modal.data('current', current);
                $.each(forms, function (i, v) {
                    if (v.name == current) currentSettings = v;
                });
                modal.data('currentSettings', currentSettings);
                modal.find('form')[0].reset();
                modal.find('.modal-title').text('New message to ' + current);
            });
        }
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
            if (typeof currentActionSettings.filterPostData == 'function'){
                values = currentActionSettings.filterPostData(values);
            }
            $.post(currentActionSettings.action, values, function (data) {
                currentActionSettings.onSend(data,currentModal);
            }).fail(function(response, status, xhr) {
                handelResponseError(response, status, xhr);
            });
        }
    });

    var handelResponseError = function(response, status, xhr){
        if ( status == 'error' )
        {
            var $container = $('<div class="alert alert-danger" />');
            $.each( response.responseJSON.error_messages, function(i,v){
                $container.append('<p>'+response.responseJSON.error_messages[i]+'</p>');
            });
            $('#alert_area').html( $container );
        }
    };

    $(document).on( 'click', '#seduta_startstop_button a.btn', function(e){
        var action = $(e.currentTarget);
        $.get(action.data('url'), function () {
            $('#punto_startstop_button').html('');
            $('#seduta_startstop_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:seduta_startstop_button', function(){
                $('#punto_startstop_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:punto_startstop_button');
            });
        }).fail(function(response, status, xhr) {
            handelResponseError(response, status, xhr);
        });
    });

    $(document).on( 'click', '#punto_startstop_button a.btn', function(e){
        var action = $(e.currentTarget);
        $.get(action.data('url'), function () {
            $('#punto_startstop_button').load( DataBaseUrl + ':consiglio:cruscotto_seduta:punto_startstop_button',function(){
                $('#odg_list').load( DataBaseUrl + ':consiglio:cruscotto_seduta:odg_list');
            });
        }).fail(function(response, status, xhr) {
            handelResponseError(response, status, xhr);
        });
    })

</script>
{/literal}
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

    <div id="content-area" class="col col-md-6">
        {include uri="design:consiglio/cruscotto_seduta/verbale.tpl" post=$seduta}
    </div>

    <div id="extra-area" class="col col-md-3">

        <div class="widget">

            <div class="widget_title">
                <h3>Votazioni</h3>
            </div>
            <div class="widget_content">

                <ul class="side_menu">
                    <li>
                        <a href="#">
                            <b>Votazione pinco pallino Votazione pinco pallino Votazione pinco
                                pallino Votazione pinco pallino</b>
                            <small>Variazione Odg</small>
                        </a>
                        <button class="btn btn-md btn-block btn-info">
                            Risultati
                        </button>
                        <br/>
                    </li>
                    <li>
                        <a href="#">
                            <b>Votazione tizio caio</b>
                            <small>Punto 1</small>
                        </a>
                        <button class="btn btn-md btn-block btn-warning" data-toggle="modal"
                                data-whatever="@getbootstrap"
                                data-target="#getbootstrapTemplate">
                            Apri votazione
                        </button>
                        <br/>
                    </li>
                </ul>
            </div>
            <a id="seduta_startstop_button" class="btn btn-danger btn-lg btn-block"
               data-toggle="modal"
               data-whatever="@tega"
               data-target="#tegaTemplate"><i
                        class="fa fa-plus"></i> Crea
                votazione</a>
        </div>
    </div>

</div>


<div class="modal fade" id="tegaTemplate" tabindex="-1" role="dialog"
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
                        <label for="recipient-name" class="control-label">Recipient:</label>
                        <input type="text" class="form-control" name="name" id="recipient-name">
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="control-label">Message:</label>
                        <textarea class="form-control" name="body" id="message-text"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Send message</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="getbootstrapTemplate" tabindex="-1" role="dialog"
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
                        <label for="message-text" class="control-label">Message:</label>
                        <textarea class="form-control" name="body" id="message-text"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Send message</button>
            </div>
        </div>
    </div>
</div>