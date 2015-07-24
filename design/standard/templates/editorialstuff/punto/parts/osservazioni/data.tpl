<div class="row">
    <div class="col-xs-12">
        <table class="table">
            <thead>
            <tr>
                <th width="1"></th>
                <th>Titolo</th>
                <th>Tipo</th>
                <th>Visibilità</th>
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            {foreach $post.osservazioni as $osservazione}
                <tr>
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/allegati_seduta/', $osservazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td>{$osservazione.object.name|wash()}</td>
                    <td>{attribute_view_gui attribute=$osservazione.object.data_map.tipo}</td>
                    <td>{include uri='design:editorialstuff/default/parts/edit_state.tpl' post=$osservazione}</td>
                    <td>{attribute_view_gui attribute=$osservazione.object.data_map.file}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>