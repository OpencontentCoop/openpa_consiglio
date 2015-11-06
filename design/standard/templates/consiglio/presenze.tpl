
<h2>Rilevazioni</h2>{$seduta.data_ora}
<table class="table table-striped">
{foreach $detections as $detection}
    {if $detection.timestamp|gt($seduta.data_ora)}
    <tr class="{if $detection.is_in|eq(1)}success{else}danger{/if}">
        <td>{$detection.time} {$detection.timestamp}</td>
        <td>
            {if $detection.label|eq('manual')} Intervento dell'segretario
            {elseif $detection.label|eq('beacons')} Rilevazione automatica
            {elseif $detection.label|eq('checkin')} Intervento dell'utente
            {/if}
        </td>
        <td>
            {if $detection.in_out|eq(1)} Presente
            {else} Assente
            {/if}
        </td>
    </tr>
    {/if}
{/foreach}
</table>