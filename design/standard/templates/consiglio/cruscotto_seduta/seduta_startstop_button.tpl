{if $post.current_state.identifier|eq('in_progress')}
    <a class="btn btn-danger btn-lg"
       data-action="stop"
       data-url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/stopSeduta')|ezurl(no)}">Concludi seduta</a>
{else}
    <a class="btn btn-success btn-lg"
       data-action="start"
       data-url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/startSeduta')|ezurl(no)}">Inizia seduta</a>
{/if}