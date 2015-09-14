{set-block scope=root variable=subject}Modifica dei referenti{/set-block}
{if is_set( $diff.referente_politico )}
    Il nuovo referente politico è {foreach $diff.referente_politico.new.content.realtion_list as $relation}{fetch( content, object, hash( object_id, $relation.contentobject_id ) ).name|wash} {/foreach}.
{/if}
{if is_set( $diff.referente_tecnico )}
    Il nuovo referente tecnico è {foreach $diff.referente_tecnico.new.content.realtion_list as $relation}{fetch( content, object, hash( object_id, $relation.contentobject_id ) ).name|wash} {/foreach}.
{/if}