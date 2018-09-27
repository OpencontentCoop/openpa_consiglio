{def $registro_presenze = $seduta.registro_presenze.hash_user_id}
{def $presenti = array()}
{foreach $seduta.partecipanti as $politico}
{if $seduta.current_state.identifier|eq( 'closed' )}
    {if $seduta.percentuale_presenza[$politico.object.id]|gt(25)}
        {set $presenti = $presenti|append($politico.object.name)}
    {/if}
{else}
    {if and(is_set($registro_presenze[$politico.object.id]), $registro_presenze[$politico.object.id]|eq(1))}
        {set $presenti = $presenti|append($politico.object.name)}
    {/if}
{/if}
{/foreach}

<p>Partecipano alla riunione: </p>
{foreach $presenti as $politico}{$politico|wash()}{delimiter}, {/delimiter}{/foreach}

<p>Assistono inoltre come invitati permanenti: </p>

{undef $registro_presenze $presenti}