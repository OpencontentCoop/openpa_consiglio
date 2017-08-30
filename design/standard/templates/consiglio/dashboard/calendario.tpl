{ezscript_require(array('ezjsc::jquery','jquery.editorialstuff_default.js'))}

{def $next_items = fetch(consiglio, next_items)}
{foreach $next_items as $item}
    {def $post = fetch(consiglio, post, hash(object, $item))}
    {include uri=concat( 'design:consiglio/dashboard/calendario/', $item.class_identifier, '.tpl' )}
    {undef $post}
{/foreach}


