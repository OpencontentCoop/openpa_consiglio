<div id="alert-area">
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
    <a id="seduta_startstop_button" class="btn btn-danger btn-lg">Concludi seduta</a>
    <a id="punto_startstop_button" class="btn btn-success btn-lg">Inizia trattazione punto 1</a>
    <a id="presenze_button" class="btn btn-info btn-lg pull-right">Presenti</a>
</div>

<hr/>

<div class="row">
    <div id="sidebar-area" class="col col-md-3">
        <div class="widget">

            <div class="widget_title">
                <h3>Ordine del giorno</h3>
            </div>
            <div class="widget_content">
                <ul class="side_menu">

                    {foreach $seduta.odg as $index => $punto}
                        <li {if $index|eq(5)}class="active"{/if}>

                            <a href="#" {if $index|lt(5)}style="text-decoration: line-through"{/if}>
                                {$punto.object.data_map.oggetto.content|wash()}
                            </a></li>
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>

    <div id="content-area" class="col col-md-6">
        <textarea class="form-control" rows="20"></textarea>
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

{literal}
    <script>
        var forms = [
            {
                name: '@tega',
                fields: ['name', 'body'],
                action: 'string'
            },
            {
                name: '@getbootstrap',
                fields: ['body'],
                action: 'strong'
            }
        ];
        $('.modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var current = button.data('whatever') // Extract info from data-* attributes
            var modal = $(this);
            $.get('test.php', {current: current}, function (data) {
                var currentSettings = {fields: []};
                modal.data('current', current);
                $.each(forms, function (i, v) {
                    if (v.name == current) currentSettings = v;
                });
                modal.data('currentSettings', currentSettings);
                modal.find('form')[0].reset();
                modal.find('.modal-title').text('New message to ' + current);
            });
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
                $.post('test.php', values, function (data) {
                    currentModal.modal('hide');
                });
            }
        });


    </script>
{/literal}