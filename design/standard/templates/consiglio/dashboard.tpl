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

<div class="row dashboard">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                {include uri='design:consiglio/dashboard/calendario.tpl'}
            </div>
        </div>
    </div>
</div>

<div class="row dashboard">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Le tue materie preferite</h3>
            </div>
            <div class="panel-body">
                {include uri='design:consiglio/dashboard/materie.tpl'}
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Le tue ultime osservazioni</h3>
            </div>
            <div class="panel-body">
                {include uri='design:consiglio/dashboard/ultime.tpl'}
            </div>
        </div>
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
