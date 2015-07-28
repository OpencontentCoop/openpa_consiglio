<div class="panel-body" style="background: #fff">


    <div class="row">

        {if $post.object.can_edit}
            <div class="col-xs-6 col-md-2">
                <form method="post" action="{"content/action"|ezurl(no)}" style="display: inline;">
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
                </form>
            </div>
        {/if}
        <div class="col-xs-6 col-md-4">
            {*<a class="btn btn-info btn-lg" data-toggle="modal"
               data-load-remote="{concat( 'layout/set/modal/content/view/full/', $post.object.main_node_id )|ezurl('no')}"
               data-remote-target="#preview .modal-content" href="#"
               data-target="#preview">Anteprima</a>*}
            <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-inline">
                <input type="hidden" name="ActionIdentifier" value="GetConvocazione" />

                <div class="input-group-btn">
                <select class="form-control input-lg" id="formInterlinea" tabindex="-1" name="ActionParameters[line_height]">
                    <option value="1">Interlinea 1</option>
                    <option value="1.1">Interlinea 2</option>
                    <option selected="" value="1.2">Interlinea 3</option>
                    <option value="1.3">Interlinea 4</option>
                    <option value="1.4">Interlinea 5</option>
                    <option value="1.5">Interlinea 6</option>
                </select>

                <button type="submit" class="btn btn-primary btn-lg" name="GetConvocazione">Download convocazione</button>
                </div>
            </form>
        </div>
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
        <a href="{concat('add/new/punto?parent=',$post.object.main_node_id)|ezurl(no)}" class="btn btn-info btn-md">Aggiungi punto</a>
    </h2>
    <div class="row">
        <div class="col-xs-12">
            {include uri=concat('design:', $template_directory, '/parts/content/odg.tpl') post=$post}
        </div>
    </div>

</div>

{*
    <style>.ui-sortable tr .handle{cursor:pointer;}.ui-sortable tr.ui-sortable-helper {background:rgba(244,251,17,0.45);}.ui-sortable tr.ui-state-highlight {background:rgba(244,251,17,0.1);</style>
//            var fixHelperModified = function(e, tr) {
//                var $originals = tr.children();
//                var $helper = tr.clone();
//                $helper.children().each(function(index){
//                    $(this).width($originals.eq(index).width())
//                });
//                return $helper;
//            };
//            $("#odg tbody").sortable({
//                items: "tr:not(.ui-state-disabled)",
//                placeholder: "ui-state-highlight",
//                handle: ".handle",
//                helper: fixHelperModified,
//                stop: function(event,ui) {
//                    renumber_table('#odg')
//                }
//            }).disableSelection();

//            function renumber_table(tableID) {
//                $(tableID + " tr:not(.ui-state-disabled)").each(function() {
//                    var value = $(this).parent().children().index($(this)) + 1;
//                    var self = $(this).find('.priority');
//                    var id = self.data( 'id' );
//                    var name = self.data( 'name' );
//                    var url = self.data( 'url' );
//                    var pk = self.data( 'pk' );
//                    if (url != null) {
//                        self.html('<i class="fa fa-cog fa-spin fa-fw"></i>');
//                        $.post(url, {name: name, pk: pk, value: value}, function (data) {
//                            self.html(value);
//                        });
//                    }
//                });
//            }
*}
