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

            {def $verbale_object = $post.verbale_object}
            {if and($verbale_object, $verbale_object.current_state.identifier|ne('draft'))}
                <tr class="warning">
                    <td class="text-center">
                        <a href="{$verbale_object.editorial_url|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td>{$verbale_object.object.name|wash()}</td>
                    <td>Verbale</td>
                    <td style="white-space: nowrap">{$verbale_object.current_state.current_translation.name|wash()}</td>
                    <td>
                        {if $verbale_object.current_state.identifier|eq('approved')}
                        {attribute_view_gui attribute=$verbale_object.object.data_map.file}
                        {/if}
                    </td>
                </tr>
            {/if}
            {foreach $post.documenti as $allegato}
                <tr>
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/allegati_seduta/', $allegato.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td>{$allegato.object.name|wash()}</td>
                    <td>{attribute_view_gui attribute=$allegato.object.data_map.tipo}</td>
                    <td>{include uri='design:editorialstuff/default/parts/edit_state.tpl' post=$allegato}</td>
                    <td>{attribute_view_gui attribute=$allegato.object.data_map.file}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>