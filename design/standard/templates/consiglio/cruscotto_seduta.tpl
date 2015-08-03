<div id="alert_area" style="position: relative;">
    <div style="display:none; position: absolute; right: 0;" id="loading"><i class="fa fa-gear fa-spin fa-2x"></i></div>
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
        {include uri="design:consiglio/cruscotto_seduta/presenze_button.tpl" post=$seduta}
    </span>
</div>

<hr/>

{def $registro_presenze = $seduta.registro_presenze}

<div class="row">
    <div id="sidebar-area" class="col col-md-3">
        <div class="widget">

            <div class="widget_title" id="odg_title">
                <h3>
                    <a href="#" data-verbale_id="{$seduta.object.id}">Ordine del giorno</a>
                </h3>
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
                {foreach $seduta.partecipanti as $partecipante}
                    <div class="col-xs-6">
                        <p class="user_voto user-{$partecipante.object_id}" {if $registro_presenze.hash_user_id[$partecipante.object_id]|not} style="opacity: .4"{/if}>
                          <span class="text-success voto" style="display: none"><i class="fa fa-certificate"></i></span>														
						  {content_view_gui content_object=$partecipante.object view="politico_line"}
                          <span class="user_buttons pull-right">
                            {*<a class="btn btn-success btn-xs" data-action="markVotoValid" data-user_id="{$partecipante.object_id}"><i class="fa fa-check"></i></a>*}														
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
<div class="modal fade" id="presenzeTemplate" tabindex="-1" role="dialog" aria-labelledby="previewLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="previewLabel">Presenze</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {foreach $seduta.partecipanti as $partecipante}
                        <div class="col-xs-2" style="position:relative;">                          					  
                          <div class="user_presenza user-{$partecipante.object_id}">
							<div style="position: absolute;top:-5px;left:10px;" class="">
							  <p class="btn btn-default btn-xs type checkin" style="display: none"><i class="fa fa-check-circle"></i></p>
							  <p class="btn btn-default btn-xs type beacons" style="display: none"><i class="fa fa-wifi"></i></p>
							  <p class="btn btn-default btn-xs type manual" style="display: none"><i class="fa fa-thumbs-up"></i></p>
							</div>
							<div class="name" {if $registro_presenze.hash_user_id[$partecipante.object_id]|not} style="opacity: .4"{/if}>
							  {content_view_gui content_object=$partecipante.object view="politico_box"}
							</div>
                          </div>
						  <div class="user_buttons" style="position: absolute; top: -5px; right: 10px;">
                            <a class="btn btn-default btn-xs" data-action="markPresente" data-user_id="{$partecipante.object_id}" title="Segna presente"><i class="fa fa-check"></i></a>							
                            <a class="btn btn-default btn-xs" data-action="markAssente" data-user_id="{$partecipante.object_id}" title="Segna presente"><i class="fa fa-close"></i></a>
                          </div>	
                        </div>
                        {delimiter modulo=6}</div><div class="row">{/delimiter}
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
{undef $registro_presenze}

{ezscript_require( array( 'ezjsc::jquery' ) )}
<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>

<script>
    var SocketUrl = "{openpaini('OpenPAConsiglio','SocketUrl','cal')}"
    var SocketPort = "{openpaini('OpenPAConsiglio','SocketPort','8090')}";
    var CurrentSedutaId = {$seduta.object_id};
    var SedutaDataBaseUrl = "{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}/";
    var VotazioneDataBaseUrl = "{'consiglio/data/votazione'|ezurl(no)}/";
    var ActionBaseUrl = "{concat('consiglio/cruscotto_seduta/',$seduta.object_id)|ezurl(no)}/";
</script>

<script src="{'javascript/cruscotto_seduta.js'|ezdesign(no)}"></script>
