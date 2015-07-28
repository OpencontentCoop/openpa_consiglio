{set-block scope=root variable=subject}Aggiornato punto di Suo interesse{/set-block}

Con la presente La informo che nell'area riservata del sito Cal.tn.it, in corrispondenza della '{$seduta.object.name|wash()}', è stato aggiornato il seguente punto in materia di '{$seduta.materia|implode( ', ' )}',
che risulta di Suo potenziale interesse:<br><br>
<strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong><br>
<br>
{if $punto.can_add_osservazioni}
    Le segnalo che il termine ultimo per la presentazione delle osservazioni è attualmente fissato per il {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}.
{/if}
<br><br>