<div style="background: #eee; padding: 20px; border: 1ps solid #ccc">
{if $refer.sostituito}
    <strong>Le notifichiamo che il documento <em>{$refer.object.name|wash()}</em> è stato sostituito</strong>
{else}
    <strong>Le notifichiamo che è stata caricato un nuovo documento: {$refer.object.name|wash()}</strong>
{/if}
</div>