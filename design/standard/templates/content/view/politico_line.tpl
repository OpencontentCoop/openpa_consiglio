<span style="vertical-align: middle; height:35px; width:35px; display:inline-block; background: url({if $object|has_attribute( 'image' )}
    {$object|attribute( 'image' ).content['logo'].url|ezroot(no)}
{else}
    {'images/profile_tiny.jpg'|ezdesign(no)}
{/if}) center center no-repeat">
</span>
<span style="vertical-align: middle; display: inline-block;">
    {if is_set($link)}<a href="{$object.main_node.url_alias|ezurl(no)}">{/if}
    {$object.name|wash()}
    {if is_set($link)}</a>{/if}
</span>

