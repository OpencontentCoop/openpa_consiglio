{set-block scope=root variable=subject}Convocazione seduta di {$seduta.competenza}{/set-block}

Gentile  {$user.contentobject.name|wash()},<br />
Ti comunichiamo che la prossima seduta del {$seduta.competenza} è fissata per il giorno <strong>{$seduta.data_ora|l10n(date)} alle ore {$seduta.data_ora|l10n(shorttime)}</strong> 
{if $seduta.object|has_attribute('luogo')}
presso {$seduta.object|attribute('luogo').content}
{/if}

{if $seduta.object|has_attribute('convocazione')}
{def $attribute = $seduta.object|attribute('convocazione')}
<p>
La convocazione con l’Ordine del giorno è disponibile cliccando qui:
	<a href="{social_pagedata('consiglio').site_url}/{concat("content/download/",$attribute.contentobject_id,"/",$attribute.id,"/file/",$attribute.content.original_filename)}">Download convocazione</a>
</p>
{undef $attribute}
{/if}
<hr />
<p>
Ricordiamo che per una visione puntale dell'ordine del giorno è necessario accedere all’area riservata del sistema informatico realizzato a supporto dell’attività disponibile all’indirizzo 
<a href="{social_pagedata('consiglio').site_url}/{$seduta.editorial_url}">{social_pagedata('consiglio').site_url}</a>
</p>

