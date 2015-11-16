{if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
    {def $politici = fetch( editorialstuff, posts, hash( factory_identifier, politico, limit, 100, sort, hash( 'politico/cognome', 'asc' ) ) )}
    {foreach $politici as $politico}
        <ul class="list-unstyled">
            <li><a href="{concat('consiglio/collaboration/',$politico.object.id)|ezurl(no)}">Area collaborativa di {$politico.object.name|wash()}</a></li>
        </ul>
    {/foreach}
    {undef $politici}
{else}
    <ul class="list-unstyled">
    {foreach $areas as $area}
        <li><a href="{concat('consiglio/collaboration/',$area.object.owner_id)|ezurl(no)}">{$area.name|wash()}</a></li>
    {/foreach}
    </ul>
{/if}
