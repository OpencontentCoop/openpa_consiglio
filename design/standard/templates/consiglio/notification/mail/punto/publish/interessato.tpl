{set-block scope=root variable=subject}Pubblicazione nuovo punto in {$punto.seduta.object.name|wash()}{/set-block}

Gentile  {$user.contentobject.name|wash()},<br />

come richiesto si segnala che, nell’area riservata del sistema informatico disponibile all’indirizzo {social_pagedata('consiglio').site_url}, in corrispondenza della {$punto.seduta.object.name|wash()}, è stato pubblicato il seguente punto in materia di <em>{$punto.materia|implode( ', ' )}</em>:
<strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong>.

<p>Per agevolare l’attività istruttoria si segnalano ulteriori informazioni di potenziale interesse, relative al punto citato:</p>
<ul>
    <li>Referente istituzionale: {$punto.referente_politico|wash()}</li>
    {if $punto.referente_tecnico}
        <li>Referente tecnico: {$punto.referente_tecnico|wash()}</li>
    {/if}
    {if $punto.can_add_osservazioni}<li>il termine ultimo per la presentazione delle osservazioni è: {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}.</li>{/if}
</ul>

<p>
    nonché ulteriori link di accesso rapido all’area riservata:<br />
    <a href="{social_pagedata('consiglio').site_url}/{$punto.editorial_url}">Dettagli del punto</a><br />
    <a href="{social_pagedata('consiglio').site_url}/{$punto.editorial_url}#tab_documenti">Elenco documenti allegati al punto</a><br />
    <a href="{social_pagedata('consiglio').site_url}/{$punto.editorial_url}#tab_osservazioni">Elenco osservazioni</a><br />
    <a href="{social_pagedata('consiglio').site_url}/{$punto.seduta.editorial_url}">Ordine del giorno della seduta</a>
</p>
