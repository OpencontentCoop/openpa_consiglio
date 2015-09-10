{def $alerts_container = openpaini( 'OpenPAConsiglio', 'DashboardAlertsContainerNode', false() )}
{if $alerts_container}
{def $alert_container_node = fetch( content, node, hash( node_id, $alerts_container ) )}
{if $alert_container_node.children_count}
<div class="alert alert-danger">
  {foreach $alert_container_node.children as $child}
	<div>
	  <small>{$child.object.published|l10n(date)}</small><br />
	  <h2>{$child.name}</h2>	  	  
	</div>
  {/foreach}
</div>
{/if}
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

{*def $panels = array(
hash( 'name', 'Ultimi contenuti modificati', 'identifier', 'ultime' ),
hash( 'name', 'Avvisi', 'identifier', 'avvisi' ),
hash( 'name', 'Materie di interesse', 'identifier', 'materie' ),
hash( 'name', 'Calendario sedute', 'identifier', 'calendario' ),
hash( 'name', 'Le mie attività', 'identifier', 'attivita_utente' ),
hash( 'name', 'Il mio profilo', 'identifier', 'profilo_utente' )
)}
{def $i = 0}
{foreach $panels as $panel}
    {if $i|eq(0)}
        <div class="row dashboard">
    {/if}
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$panel.name|wash()}</h3>
            </div>
            <div class="panel-body" style="height: 250px; overflow-y: auto">
                {include uri=concat( 'design:consiglio/dashboard/', $panel.identifier, '.tpl' )}
            </div>
        </div>
    </div>
    {if eq(sum($i,1)|mod(2),0)}
        </div>
        <div class="row dashboard">
    {/if}
    {if $i|eq(count($panels)|sub(1))}
        </div>
    {/if}
    {set $i = $i|sum(1)}
{/foreach*}
