<div class="panel-body" style="background: #fff">


    <div class="row">

        {if $post.object.can_edit}
            <div class="col-xs-12 col-sm-12 col-md-3" style="margin-bottom: 10px">
                <form method="post" action="{"content/action"|ezurl(no)}" style="display: inline;">
                    <input type="hidden" name="ContentObjectLanguageCode"
                           value="{ezini( 'RegionalSettings', 'ContentObjectLocale', 'site.ini')}"/>					
                    <button class="btn btn-info btn-lg" type="submit" name="EditButton">Modifica</button>
                    <input type="hidden" name="HasMainAssignment" value="1"/>
                    <input type="hidden" name="ContentObjectID" value="{$post.object.id}"/>
                    <input type="hidden" name="NodeID" value="{$post.node.node_id}"/>
                    <input type="hidden" name="ContentNodeID" value="{$post.node.node_id}"/>
                    {* If a translation exists in the siteaccess' sitelanguagelist use default_language, otherwise let user select language to base translation on. *}
                    {def $avail_languages = $post.object.available_languages
                    $content_object_language_code = ''
                    $default_language = $post.object.default_language}
                    {if and( $avail_languages|count|ge( 1 ), $avail_languages|contains( $default_language ) )}
                        {set $content_object_language_code = $default_language}
                    {else}
                        {set $content_object_language_code = ''}
                    {/if}
                    <input type="hidden" name="ContentObjectLanguageCode"
                           value="{$content_object_language_code}"/>
                    <input type="hidden" name="RedirectIfDiscarded"
                           value="{concat('editorialstuff/edit/', $factory_identifier, '/',$post.object.id)}"/>
                    <input type="hidden" name="RedirectURIAfterPublish"
                           value="{concat('editorialstuff/edit/', $factory_identifier, '/',$post.object.id)}"/>
					{if $post.object.can_remove}
                        <button class="btn btn-danger btn-lg" type="submit" name="ActionRemove">Rimuovi</button>
                        <input type="hidden" name="RedirectURIAfterRemove"
                            value="{concat('editorialstuff/', 'dashboard/seduta')}" />
                    {/if}                    
                </form>
            </div>
        {/if}
		{if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
        <div class="col-xs-12 col-sm-6 col-md-5" style="margin-bottom: 10px">
            {*<a class="btn btn-info btn-lg" data-toggle="modal"
               data-load-remote="{concat( 'layout/set/modal/content/view/full/', $post.object.main_node_id )|ezurl('no')}"
               data-remote-target="#preview .modal-content" href="#"
               data-target="#preview">Anteprima</a>*}
            <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-inline">
                <input type="hidden" name="ActionIdentifier" value="GetConvocazione" />

                <div class="input-group-btn">
                <select class="form-control input-lg" id="formInterlinea" tabindex="-1" name="ActionParameters[line_height]">
                    <option value="0.8">Interlinea 1</option>
                    <option value="1">Interlinea 2</option>
                    <option selected="" value="1.2">Interlinea 3</option>
                    <option value="1.5">Interlinea 4</option>
                    <option value="1.8">Interlinea 5</option>
                    <option value="2">Interlinea 6</option>
                </select>

                <button type="submit" class="btn btn-primary btn-lg" name="GetConvocazione">Download convocazione</button>
                </div>
            </form>
        </div>
        {elseif array('sent')|contains($post.current_state.identifier)}
            <div class="well well-sm text-center">
                <strong>Convocazione:</strong>
                {attribute_view_gui attribute=$post.object.data_map.convocazione}
            </div>
		{/if}
        {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom: 10px">
                <a target="_blank" class="btn btn-lg btn-warning" href="{concat('consiglio/cruscotto_seduta/', $post.object_id)|ezurl(no)}"><i class="fa fa-dashboard"></i> Apri cruscotto</a>
                <a target="_blank" class="btn btn-lg btn-warning" href="{concat('consiglio/monitor_sala/', $post.object_id)|ezurl(no)}"><i class="fa fa-desktop"></i> Apri monitor</a>
            </div>
        {/if}
    </div>

    <hr/>

    {foreach $post.content_attributes as $identifier => $attribute}
        <div class="row edit-row">
            <div class="col-md-3"><strong>{$attribute.contentclass_attribute_name}</strong></div>
            <div class="col-md-9">
                {if $identifier|eq('protocollo')}
                    <a href="#" class="editable" data-type="text" data-name="protocollo"
                       data-pk="{$attribute.id}"
                       data-url="{concat('/edit/attribute/',$post.object.id,'/protocollo/1')|ezurl(no)}"
                       data-title="Imposta protocollo">
                        {attribute_view_gui attribute=$attribute}
                    </a>
                {else}
                    {attribute_view_gui attribute=$attribute image_class=medium}
                {/if}
            </div>
        </div>
    {/foreach}


    <hr />
    <h2>
        Ordine del giorno
	{if $post.can_modify_odg}
        <a href="{concat('editorialstuff/add/punto?parent=',$post.object.main_node_id)|ezurl(no)}" class="btn btn-info btn-md">Aggiungi punto</a>{/if}
    </h2>
    <div class="row">
        <div class="col-xs-12">
            {include uri=concat('design:', $template_directory, '/parts/content/odg.tpl') post=$post}
        </div>
    </div>

</div>