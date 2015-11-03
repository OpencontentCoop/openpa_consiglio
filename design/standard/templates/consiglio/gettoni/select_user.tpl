{def $politici = fetch( 'editorialstuff', 'posts', hash( 'factory_identifier', 'politico', 'limit', 100, 'sort', hash( 'name', 'asc' ) ) )}

<h3>Seleziona utente:</h3>
<ul class="list-unstyled">
{foreach $politici as $politico}
    <li><a href="{concat('consiglio/gettoni/',$politico.object.id)|ezurl(no)}">{$politico.object.name|wash()}</a></li>
{/foreach}
</ul>