<h3 class="text-center" align="center">{$verbale.numero}</h3>
<p>{$verbale.intro}</p>

<h3 class="text-center" align="center">ORDINE DEL GIORNO</h3>
<p>{$verbale.odg}</p>

<p>{$verbale.partecipanti}</p>

<p>{$verbale.presidente}</p>

<ol>
    {foreach $verbale_fields as $identifier => $field}
        {if is_numeric($identifier)}
            <li>{$verbale[$identifier]}</li>
        {/if}
    {/foreach}
</ol>

<p>{$verbale.conclusione}</p>