{if $view|contains('inline')|not()}
<div class="embed {if $object_parameters.align}{if $object_parameters.align|eq('center')}text-center{else}object-{$object_parameters.align}{/if}{/if}{if ne($classification|trim,'')} {$classification|wash}{/if}"{if is_set($object_parameters.id)} id="{$object_parameters.id}"{/if}>
{/if}
{content_view_gui view=$view link_parameters=$link_parameters object_parameters=$object_parameters content_object=$object classification=$classification}
{if $view|contains('inline')|not()}
</div>
{/if}