<ul class="side_menu">
    {foreach $post.odg as $index => $punto}
        <li {if $punto.current_state.identifier|eq('in_progress')}class="alert alert-success" style="padding:0 10px"{/if}>
            <a href="#" {if $punto.current_state.identifier|eq('closed')}style="text-decoration: line-through"{/if}
                    data-verbale_id="{$punto.object.id}">
                <small>{$punto.object.name|wash()}</small><br />
                {$punto.object.data_map.oggetto.content|wash()}
            </a></li>
    {/foreach}
</ul>