{if $post.current_state.identifier|eq('in_progress')}
    <a class="btn btn-danger btn-lg seduta_start_stop"
       data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/stopSeduta')|ezurl(no)}"
       data-add_to_verbale="Fine trattazione">Concludi seduta</a>
{elseif $post.current_state.identifier|eq('sent')}
    <a class="btn btn-success btn-lg seduta_start_stop"
       data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/startSeduta')|ezurl(no)}"
       data-add_to_verbale="Inizio trattazione">Inizia seduta</a>
{else}
    <a class="btn btn-default btn-lg disabled">Seduta {$post.current_state.current_translation.name|wash()}</a>
{/if}
