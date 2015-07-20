{def $hasCurrentPunto = false()}
{foreach $post.odg as $index => $punto}
    {if $punto.current_state.identifier|eq('in_progress')}
        <h4>Verbale {$punto.object.name|wash()}</h4>
        <textarea class="form-control" rows="20"></textarea>
        {set $hasCurrentPunto = true()}
    {/if}
{/foreach}
{if $hasCurrentPunto|not()}
<h4>Verbale {$post.object.name|wash()}</h4>
<textarea class="form-control" rows="20"></textarea>
{/if}