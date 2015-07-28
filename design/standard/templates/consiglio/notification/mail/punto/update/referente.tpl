{set-block scope=root variable=subject}Aggiornato punto di Sua competenza{/set-block}

<p>Con la presente La informo che nell'area riservata del sito Cal.tn.it, in corrispondenza della '{$seduta.object.name|wash()}', è stato aggiornato il punto in materia di '{$seduta.materia|implode( ', ' )}' che risulta di Sua competenza:</p>
<p><strong>{attribute_view_gui attribute=$punto.object.data_map.oggetto}</strong></p>

{if count( $diff )|gt(0)}
    <p>I valori aggiornati sono: {foreach $diff as $attribute}{$attribute.contentclass_attribute_name}{/foreach}</p>
{/if}

{if $punto.can_add_osservazioni}
    <p>Le segnalo che il termine ultimo per la presentazione delle osservazioni è attualmente fissato per il {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}.</p>
{/if}
