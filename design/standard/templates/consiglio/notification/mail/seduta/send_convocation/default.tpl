{set-block scope=root variable=subject}Convocazione seduta di {$seduta.competenza}{/set-block}

Gentile  {$user.contentobject.name|wash()},<br />
la presente per informarla che la seduta di {$seduta.competenza} è fissata per il giorno <strong>{$seduta.data_ora|l10n(date)} alle ore {$seduta.data_ora|l10n(shorttime)}</strong>

{if $seduta.object|has_attribute('convocazione')}
{def $attribute = $seduta.object|attribute('convocazione')}
<p>
E' possibile effettuare il download del file pdf della convazione al seguente link:
	<a href="{social_pagedata('consiglio').site_url}/{concat("content/download/",$attribute.contentobject_id,"/",$attribute.id,"/file/",$attribute.content.original_filename)}">Download convocazione</a>
</p>
{undef $attribute}
{/if}

<p>
Si ricorda che per una visione puntale dell'ordine del giorno è necessario accedere all’area riservata del sistema informatico realizzato a supporto dell’attività disponibile all’indirizzo {social_pagedata('consiglio').site_url}
</p>

<p>
Per l’accesso rapido all’area riservata è sempre possibile riferirsi al seguente link:
    <a href="{social_pagedata('consiglio').site_url}/{$seduta.editorial_url}">Ordine del giorno della seduta</a>
</p>
