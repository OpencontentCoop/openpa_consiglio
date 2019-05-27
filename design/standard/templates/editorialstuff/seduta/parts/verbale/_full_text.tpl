<h3 class="text-center" align="center">{$verbale.numero}</h3>
<p>{$verbale.intro}</p>

<h3 class="text-center" align="center">ORDINE DEL GIORNO</h3>
<div>{$verbale.odg}</div>

<div>{$verbale.partecipanti}</div>

<div>{$verbale.presidente}</div>

<ol>
    {foreach $verbale_fields as $identifier => $field}
        {if is_numeric($identifier)}
            <li>{$verbale[$identifier]}</li>
        {/if}
    {/foreach}
</ol>

<div>{$verbale.conclusione}</div>