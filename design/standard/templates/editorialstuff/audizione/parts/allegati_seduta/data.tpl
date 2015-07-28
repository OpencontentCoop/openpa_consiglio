<div class="row">
    <div class="col-xs-12">
        <table class="table">
            <thead>
            <tr>
                <th width="1"></th>
                <th>Titolo</th>
                <th>Tipo</th>
                {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                <th>Visibilità</th>
                {/if}
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            {foreach $post.documenti as $allegato}
                <tr>
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/allegati_seduta/', $allegato.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td>{$allegato.object.name|wash()}</td>
                    <td>{attribute_view_gui attribute=$allegato.object.data_map.tipo}</td>
                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                    <td>{include uri='design:editorialstuff/default/parts/edit_state.tpl' post=$allegato}</td>
                    {/if}
                    <td>{attribute_view_gui attribute=$allegato.object.data_map.file}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>