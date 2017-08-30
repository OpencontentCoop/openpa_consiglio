{set-block scope=root variable=subject}Segnalazione variazione data seduta di {$seduta.competenza} prevista per {$old_date|l10n(date)}{/set-block}

Gentile  {$user.contentobject.name|wash()},<br />
si segnala che la seduta di {$seduta.competenza} programmata per il giorno {$old_date|l10n(date)} alle ore {$old_date|l10n(shorttime)} è stata {if $old_date|gt($seduta.data_ora)}anticipata{else}posticipata{/if} a <strong>{$seduta.data_ora|l10n(date)} alle ore {$seduta.data_ora|l10n(shorttime)}</strong>

<p>
Si ricorda che per una visione puntale dell'ordine del giorno è necessario accedere all’area riservata del sistema informatico realizzato a supporto dell’attività disponibile all’indirizzo {social_pagedata('consiglio').site_url}</p>

<p>
Per l’accesso rapido all’area riservata è sempre possibile riferirsi al seguente link:
    <a href="http://{social_pagedata('consiglio').site_url}/{$seduta.editorial_url}">Ordine del giorno della seduta</a>
</p>
