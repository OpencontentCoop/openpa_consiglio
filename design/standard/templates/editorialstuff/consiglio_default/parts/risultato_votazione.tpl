{def $post_result = $post.result}

<h3>
    {if $post.current_state.identifier|eq('closed')}Esito della votazione{else}Votazione{/if} {attribute_view_gui attribute=$post.object.data_map.short_text}
    <small>{attribute_view_gui attribute=$post.object.data_map.type}</small>
</h3>

<div class="alert alert-info">{$post.type_description}</div>

<p class="text">{attribute_view_gui attribute=$post.object.data_map.text}</p>

{include uri=concat( 'design:editorialstuff/consiglio_default/parts/risultato_votazione/', $post.result_template )}

<p>
    {if $post.current_state.identifier|eq('pending')}
        <a href="#" class="remove_votazione btn btn-danger btn-xs"
           data-remove_votazione="{$post.object_id}"
           data-remove_action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/removeVotazione')|ezurl(no)}">
            <i class="fa fa-trash"></i> Elimina
        </a>
    {/if}
    {if $post.current_state.identifier|eq('closed')}
        <a class="btn btn-info btn-xs launch_monitor_votazione" data-action_url="{concat('consiglio/cruscotto_seduta/',$post.seduta_id,'/launchMonitorVotazione/', $post.object_id)|ezurl(no)}" href="#"><i class="fa fa-desktop"></i> Mostra risultati su monitor sala</a>
    {/if}
</p>

{undef $post_result}
