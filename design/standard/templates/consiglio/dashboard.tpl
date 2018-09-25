{def $alerts = fetch(consiglio, alerts)}
{if count($alerts)}
<div class="alert alert-danger">
  {foreach $alerts as $child}
	<div>
	  <small>{$child.object.published|l10n(date)}</small><br />
	  <h2>{$child.name}</h2>	  	  
	</div>
  {/foreach}
</div>
{/if}

{include uri='design:consiglio/dashboard/calendario.tpl'}

<div class="row dashboard">

    <div class="col-sm-6">
        {include uri='design:consiglio/dashboard/materie.tpl'}
        {include uri='design:consiglio/dashboard/ultime.tpl'}        
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Il tuo profilo utente</h3>
            </div>
            <div class="panel-body">
                {include uri='design:consiglio/dashboard/profilo_utente.tpl'}
                {include uri='design:consiglio/dashboard/attivita_utente.tpl'}
                {include uri='design:consiglio/dashboard/avvisi.tpl'}
            </div>
        </div>
    </div>
</div>

<div class="row dashboard">
    <div class="col-sm-6">
        {include uri='design:consiglio/dashboard/eventi.tpl'}        
    </div>
    <div class="col-sm-6">
        {include uri='design:consiglio/dashboard/documenti.tpl'}        
    </div>
</div>