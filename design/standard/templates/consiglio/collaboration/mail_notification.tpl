{if $object.class_identifier|eq('openpa_consiglio_collaboration_room')}
    {def $area = $object.main_node.parent}
    {set-block scope=root variable=subject}Nuova tematica di discussione in {$area.name|wash()}{/set-block}
    <p>La presente per notificarLe la creazione della nuova tematica di dicussione nell'area collaborativa alla quale partecipa:
    <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/tag-{$object.main_node_id}"><strong>{$object.name|wash()}</strong></a>
    </p>
{elseif $object.class_identifier|eq('openpa_consiglio_collaboration_comment')}
    {def $tag = $object.main_node.parent}
    {def $area = $tag.parent}
    {set-block scope=root variable=subject}Nuovo intervento in {$area.name|wash()}{/set-block}
    <p>La presente per notificarLe l'inserimento di un nuovo intervento nella tematica di dicussione  {$tag.name|wash()} nell'area collaborativa alla quale partecipa:
        <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/tag-{$tag.node_id}"><strong>{$object.name|wash()}</strong></a>
    </p>
{elseif $object.class_identifier|eq('openpa_consiglio_collaboration_file')}
    {def $tag = $object.main_node.parent}
    {def $area = $tag.parent}
    {set-block scope=root variable=subject}Nuovo documento in {$area.name|wash()}{/set-block}
    <p>La presente per notificarLe l'inserimento di un nuovo documento nella tematica di dicussione  {$tag.name|wash()} nell'area collaborativa alla quale partecipa:
        <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/tag-{$tag.node_id}"><strong>{$object.name|wash()}</strong></a>
    </p>
{/if}