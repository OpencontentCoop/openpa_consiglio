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
                        <label for="message-point" class="control-label">La votazione riguarda il punto:</label>
                        <select name="puntoId" id="message-point">
                            <option>Nessuno</option>
                            {foreach $seduta.odg as $punto}
                                <option value="{$punto.object_id}" data-text="{$punto.object.data_map.oggetto.content|wash()}">{$punto.object.data_map.n_punto.content}</option>
                            {/foreach}
                        </select>
                        <a id="popolaTestoVotazione" class="btn btn-xs btn-default" style="display: none" href="#">Popola il testo con l'oggetto del punto selezionato</a>
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

<div class="modal fade" id="risultatiVotazioneTemplate" role="dialog" aria-labelledby="previewLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="previewLabel"></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
{*
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

                <div id="votazione_in_progress">
                    <div class="row">
                        {foreach $partecipanti as $partecipante}
                        <div class="col-xs-6">
                            <p class="user_voto user-{$partecipante.object_id}" {if $registro_presenze.hash_user_id[$partecipante.object_id]|not} style="opacity: .4"{/if}>
                                <span class="text-success voto" style="display: none"><i class="fa fa-certificate"></i></span>
                                {content_view_gui content_object=$partecipante.object view="politico_line"}
                                <span class="user_buttons pull-right">
                                    <a style="display:none" class="btn btn-danger btn-xs mark_invalid" data-action="markVotoInvalid" data-user_id="{$partecipante.object_id}">Non presente<br />annulla voto</a>
                          </span>
                            </p>
                        </div>
                        {delimiter modulo=6}</div><div class="row">{/delimiter}
                        {/foreach}
                    </div>
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
*}
{literal}
<script>
    var Modals = [
        {
            name: 'creaVotazione',
            title: 'Nuova votazione',
            fields: ['shortText', 'text', 'puntoId'],
            resetForm: true,
            onShow: null,
            onSent: function (data, modal) {
                Votazioni.reload();
                modal.modal('hide');
            }
        },
        {
            name: 'infoVotazione',
            title: 'Informazioni votazione',
            onShow: function (modal, button) {
                modal.find('.modal-body').html('');
                modal.find('.modal-body').load( button.data('load_url') );
            }
        },
        {
            name: 'risultatiVotazione',
            title: 'Risultati votazione',
            onShow: function (modal, button) {
                modal.find('.modal-body').html('');
                modal.find('.modal-body').load( button.data('load_url') );
            }
        }
    ];
</script>
{/literal}