{if $post.current_state.identifier|eq('in_progress')}
    {foreach $post.odg as $punto}
        {if $punto.current_state.identifier|eq('published')}
            <a class="btn btn-success btn-lg" data-url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/startPunto/',$punto.object_id)|ezurl(no)}">Inizia trattazione punto {$punto.object.data_map.n_punto.content}</a>
            {break}
        {elseif $punto.current_state.identifier|eq('in_progress')}
            <a class="btn btn-danger btn-lg" data-url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/stopPunto/',$punto.object_id)|ezurl(no)}">Concludi trattazione punto {$punto.object.data_map.n_punto.content}</a>
            {break}
        {/if}
    {/foreach}
{/if}