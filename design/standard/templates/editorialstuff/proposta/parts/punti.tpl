<div class="panel-body" style="background: #fff">
    {if $post.current_state.identifier|eq('online')}
        {if count($post.punti)|gt(0)}                        
            {foreach $post.punti as $punto}
                {include uri='design:editorialstuff/proposta/parts/embed_punto.tpl' post=$punto}
                {delimiter}<hr />{/delimiter}
            {/foreach}
        {/if}
    {/if}
</div>