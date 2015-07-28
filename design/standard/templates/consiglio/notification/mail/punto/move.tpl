{set-block scope=root variable=subject}Spostamento punto all'ordine del giorno{/set-block}

<p>Con la presente La informo che nell'area riservata del sito Cal.tn.it, in corrispondenza della '{$seduta.object.name|wash()}', è stato insrito il punto in materia di '{$seduta.materia|implode( ', ' )}':</p>
<p><strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong></p>

{attribute_view_gui attribute=$punto.object.data_map.alert}

{if $punto.can_add_osservazioni}
    <p>Le segnalo che il termine ultimo per la presentazione delle osservazioni è attualmente fissato per il {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}.</p>
{/if}
