{def $registro_presenze = $seduta.registro_presenze}
{def $count_partecipanti = count($seduta.partecipanti)}
<script>
    var SocketUrl = "{fetch(consiglio, socket_info).url}";
    var SocketPort = "{fetch(consiglio, socket_info).port}";
    var CurrentSedutaId = {$seduta.object_id};
    var SedutaDataBaseUrl = "{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}/";
    var VotazioneDataBaseUrl = "{'consiglio/data/votazione'|ezurl(no)}/";
    var ActionBaseUrl = "{concat('consiglio/cruscotto_seduta/',$seduta.object_id)|ezurl(no)}/";
</script>


<div id="timer" style="display: none;"><strong><span class="minutes">00</span>:<span class="seconds">00</span></strong></div>
<div style="display:none;" id="loading"><i class="fa fa-gear fa-spin fa-2x"></i></div>

<div id="alert_area" style="position: absolute;z-index: 1000;width: 50%;left: 25%;top: 50%;box-shadow: 0 5px 15px rgba(0,0,0,0.5);font-size: 1.2em">
    {if count( $errors )}
        <div class="alert alert-danger alert-dismissible" style="margin-bottom: 0;font-size: 2em">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {foreach $errors as $error}
                <p><strong>{$error|wash()}</strong></p>
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
        <div class="col-md-{if $enable_votazione}7{else}8{/if}">Ordine del giorno</div>
        <div class="col-md-2"{if $enable_votazione|not()} style="display: none" {/if}>
            Votazioni
            <a class="btn btn-warning btn-xs"
               data-toggle="modal"
               data-target="#creaVotazioneTemplate"
               data-modal_configuration="creaVotazione"
               data-action_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/creaVotazione')|ezurl(no)}">
                <i class="fa fa-plus"></i> Crea
            </a>
        </div>
        <div class="col-md-{if $enable_votazione}3{else}4{/if} no-padding">
            Presenze
            <span class="label label-default">
                <span class="totale-presenze">{$registro_presenze.in}</span>/{$count_partecipanti}
            </span>
            <a class="btn btn-info btn-xs launch_monitor_presenze" data-action_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/launchMonitorPresenze')|ezurl(no)}" href="#"><i class="fa fa-desktop"></i></a>
            <span id="totale-votanti" class="label label-warning" style="display: none">0</span>
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
    <div id="verbale-col" class="col-md-{if $enable_votazione}4{else}5{/if}">
        <h2 class="visible-xs visible-sm">Verbale</h2>
        <div id="verbale" data-save_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/saveVerbale')|ezurl(no)}"
             data-load_url="{concat('consiglio/data/seduta/',$seduta.object_id, '/:consiglio:cruscotto_seduta:verbale')|ezurl(no)}">
            {include uri="design:consiglio/cruscotto_seduta/verbale.tpl" post=$seduta}
        </div>
    </div>
    <div id="votazioni-col" class="col-md-2"{if $enable_votazione|not()} style="display: none" {/if}>
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
    <div id="presenze-col" class="col-md-{if $enable_votazione}3{else}4{/if} no-padding">
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
