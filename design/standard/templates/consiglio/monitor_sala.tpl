<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>
<script>
    var SocketUrl = "{openpaini('OpenPAConsiglio','SocketUrl','cal')}"
    var SocketPort = "{openpaini('OpenPAConsiglio','SocketPort','8090')}";
    var CurrentSedutaId = {$seduta.object_id};
    var SedutaDataBaseUrl = "{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}/";
    var VotazioneDataBaseUrl = "{'consiglio/data/votazione'|ezurl(no)}/";
    var ActionBaseUrl = "{concat('consiglio/cruscotto_seduta/',$seduta.object_id)|ezurl(no)}/";
</script>
<script src="{'javascript/monitor_sala.js'|ezdesign(no)}"></script>


<div id="alert-area">
    {if count( $errors )}
        <div class="alert alert-danger">
            {foreach $errors as $error}
                <p>{$error|wash()}</p>
            {/foreach}
        </div>
    {/if}
</div>

<div id="seduta">
    <div class="row">
        <div class="col col-md-12">
            <img class="center-block" height="100" src="{'images/monitor_sala/logo.png'|ezdesign(no)}" />
            <h1 class="text-center">{$seduta.object.name|wash()}</h1>
        </div>
    </div>
</div>

<hr />

{def $registro_presenze = $seduta.registro_presenze}
<div id="presenze">
    <div class="row">        
	  {foreach $seduta.partecipanti as $partecipante}
		  <div class="col-xs-2 user_presenza user-{$partecipante.object_id}"
				  {if $registro_presenze.hash_user_id[$partecipante.object_id]|not} style="opacity: .4"{/if}>
			  {content_view_gui content_object=$partecipante.object view="politico_box"}
		  </div>
		  {delimiter modulo=6}</div><div class="row">{/delimiter}
	  {/foreach}        
    </div>
</div>
{undef $registro_presenze}

<div id="text" style="display: none">
    <div class="row">
        <div class="col col-md-12">
            <h1 class="text-center"></h1>
        </div>
    </div>

    <div class="row alert alert-warning" style="display: none">
        <div class="col col-md-12">
            <h1 class="text-center"></h1>
        </div>
    </div>
</div>
