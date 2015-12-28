{if $object.class_identifier|eq('openpa_consiglio_collaboration_room')}
    {def $area = $object.main_node.parent}
    {if $object.current_version|eq(1)}
        {set-block scope=root variable=subject}Nuova tematica di discussione in {$area.name|wash()}{/set-block}
        <p>La presente per segnalare la creazione della nuova tematica di discussione nell'area collaborativa alla quale partecipa:
            <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/{$area.contentobject_id}/room-{$object.main_node_id}"><strong>{$object..data_map.name.content|wash()}</strong></a>
        </p>
        {if and(is_set($object.data_map.expiry), $object.data_map.expiry.has_content)}
            <p>Le ricordiamo che può partecipare alla discussione della tematica fino alla data {$object.data_map.expiry.content.timestamp|datetime( 'custom', '%j/%m/%Y')}</p>
        {/i}
    {elseif and(is_set($room.data_map.expiry), $room.data_map.expiry.has_content)}
        {set-block scope=root variable=subject}Modifica tematica di discussione in {$area.name|wash()}{/set-block}
        <p>La presente per segnalare la modifica della data di scadenza della tematica di discussione nell'area collaborativa alla quale partecipa:
            <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/{$area.contentobject_id}/room-{$object.main_node_id}"><strong>{$object..data_map.name.content|wash()}</strong></a>
        </p>
        <p>La nuova data di scadenza prevista per la tematica è {$object.data_map.expiry.content.timestamp|datetime( 'custom', '%j/%m/%Y' )}</p>
    {/if}

{elseif $object.class_identifier|eq('openpa_consiglio_collaboration_comment')}
    {def $room = $object.main_node.parent}
    {def $area = $room.parent}
    {set-block scope=root variable=subject}Nuovo intervento in {$area.name|wash()}{/set-block}
    <p>
        La presente per segnalare l'inserimento di un nuovo intervento nella tematica di discussione nell'area collaborativa alla quale partecipa:
    </p>
    <p>
        <em>{$object.data_map.message.content|wash()}</em>
    </p>
    {if and(is_set($room.data_map.expiry), $room.data_map.expiry.has_content)}
        <p>Le ricordiamo che può partecipare alla discussione della tematica fino alla data {$room.data_map.expiry.content.timestamp|datetime( 'custom', '%j/%m/%Y' )}</p>
    {/if}

{elseif $object.class_identifier|eq('openpa_consiglio_collaboration_file')}
    {def $room = $object.main_node.parent}
    {def $area = $room.parent}
    {set-block scope=root variable=subject}Nuovo documento in {$area.name|wash()}{/set-block}

    <p>La presente per segnalare l'inserimento del documento <em>{$object.name|wash()}</em> nell'area collaborativa alla quale partecipa</p>

    {if and(is_set($room.data_map.expiry), $room.data_map.expiry.has_content)}
        <p>Le ricordiamo che può partecipare alla discussione della tematica fino alla data {$room.data_map.expiry.content.timestamp|datetime( 'custom', '%j/%m/%Y' )}</p>
    {/if}


{/if}

{if or( $area, $room )}
<hr />
<p>
    Link per accedere all'area collaborativa riservata:<br />
    {if $area}
        <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/{$area.contentobject_id}">{$area.name|wash()}</a><br />
    {/if}
    {if $room}
        <a href="http://{social_pagedata('consiglio').site_url}/consiglio/collaboration/{$area.contentobject_id}/room-{$room.node_id}">Tematica di discussione</a>
    {/if}
</p>
{/if}
