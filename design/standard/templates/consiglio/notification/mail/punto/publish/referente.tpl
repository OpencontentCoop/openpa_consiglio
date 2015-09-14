{set-block scope=root variable=subject}Nuovo punto di Suo competenza{/set-block}

Con la presente La informo che nella <strong>{$seduta.object.name|wash()}</strong> è stato pubblicato il seguente punto in materia di {$punto.materia|implode( ', ' )}, che risulta di Sua competenza:<br><br>
<strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong><br><br>

Il referenti del punto sono:
<ul>
    <li>Referente politico: {attribute_view_gui attribute=$punto.object.data_map.referente_politico}</li>
    <li>Referente tecnico: {attribute_view_gui attribute=$punto.object.data_map.referente_tecnico}</li>
</ul>

<br><br>

{if $punto.can_add_osservazioni}
    Le segnalo che il termine ultimo per la presentazione delle osservazioni è attualmente fissato per il {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}.
{/if}
<br><br>