{set $classification = cond( and(is_set( $align ), $align ), concat( $classification, ' object-', $align ), $classification )}
<table class="table {if $classification}{$classification|wash}{else}table-striped{/if}" border="0" cellspacing="0" width="100%"{if and(is_set( $summary ), $summary)} summary="{$summary|wash}"{/if}{if and(is_set( $title ), $title)} title="{$title|wash}"{/if}>
{if and(is_set( $caption ), $caption)}<caption>{$caption|wash}</caption>{/if}
{$rows}
</table>