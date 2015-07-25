<div class="panel-body" style="background: #fff" id="osservazioni_add">
    <div class="row">
        <div class="col-xs-12 osservazioni_docs">
            {include uri=concat('design:', $template_directory, '/parts/osservazioni/data.tpl') post=$post}
        </div>
    </div>
    {if $post.can_add_osservazioni}
        <hr/>
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="well">
                    <h2>Aggiungi osservazione</h2>

                    <form action="" enctype="multipart/form-data" method="post"
                          class="upload-form form-horizontal">
                        <div class="form-group">
                            <label for="OsservazioneTitle"
                                   class="col-sm-2 control-label">Titolo</label>

                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="FileAttributes[name]"
                                       id="OsservazioneTitle"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="OsservazioneType"
                                   class="col-sm-2 control-label">Tipo</label>

                            <div class="col-sm-10">
                                <select class="form-control" name="FileAttributes[type]"
                                        id="OsservazioneType">
                                    <option value="test">Test</option>
                                    <option value="tost">Tost</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="OsservazioneState"
                                   class="col-sm-2 control-label">Visibilità</label>

                            <div class="col-sm-10">
                                <select class="form-control" name="FileProperties[state_identifier]"
                                        id="OsservazioneState">
                                    <option value="visibilita_osservazione_seduta.consiglieri">
                                        Consiglieri
                                    </option>
                                    <option value="visibilita_osservazione_seduta.referenti">
                                        Referenti
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="clearfix">
                    <span class="btn btn-success btn-lg fileinput-button pull-right">
                        <i class="glyphicon glyphicon-plus"></i>
                        <span>Scegli file e salva</span>
                        <input class="DocFile" type="file" name="DocFile[]"
                               data-url="{concat('editorialstuff/file/punto/upload/', $post.object.id, '/osservazioni' )|ezurl(no)}"/>
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
                var osservazioni = $('#osservazioni_add');
                osservazioni.find('.DocFile').fileupload({
                    formData: function (form) {
                        return form.serializeArray();
                    },
                    dataType: 'json',
                    submit: function (e, data) {
                        osservazioni.find('.upload-form').hide();
                        osservazioni.find('.upload-loading').show();
                    },
                    error: function (e, data) {
                        alert(data);
                        osservazioni.find('.upload-form').show();
                        osservazioni.find('.upload-loading').hide();
                    },
                    done: function (e, data) {
                        if (data.result.errors.length > 0) {
                            var errorContainer = $('<div class="alert alert-danger"></div>');
                            $.each(data.result.errors, function (index, error) {
                                $('<p>' + error.description + '</p>').appendTo(errorContainer)
                            });
                            errorContainer.prependTo(osservazioni.find('.osservazioni_docs'));
                        } else if (typeof data.result.content != 'undefined') {
                            osservazioni.find('.osservazioni_docs').html(data.result.content);
                        }
                        osservazioni.find('.upload-form').show();
                        osservazioni.find('.upload-loading').hide();
                    }
                });
            });
        </script>
    {/literal}
    {/if}

</div>