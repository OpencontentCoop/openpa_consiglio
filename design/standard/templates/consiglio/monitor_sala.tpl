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

{def $currentPunto = false()}
{foreach $seduta.odg as $index => $punto}{if $punto.current_state.identifier|eq('in_progress')}
{set $currentPunto = concat( '<strong>Punto ', $punto.object.data_map.n_punto.content|wash(), '</strong><br />', $punto.object.data_map.oggetto.content|wash() )}
{/if}{/foreach}

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
          <div class="col col-md-3 text-center">
            <img class="center-block" height="100" src="{'images/monitor_sala/logo.png'|ezdesign(no)}" />
          </div>
          <div class="col col-md-9 text-center">
            <h1 class="text-center"><strong>{$seduta.object.name|wash()}</strong></h1>
          </div>
        </div>
    </div>
</div>

<hr />

{def $registro_presenze = $seduta.registro_presenze}
<div id="presenze" {if $currentPunto}style="display:none"{/if}>
    <div class="row">
    {def $partecipanti = $seduta.partecipanti}
    {def $col = 2 $modulo = 6}
    {if $partecipanti|count()|gt(18)}
      {set $col = 1 $modulo = 12}
    {/if}
	  {foreach $seduta.partecipanti as $partecipante}
		  <div class="col-xs-{$col} user_presenza user-{$partecipante.object_id}"
				  {if $registro_presenze.hash_user_id[$partecipante.object_id]|not} style="opacity: .4"{/if}>
			  {content_view_gui content_object=$partecipante.object view="politico_box"}
		  </div>
		  {delimiter modulo=$modulo}</div><div class="row">{/delimiter}
	  {/foreach}        
    </div>
</div>
{undef $registro_presenze}

<div id="text" {if $currentPunto|not()}style="display: none"{/if}>
    <div class="row data">
        <div class="col col-md-12">
            <h1 class="text-center">
              {$currentPunto}
            </h1>
        </div>
    </div>

    <div class="row alert alert-warning" style="display: none">
        <div class="col col-md-12">
            <h1 class="text-center"></h1>
        </div>
    </div>
</div>


<div class="row">
    <div class="col col-md-8 col-md-offset-2">
        <div id="detail" style="font-size:2em"></div>
    </div>
</div>
