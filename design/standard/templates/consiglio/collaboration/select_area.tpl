{if $error}
    <div class="alert alert-danger">
        <h3 style="margin: 0">{$error|wash()}</h3>
    </div>
{/if}


<table class="table">
    <tr>
        <th>Area</th>
        <th style="white-space: nowrap">Creata il</th>
        <th style="white-space: nowrap">Ultima modifica</th>
        <th>Tematiche</th>
        <th></th>
    </tr>
    {foreach $areas as $area}
        <tr>
            <td>
                <a href="{concat('consiglio/collaboration/',$area.object.id)|ezurl(no)}">{$area.object.name|wash()}</a>
            </td>
            <td style="white-space: nowrap">
                {$area.object.published|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}
            </td>
            <td style="white-space: nowrap">
                {$area.main_node.modified_subnode|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}
            </td>
            <td style="white-space: nowrap">
                {$area.main_node.children_count} tematiche
            </td>
            <td>
                <a class="btn btn-primary btn-sm"
                   href="{concat('consiglio/collaboration/', $area.object.id)|ezurl(no)}">Accedi</a>
            </td>
        </tr>
    {/foreach}
</table>

