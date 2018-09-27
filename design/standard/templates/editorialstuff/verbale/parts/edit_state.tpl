{def $allowed = array()}
{foreach $post.states as $key => $state}
    {if $post.object.allowed_assign_state_id_list|contains($state.id)}
    {set $allowed = $allowed|append( hash( 'identifier', $key, 'state',  $state ) )}
    {/if}
{/foreach}
<select class="inline_edit_state" name="inline_edit_state">
    {if $allowed|count()}
    {foreach $allowed as $state}
        <option value="{$state.state.id}" {if $post.current_state.id|eq($state.state.id)} selected="selected"{/if} data-href="{concat('editorialstuff/state_assign/', $post.factory_identifier, '/', $state.identifier, "/", $post.object.id )|ezurl(no)}">{$state.state.current_translation.name|wash}</option>
    {/foreach}
    {/if}
</select>
