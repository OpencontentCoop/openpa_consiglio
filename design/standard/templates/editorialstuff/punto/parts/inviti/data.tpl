<table class="table sorted_table" id="tableinviti"
       data-url="{concat('/consiglio/data/punto/',$post.object_id,'/parts:inviti:data')|ezurl(no)}">
    <thead>
    <tr>
        <th width="1"></th>
        <th>Titolo</th>
        <th>Protocollo invito</th>
        <th width="1"></th>
    </tr>
    </thead>
    <tbody>
    {foreach $post.invitati as $invitato}
        <tr>
            <td class="text-center">
                <a href="{concat( 'editorialstuff/edit/invitato/', $invitato.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
            </td>
            <td>{$invitato.object.name|wash()}</td>
            <td>
            {def $invito = fetch( 'content', 'object', hash( 'remote_id', concat( 'invito_', $post.seduta_id, '_', $invitato.object_id ) ) )}
            {if $invito}
                <a href="#" class="edit-protocollo" data-type="text" data-name="protocollo"
                   data-pk="{$invito.data_map.protocollo.id}"
                   data-url="{concat('/edit/attribute/',$invito.id,'/protocollo/1')|ezurl(no)}"
                   data-title="Imposta protocollo">
                    {if $invito.data_map.protocollo.has_content}
                        {attribute_view_gui attribute=$invito.data_map.protocollo}
                    {else}
                        nessuno
                    {/if}
                </a>
            {/if}

            </td>
            <td class="text-center">
                {if $invito}
                <form action="{concat( 'editorialstuff/download/invito/', $invito.id)|ezurl('no')}" enctype="multipart/form-data" method="get" class="form-inline">
                    <div class="input-group-btn">
                        <select class="form-control input-md" id="formInterlinea" tabindex="-1" name="line_height">
                            <option value="1">Interlinea 1</option>
                            <option value="1.1">Interlinea 2</option>
                            <option selected="" value="1.2">Interlinea 3</option>
                            <option value="1.3">Interlinea 4</option>
                            <option value="1.4">Interlinea 5</option>
                            <option value="1.5">Interlinea 6</option>
                        </select>

                        <button type="submit" class="btn btn-primary btn-md">Download invito</button>
                    </div>
                </form>
                {/if}
            </td>
            {undef $invito}
        </tr>
    {/foreach}
    </tbody>
</table>
