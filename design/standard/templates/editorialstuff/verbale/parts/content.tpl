{def $count_assigned_nodes = count($post.object.assigned_nodes)}
<div class="panel-body" style="background: #fff">


    <div class="row">

        {if $post.object.can_edit}
            <div class="col-xs-6 col-md-4">
                <form method="post" action="{"content/action"|ezurl(no)}" style="display: inline;">
					<div class="row panel-body">
                    <input type="hidden" name="ContentObjectLanguageCode"
                           value="{ezini( 'RegionalSettings', 'ContentObjectLocale', 'site.ini')}"/>
                    <button class="btn btn-info btn-lg" type="submit" name="EditButton">Modifica
                    </button>
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

					{if and($post.object.can_remove, $count_assigned_nodes|eq(1))}
                        <button class="btn btn-danger btn-lg" type="submit" name="ActionRemove">Rimuovi</button>
                        <input type="hidden" name="RedirectURIAfterRemove" value="{concat('editorialstuff/dashboard/', $factory_identifier)}" />
                    {/if}
                    <a class="btn btn-primary btn-lg" href="{concat("content/download/",$post.object.data_map.file.contentobject_id,"/",$post.object.data_map.file.id,"/file/",$post.object.data_map.file.content.original_filename)|ezurl(no)}" title="Scarica il file {$post.object.data_map.file.content.original_filename|wash( xhtml )}">
                        Download
                    </a>
                    </div>
                </form>
            </div>
        {elseif $post.current_state.identifier|eq('approved')}
            <a class="btn btn-primary btn-lg" href="{concat("content/download/",$post.object.data_map.file.contentobject_id,"/",$post.object.data_map.file.id,"/file/",$post.object.data_map.file.content.original_filename)|ezurl(no)}" title="Scarica il file {$post.object.data_map.file.content.original_filename|wash( xhtml )}">
                Download
            </a>
        {/if}
        {*<div class="col-xs-6 col-md-2">
            <a class="btn btn-info btn-lg" data-toggle="modal"
               data-load-remote="{concat( 'layout/set/modal/content/view/full/', $post.object.main_node_id )|ezurl('no')}"
               data-remote-target="#preview .modal-content" href="#"
               data-target="#preview">Anteprima</a>
        </div>*}
    </div>

    <hr/>

    <div class="row edit-row">
        <div class="col-md-3"><strong><em>Autore</em></strong></div>
        <div class="col-md-9">
            {if $post.object.owner}{$post.object.owner.name|wash()}{else}?{/if}
        </div>
    </div>

    <div class="row edit-row">
        <div class="col-md-3"><strong><em>Data di pubblicazione</em></strong></div>
        <div class="col-md-9">
            <p>{$post.object.published|l10n(shortdatetime)}</p>
            {if $post.object.current_version|gt(1)}
                <small>Ultima modifica di <a
                            href={$post.object.main_node.creator.main_node.url_alias|ezurl}>{$post.object.main_node.creator.name}</a>
                    il {$post.object.modified|l10n(shortdatetime)}</small>
            {/if}
        </div>
    </div>


    {*<div class="row edit-row">
        <div class="col-md-3"><strong><em>Collocazioni</em></strong></div>
        <div class="col-md-9">
            <ul class="list-unstyled">
                {foreach $post.object.assigned_nodes as $item}
                    <li style="padding: 3px 0">
                        {if and($count_assigned_nodes|gt(1), $item.can_remove)}
                        <form method="post" action="{"content/action"|ezurl(no)}" style="display: inline;">
                            <input type="hidden" name="ContentObjectID" value="{$post.object.id}"/>
                            <input type="hidden" name="NodeID" value="{$item.node_id}"/>
                            <input type="hidden" name="ContentNodeID" value="{$item.node_id}"/>
                            <button class="btn btn-danger btn-xs" type="submit" name="ActionRemove"><i class="fa fa-trash-o"></i></button>
                            <input type="hidden" name="RedirectURIAfterRemove" value="{concat('editorialstuff/dashboard/', $factory_identifier)}" />
                        </form>
                        {/if}
                        {$item.path_with_names} {if $item.node_id|eq($post.object.main_node_id)}(principale){/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>*}

    {foreach $post.content_attributes as $identifier => $attribute}
		{if $attribute.has_content}
        <div class="row edit-row">
            <div class="col-md-3"><strong>{$attribute.contentclass_attribute_name}</strong></div>
            <div class="col-md-9">
                {attribute_view_gui attribute=$attribute image_class=medium}
            </div>
        </div>
		{/if}
    {/foreach}


</div>
