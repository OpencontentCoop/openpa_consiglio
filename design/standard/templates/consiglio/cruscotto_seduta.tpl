{def $registro_presenze = $seduta.registro_presenze}
{def $count_partecipanti = count($seduta.partecipanti)}
<script>
    var SocketUrl = "{openpaini('OpenPAConsiglio','SocketUrl','cal')}"
    var SocketPort = "{openpaini('OpenPAConsiglio','SocketPort','8090')}";
    var CurrentSedutaId = {$seduta.object_id};
    var SedutaDataBaseUrl = "{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}/";
    var VotazioneDataBaseUrl = "{'consiglio/data/votazione'|ezurl(no)}/";
    var ActionBaseUrl = "{concat('consiglio/cruscotto_seduta/',$seduta.object_id)|ezurl(no)}/";
</script>


<div id="timer" style="display: none;"><strong><span class="minutes">00</span>:<span class="seconds">00</span></strong></div>
<div style="display:none;" id="loading"><i class="fa fa-gear fa-spin fa-2x"></i></div>

<div id="alert_area" style="position: absolute; z-index: 1000; width: 96%; left: 2%; top: 4%;">
    {if count( $errors )}
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {foreach $errors as $error}
                <p>{$error|wash()}</p>
            {/foreach}
        </div>
    {/if}
</div>


<div id="top" class="split" data-spy="affix" data-offset-top="80">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="title">
                <span class="seduta_startstop_button"
                      data-load_url="{concat('consiglio/data/seduta/',$seduta.object_id, '/::consiglio:cruscotto_seduta:seduta_startstop_button')|ezurl(no)}">
                    {include uri="design:consiglio/cruscotto_seduta/seduta_startstop_button.tpl" post=$seduta}
                </span>
                <a class='show-verbale' href="#" data-verbale_id="{$seduta.object.id}">
                    {$seduta.object.name} <small>ore {attribute_view_gui attribute=$seduta.object.data_map.orario}</small>
                </a>
            </h1>
        </div>
    </div>
    <div class="row bg-primary hidden-xs hidden-sm">
        <div class="col-md-7">Ordine del giorno</div>
        <div class="col-md-2">
            Votazioni
            <a class="btn btn-warning btn-xs"
               data-toggle="modal"
               data-target="#creaVotazioneTemplate"
               data-modal_configuration="creaVotazione"
               data-action_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/creaVotazione')|ezurl(no)}">
                <i class="fa fa-plus"></i> Crea
            </a>
        </div>
        <div class="col-md-3 no-padding">
            Presenze
            <span class="label label-default">
                <span class="totale-presenze">{$registro_presenze.in}</span>/{$count_partecipanti}
            </span>
            <a class="btn btn-info btn-xs launch_monitor_presenze" data-action_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/launchMonitorPresenze')|ezurl(no)}" href="#"><i class="fa fa-desktop"></i></a> 
        </div>
    </div>
</div>


<div id="body" class="row">
    <div id="odg-col" class="col-md-3">
        <h2 class="visible-xs visible-sm">Ordine del giorno</h2>
        <div id="odg_list" data-load_url="{concat('consiglio/data/seduta/',$seduta.object_id, '/:consiglio:cruscotto_seduta:odg_list')|ezurl(no)}">
            {include uri="design:consiglio/cruscotto_seduta/odg_list.tpl" post=$seduta}
        </div>
    </div>
    <div id="verbale-col" class="col-md-4">
        <h2 class="visible-xs visible-sm">Verbale</h2>
        <div id="verbale" data-save_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/saveVerbale')|ezurl(no)}"
             data-load_url="{concat('consiglio/data/seduta/',$seduta.object_id, '/:consiglio:cruscotto_seduta:verbale')|ezurl(no)}">
            {include uri="design:consiglio/cruscotto_seduta/verbale.tpl" post=$seduta}
        </div>
    </div>
    <div id="votazioni-col" class="col-md-2">
        <h2 class="visible-xs visible-sm">
            Votazioni
                <a class="btn btn-warning btn-xs"
                   data-toggle="modal"
                   data-target="#creaVotazioneTemplate"
                   data-modal_configuration="creaVotazione"
                   data-action_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/creaVotazione')|ezurl(no)}">
                    <i class="fa fa-plus"></i> Crea
                </a>
        </h2>
        <div id="votazioni" data-load_url="{concat('consiglio/data/seduta/',$seduta.object_id, '/:consiglio:cruscotto_seduta:votazioni')|ezurl(no)}">
            {include uri="design:consiglio/cruscotto_seduta/votazioni.tpl" post=$seduta}
        </div>
    </div>
    <div id="presenze-col" class="col-md-3 no-padding">
        <h2 class="visible-xs visible-sm">
            Presenze
            <span class="label label-default">
                <span class="totale-presenze">{$registro_presenze.in}</span>/{$count_partecipanti}
            </span>
        </h2>
        <div id="presenze" data-load_url="{concat('consiglio/data/seduta/',$seduta.object_id, '/:consiglio:cruscotto_seduta:presenze')|ezurl(no)}">
            {include uri="design:consiglio/cruscotto_seduta/presenze.tpl" post=$seduta}
        </div>
    </div>
</div>

{include uri="design:consiglio/cruscotto_seduta/modals.tpl"}

{ezscript_require( array( 'ezjsc::jquery', 'jquery.confirm.min.js' ) )}
<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>
<script src="{'javascript/cruscotto_seduta_tools.js'|ezdesign(no)}?_={currentdate()}"></script>
<script src="{'javascript/cruscotto_seduta.js'|ezdesign(no)}?_={currentdate()}"></script>
{ezcss_require( array( 'cruscotto_seduta.css' ) )}
