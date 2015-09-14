{set-block scope=root variable=subject}Punto all'ordine del giorno{/set-block}

Con la presente La informo che nell'area riservata del sito Cal.tn.it, in corrispondenza della '{$punto.seduta.object.name|wash()}', è pubblicato il seguente punto in materia di '{$punto.materia|implode( ', ' )}':<br><br>
<strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong><br><br>

Il referenti del punto sono:
<ul>
    <li>Referente politico: {attribute_view_gui attribute=$punto.object.data_map.referente_politico}</li>
    <li>Referente tecnico: {attribute_view_gui attribute=$punto.object.data_map.referente_tecnico}</li>
</ul>

{if $punto.can_add_osservazioni}
    Le segnalo che il termine ultimo per la presentazione delle osservazioni è attualmente fissato per il {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}.
{/if}
<br><br>

<!--ITEMS DATA-->