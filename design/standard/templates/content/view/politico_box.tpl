{if is_set($size)|not}{def $size = 'medium'}{/if}
<div style="height:120px; background: url({if $object|has_attribute( 'image' )}{$object|attribute( 'image' ).content[$size].url|ezroot(no)}{else}{'images/profile_medium.jpg'|ezdesign(no)}{/if}) top center no-repeat"></div>
<p class="text-center">
    <strong>{$object.name|wash()}</strong>
</p>
