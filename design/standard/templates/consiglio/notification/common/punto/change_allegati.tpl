<div style="background: #eee; padding: 20px; border: 1ps solid #ccc">
{if $refer.sostituito}
    <strong>Sostituito documento <em>{$refer.object.name|wash()}</em></strong>
{else}
    <strong>Inserimento nuovo documento: {$refer.object.name|wash()}</strong>
{/if}
</div>