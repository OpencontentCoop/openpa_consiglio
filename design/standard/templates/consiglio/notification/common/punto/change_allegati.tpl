<div style="background: #eee; padding: 20px; border: 1ps solid #ccc">
<ul><li>
{if $refer.sostituito}
    <strong>è stato sostituito il documento <em>{$refer.object.name|wash()}</em></strong>
{else}
    <strong>è stato caricato un nuovo documento ({$refer.object.name|wash()})</strong>
{/if}
</li></ul>
</div>