{ezscript_require(array('ezjs::jquery','jquery.editorialstuff_default.js'))}
{def $materie_like = fetch( editorialstuff, notification_rules_post_ids, hash( type, 'materia/like', user_id, fetch(user,current_user).contentobject_id ) )}
{def $calendarData = fetch( openpa, calendario_eventi, hash(
    'calendar', fetch( content, node, hash( node_id, 1136 ) ),
    'params', hash(
        'interval', 'P4W',
        'view', 'calendar'
    )
))}

{foreach $calendarData.events as $event}
    {def $post = object_handler($event.object).gestione_sedute_consiglio.stuff}
    {include uri=concat( 'design:consiglio/dashboard/calendario/', $event.object.class_identifier, '.tpl' )}
    {undef $post}
{/foreach}


