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
        <div class="col-xs-6 col-md-2">
            <a class="btn btn-info btn-lg" data-toggle="modal"
               data-load-remote="{concat( 'layout/set/modal/content/view/full/', $post.object.main_node_id )|ezurl('no')}"
               data-remote-target="#preview .modal-content" href="#{*$post.url*}"
               data-target="#preview">Anteprima</a>
        </div>
    </div>

    <hr/>

    {foreach $post.content_attributes as $identifier => $attribute}
        <div class="row edit-row">
            <div class="col-md-3"><strong>{$attribute.contentclass_attribute_name}</strong></div>
            <div class="col-md-9">
                {attribute_view_gui attribute=$attribute image_class=medium}
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

    {ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css'))}
    {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryUI', 'bootstrap-editable.min.js' ) )}
    {literal}<script>
        $(document).ready(function(){
            var editableOptions = {
                success: function(response, newValue) {
                    reload('#odg');
                    return response;
                },
                error: function(response, newValue) {
                    if(response.responseJSON.status == 'error') return response.responseJSON.message;
                }
            };

            function reload(tableID){
                var self = $(tableID);
                var url = self.data('url');
                $.get(url,function (data) {
                    self.parent().html(data).find('.editable').editable(editableOptions);
                    //renumber_table('#odg');
                });
            }

            $('.editable').editable(editableOptions);
            reload('#odg');
        })
    </script>

    {/literal}

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
