<form>
    {def $hasCurrentPunto = false()}
    {foreach $post.odg as $index => $punto}
        <div class="textarea-container" {if $punto.current_state.identifier|eq('in_progress')|not()}style="display: none"{/if}>
            <h4>Verbale {$punto.object.name|wash()}</h4>
            <textarea name="Verbale[{$punto.object.id}]" class="form-control"
                      rows="20">{$punto.verbale}</textarea>
            <a href="#" class="save-verbale btn btn-danger pull-right">Salva Modifiche</a>
            {if $punto.current_state.identifier|eq('in_progress')}{set $hasCurrentPunto = true()}{/if}
        </div>
    {/foreach}
    <div class="textarea-container" {if $hasCurrentPunto}style="display: none"{/if}>
        <h4>Verbale {$post.object.name|wash()}</h4>
        <textarea name="Verbale[{$post.object.id}]" class="form-control"
                  rows="20">{$post.verbale}</textarea>
        <a href="#" class="save-verbale btn btn-danger pull-right">Salva Modifiche</a>
    </div>
</form>