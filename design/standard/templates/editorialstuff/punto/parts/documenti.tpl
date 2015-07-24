<div class="panel-body" style="background: #fff"  id="allegati_add">
    <div class="row">
        <div class="col-xs-12 allegati_docs">
            {include uri=concat('design:', $template_directory, '/parts/allegati_seduta/data.tpl') post=$post}
        </div>
    </div>
{if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
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
                            <select class="form-control" name="FileAttributes[type]" id="DocType">
                                <option value="test">Test</option>
                                <option value="tost">Tost</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="DocState" class="col-sm-2 control-label">Visibilità</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="FileProperties[state_identifier]" id="DocState">
                                <option value="visibilita_allegato_seduta.consiglieri">Consiglieri</option>
                                <option value="visibilita_allegato_seduta.referenti">Referenti</option>
                            </select>
                        </div>
                    </div>

                    <div class="clearfix">
                        <span class="btn btn-success btn-lg fileinput-button pull-right">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>Scegli file e salva</span>
                            <input class="DocFile" type="file" name="DocFile[]" data-url="{concat('editorialstuff/file/punto/upload/', $post.object.id, '/documenti' )|ezurl(no)}" />
                        </span>
                    </div>

                </form>
                <div class="upload-loading" class="text-center" style="display: none">
                    <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
                </div>
            </div>
        </div>
    </div>
    {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::jqueryUI', 'plugins/jquery.fileupload/jquery.fileupload.js' ) )}
    {ezcss_require( 'plugins/jquery.fileupload/jquery.fileupload.css' )}
{literal}
    <script>
        $(function () {
            var allegati = $('#allegati_add');
            allegati.find('.DocFile').fileupload({
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
                        allegati.find('.allegati_docs').html(data.result.content);
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