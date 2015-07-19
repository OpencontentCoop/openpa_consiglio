<ul class="side_menu">
    {foreach $post.odg as $index => $punto}
        <li {if $punto.current_state.identifier|eq('in_progress')}}class="active"{/if}>
            <a href="#" {if $punto.current_state.identifier|eq('closed')}}style="text-decoration: line-through"{/if}>
                <small>{$punto.object.name|wash()}</small><br />
                {$punto.object.data_map.oggetto.content|wash()}
            </a></li>
    {/foreach}
</ul>