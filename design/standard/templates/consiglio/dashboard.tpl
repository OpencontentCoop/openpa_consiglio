{def $panels = array(
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
{/foreach}