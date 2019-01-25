<div class="panel-body" style="background: #fff">


    <div class="row">

        {if $post.object.can_edit}
            <div class="col-xs-6 col-md-4">
                <form method="post" action="{"content/action"|ezurl(no)}" style="display: inline;">
					<div class="row">
	                <button class="btn btn-info btn-lg" type="submit" name="EditButton">Modifica</button>
                    <input type="hidden" name="ContentObjectLanguageCode"
                           value="{ezini( 'RegionalSettings', 'ContentObjectLocale', 'site.ini')}"/>
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
						{def $parent = fetch(content,node,hash(node_id, $post.node.parent_node_id))}
						<input type="hidden" name="RedirectURIAfterRemove" 
							value="{concat('editorialstuff/edit/', $parent.class_identifier, '/',$parent.contentobject_id)}" />
						{undef $parent}
					{/if}
					</div>
                </form>
            </div>
        {/if}
        {*<div class="col-xs-6 col-md-2">
            <a class="btn btn-info btn-lg" data-toggle="modal"
               data-load-remote="{concat( 'layout/set/modal/content/view/full/', $post.object.main_node_id )|ezurl('no')}"
               data-remote-target="#preview .modal-content" href="#"
               data-target="#preview">Anteprima</a>
        </div>*}
    </div>

    <hr/>

    {foreach $post.content_attributes as $identifier => $attribute}        
        {if and($identifier|eq('verbale'), or($post.current_user_is_referente,$post.object.can_edit,fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' )))|not() )}            
            {skip}            
        {/if}
        <div class="row edit-row">
            <div class="col-md-3"><strong>{$attribute.contentclass_attribute_name}</strong></div>
            <div class="col-md-9">
                {attribute_view_gui attribute=$attribute image_class=medium}
            </div>
        </div>
    {/foreach}



</div>