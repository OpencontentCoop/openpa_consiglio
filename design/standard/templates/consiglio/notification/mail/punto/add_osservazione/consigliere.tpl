{set-block scope=root variable=subject}Segnalazione inserimento osservazione al punto a punto previsto in seduta {$punto.seduta.object.name|wash()}{/set-block}

Egregio/Gentile {$user.contentobject.name|wash()},<br />
come richiesto si segnalano le modifiche apportate al punto in materia di <em>{$punto.materia|implode( ', ' )}</em> programmato nella <strong>{$punto.seduta.object.name|wash()}</strong> e concernente "<strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong>":

{include uri='design:consiglio/notification/common/punto/add_osservazione.tpl'}

<p>
    Per l’accesso rapido all’area riservata è sempre possibile riferirsi ai link:<br />
    <a href="http://{social_pagedata('consiglio').site_url}/{$punto.editorial_url}">Dettagli del punto</a><br />
    <a href="http://{social_pagedata('consiglio').site_url}/{$punto.editorial_url}#tab_documenti">Elenco documenti allegati al punto</a><br />
    <a href="http://{social_pagedata('consiglio').site_url}/{$punto.editorial_url}#tab_osservazioni">Elenco osservazioni</a><br />
    <a href="http://{social_pagedata('consiglio').site_url}/{$punto.seduta.editorial_url}">Ordine del giorno della seduta</a>
</p>
