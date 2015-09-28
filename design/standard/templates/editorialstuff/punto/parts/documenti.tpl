<div class="panel-body" style="background: #fff;"  id="allegati_add" data-action_url="{concat('editorialstuff/action/punto/', $post.object_id)|ezurl(no)}">
    <div class="row">
        <div class="col-xs-12 allegati_docs">
            {include uri=concat('design:', $template_directory, '/parts/allegati_seduta/data.tpl') post=$post}
        </div>
    </div>
{if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
    {def $class = fetch( content, class, hash( class_id, 'allegato_seduta' ) )
         $tipo_attribute = false()}
    {foreach $class.data_map as $identifier => $class_attribute}
        {if $identifier|eq('tipo')}
            {set $tipo_attribute = $class_attribute}
        {/if}
    {/foreach}
    <hr />
    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <div class="well">
                <h2>Aggiungi nuovo documento</h2>
                <form action="" enctype="multipart/form-data" method="post" class="upload-form form-horizontal">
                    <div class="form-group">
                        <label for="DocTitle" class="col-sm-2 control-label">Titolo</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="FileAttributes[name]" id="DocTitle" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="DocType" class="col-sm-2 control-label">Tipo</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="FileAttributes[tipo]" id="DocType">
                                {foreach $tipo_attribute.content.options as $item}
                                    <option value="{$item.name|wash()}">{$item.name|wash()}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="DocState" class="col-sm-2 control-label">Visibilit&agrave;</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="FileProperties[state_identifier]" id="DocState">
                                <option value="visibilita_allegato_seduta.consiglieri">Consiglieri</option>
                                <option value="visibilita_allegato_seduta.referenti">Referenti</option>
                                <option value="visibilita_allegato_seduta.pubblico">Pubblico</option>
                            </select>
                        </div>
                    </div>

                    <div class="clearfix">
                        <span class="btn btn-success btn-lg fileinput-button pull-right">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>Scegli file e salva</span>
                            <input class="upload" type="file" name="DocFile[]" data-url="{concat('editorialstuff/file/punto/upload/', $post.object.id, '/documenti' )|ezurl(no)}" />
                        </span>
                    </div>

                </form>
                <div class="upload-loading" class="text-center" style="display: none">
                    <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
                </div>
            </div>
        </div>
    </div>
    {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI', 'jquery.fileupload.js') )}
    {ezcss_require( 'jquery.fileupload.css' )}
    {literal}

    <style scoped="scoped">
        .ui-sortable tr .sort-handle{cursor:pointer;}
        .ui-sortable tr.ui-sortable-helper {background:rgba(244,251,17,0.45);}
        .ui-sortable tr.ui-state-highlight {background:rgba(244,251,17,0.1);
    </style>

    <script>
        $(function () {
            var allegati = $('#allegati_add');

            var fixHelperModified = function (e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function (index) {
                    $(this).width($originals.eq(index).width())
                });
                return $helper;
            };

            var allegatiSortableOptions = {
                items: "tr:not(.ui-state-disabled)",
                placeholder: "ui-state-highlight",
                handle: ".sort-handle",
                helper: fixHelperModified,
                stop: function (event, ui) {
                    var ids = [];
                    $.each(allegati.find(".allegati_docs tbody tr" ), function(){
                        ids.push( $(this).data('allegato_id') );
                    });
                    $.ajax({
                        url: allegati.data( 'action_url' ),
                        method: 'POST',
                        data: {
                            ActionIdentifier: 'SortAllegati',
                            SortAllegati: true,
                            ActionParameters: {
                                identifier: 'documenti',
                                sort_ids: ids
                            }
                        }
                    });
                }
            };

            allegati.find(".allegati_docs tbody").sortable(allegatiSortableOptions).disableSelection();

            $(document).on( 'click', 'input.edit-sostituito', function(e){
                var input = $(e.currentTarget);
                var value = input.is(':checked') ? 1 : 0;
                $.ajax({
                    url: input.data( 'url' ),
                    method: 'POST',
                    data: {value: value}
                });
            });

            allegati.find('.upload').fileupload({
                dropZone: allegati,
                formData: function (form) {
                    return form.serializeArray();
                },
                dataType: 'json',
                submit: function (e, data) {
                    allegati.find('.upload-form').hide();
                    allegati.find('.upload-loading').show();
                },
                done: function (e, data) {
                    if (data.result.errors.length > 0) {
                        var errorContainer = $('<div class="alert alert-danger"></div>');
                        $.each(data.result.errors, function (index, error) {
                            $('<p>' + error.description + '</p>').appendTo(errorContainer)
                        });
                        errorContainer.prependTo(allegati.find('.allegati_docs'));
                    } else if (typeof data.result.content != 'undefined') {
                        allegati.find('.allegati_docs')
                            .html(data.result.content)
                            .find("tbody")
                            .sortable(allegatiSortableOptions).disableSelection();
                    }
                    allegati.find('.upload-form').show();
                    allegati.find('.upload-loading').hide();
                }
            });
        });
    </script>
{/literal}
{/if}
</div>