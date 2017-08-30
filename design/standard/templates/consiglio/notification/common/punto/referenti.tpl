<p>
    Le segnalo, infine, ulteriori informazioni relative al punto citato e di Suo potenziale interesse:
<ul>
    <li>Referente istituzionale: {$punto.referente_politico|wash()}</li>
    {if $punto.referente_tecnico}
        <li>Referente tecnico: {$punto.referente_tecnico|wash()}</li>
    {/if}
</ul>
