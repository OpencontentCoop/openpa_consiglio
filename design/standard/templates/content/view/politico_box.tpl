<div style="height:150px; background: url({if $object|has_attribute( 'image' )}
{$object|attribute( 'image' ).content['medium'].url|ezroot(no)}
{else}
{'images/profile.jpg'|ezdesign(no)}
{/if}) top center no-repeat"></div>
<p class="text-center">
    <strong>{$object.name|wash()}</strong>
</p>
