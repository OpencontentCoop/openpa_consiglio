<div class="row">
    <div class="col-xs-12">
        <table class="table">
            <thead>
            <tr>
                <th width="1"></th>
                <th>Autore</th>
                <th>Testo breve</th>
                {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                <th>Visibilità</th>
                {/if}
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            {foreach $post.osservazioni as $osservazione}
                <tr>
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/osservazioni/', $osservazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td>
                        {content_view_gui content_object=$osservazione.object.owner view="politico_line"}
                    </td>
                    <td>{attribute_view_gui attribute=$osservazione.object.data_map.messaggio}</td>
                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                    <td>{include uri='design:editorialstuff/default/parts/edit_state.tpl' post=$osservazione}</td>
                    {/if}
                    <td>{attribute_view_gui attribute=$osservazione.object.data_map.allegato}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>