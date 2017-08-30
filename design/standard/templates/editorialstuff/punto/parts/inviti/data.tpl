{def $inviti = array()}
<table class="table sorted_table" id="tableinviti"
       data-url="{concat('/consiglio/data/punto/',$post.object_id,'/parts:inviti:data')|ezurl(no)}">
    <thead>
    <tr>
        <th width="1"></th>
        <th>Titolo</th>
        <th>Protocollo invito</th>
        <th>Ora</th>
        <th width="1"></th>
        {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
            <th></th>
        {/if}
    </tr>
    </thead>
    <tbody>
    {foreach $post.invitati as $invitato}
        <tr>
            {def $invito = fetch( 'content', 'object', hash( 'remote_id', concat( 'invito_', $post.seduta_id, '_', $invitato.object_id ) ) )}
            {def $stuff_post = fetch(consiglio, post, hash(object, $invito))}
            {set $inviti = array()}
            {foreach $stuff_post.punti as $i}
                {if ne($i.object_id, $post.object.id)}
                    {set $inviti = $inviti|append($i.n_punto)}
                {/if}
            {/foreach}
            <td class="text-center">
                <a href="{concat( 'editorialstuff/edit/invitato/', $invitato.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
            </td>
            <td>{$invitato.object.name|wash()}{if gt($inviti|count(), 0)}<br><small>Invitato anche {if gt($inviti|count(), 1)}ai punti{else}al punto{/if} {$inviti|implode(', ')}</small>{/if}</td>
            <td>

                {*$stuff_post.punti[0]|attribute(show)*}
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
            <td>
                {if $invito}
                    <a href="#" class="edit-ora" data-type="text" data-name="ora"
                       data-pk="{$invito.data_map.ora.id}"
                       data-url="{concat('/edit/attribute/',$invito.id,'/ora/1')|ezurl(no)}"
                       data-title="Imposta orario">
                        {if $invito.data_map.ora.has_content}
                            {attribute_view_gui attribute=$invito.data_map.ora}
                        {else}
                            {if eq($stuff_post.punti[0].object_id, $post.object.id)}
                                {attribute_view_gui attribute=$post.object.data_map.orario_trattazione}
                            {else}
                                {$stuff_post.punti[0].ora}
                            {/if}
                        {/if}
                    </a>
                    {if $invito.data_map.ora.has_content}
                        <span class="label-ora text-danger"> (Orario impostato manualmente)</span>
                    {else}
                        {if eq($stuff_post.punti[0].object_id, $post.object.id)}
                            <span class="label-ora text-info"> (Orario di trattazione del punto) </span>
                        {else}
                            <span class="label-ora text-warning"> (Orario di trattazione del punto precendente)</span>
                        {/if}
                    {/if}
                {/if}
            </td>
            <td class="text-center">
                {if $invito}
                <form action="{concat( 'editorialstuff/download/invito/', $invito.id)|ezurl('no')}" enctype="multipart/form-data" method="get" class="form-inline">
                    <div class="input-group-btn">
                        <select class="form-control input-md" id="formInterlinea" tabindex="-1" name="line_height">
                            <option value="0.8">Interlinea 1</option>
                            <option value="1">Interlinea 2</option>
                            <option selected="" value="1.2">Interlinea 3</option>
                            <option value="1.5">Interlinea 4</option>
                            <option value="1.8">Interlinea 5</option>
                            <option value="2">Interlinea 6</option>
                        </select>

                        <button type="submit" class="btn btn-primary btn-md">Download invito</button>
                    </div>
                </form>
                {/if}
            </td>
            {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                <td>
                    <form action="{concat('editorialstuff/action/punto/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-horizontal">
                        <input type="hidden" name="ActionIdentifier" value="RemoveInvitato" />
                        <input type="hidden" name="ActionParameters[invitato]" value="{$invitato.object_id}" />
                        <button type="submit" name="RemoveInvitato" class="btn btn-link btn-xs"><i class="fa fa-trash"></i></button>
                    </form>
                </td>
            {/if}
            {undef $invito}
        </tr>
    {/foreach}
    </tbody>
</table>
