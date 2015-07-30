<div class="row">
    <div class="col-xs-12" style="position:relative">
        <table class="table" style="border-spacing: 0;">
            <thead>
            <tr>
                {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                    <th></th>
                    <th>Sostituito</th>
                {/if}
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
                <tr data-allegato_id="{$allegato.object.id}">
                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                        <td><i class="fa fa-reorder sort-handle"></i> </td>
                        <td class="text-center">
                            <input type="checkbox" {if $allegato.sostituito}checked="checked"{/if} class="edit-sostituito"
                                   data-url="{concat('/edit/attribute/',$allegato.object.id,'/sostituito/1')|ezurl(no)}" />
                        </td>
                    {/if}
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/allegati_seduta/', $allegato.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td>
                        {$allegato.object.name|wash()}
                        {if $allegato.sostituito}
                            <span class="label label-warning">sostituito</span>
                        {/if}
                    </td>
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