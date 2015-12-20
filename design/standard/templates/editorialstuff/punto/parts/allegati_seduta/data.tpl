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
                <th>Visibilit&agrave;</th>
                {/if}
                <th>Download</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach $post.documenti as $allegato}
			{if $allegato.object.can_read|not}{skip}{/if}
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
                        {if $allegato.sostituito}<strike>{/if}
						{$allegato.object.name|wash()}
                        {if $allegato.sostituito}</strike>{/if}

                        {if $allegato.sostituito}
                            <span class="label label-warning">sostituito</span>
                        {/if}
                    </td>
                    <td>{attribute_view_gui attribute=$allegato.object.data_map.tipo}</td>
                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                    <td>{include uri='design:editorialstuff/default/parts/edit_state.tpl' post=$allegato}</td>
                    {/if}
                    <td>{attribute_view_gui attribute=$allegato.object.data_map.file}</td>
                    <td>
                        {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                            <a href="{concat('editorialstuff/file/punto/remove/', $post.object.id, '/documenti/', $allegato.object.id )|ezurl(no)}" class="btn btn-link btn-xs"><i class="fa fa-trash"></i></a>
                        {/if}
                        {if $allegato.can_share}
                            {if $allegato.is_shared|not()}
                                <form action="{concat('consiglio/share')|ezurl(no)}" enctype="multipart/form-data" method="post">
                                    <input type="hidden" name="Factory" value="allegati_seduta"/>
                                    <input type="hidden" name="Id" value="{$allegato.object_id}"/>
                                    <input type="hidden" name="RedirectUrl" value="{$post.editorial_url}"/>
                                    <p class="clearfix">
                                        <button class="btn btn-primary" type="submit" name="Share"><i class="fa fa-share-alt"></i> Copia in area collaborativa</button>
                                    </p>
                                </form>
                            {else}
                                <a href="{$post.shared_url|ezurl(no)}" class="btn btn-default"><i class="fa fa-share-alt"></i> Copiato in area collaborativa</a>
                            {/if}
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>