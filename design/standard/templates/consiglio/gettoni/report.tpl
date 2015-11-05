<form>
    <h1>Nota spese {$politico.object.name|wash()} {if $politico.is_in['giunta']}(assessore){/if} <small>{$interval_name}</small></h1>

    <table class="table">
        <tr>
            <th>Data convocazione</th>
            <th>Motivo</th>
            <th>Sede</th>
            <th style="vertical-align: middle; text-align: center">Gettone</th>
            <th style="vertical-align: middle; text-align: center">Km</th>
            <th style="vertical-align: middle; text-align: center">Spese e pezze giustificative</th>
        </tr>
        {foreach $sedute as $seduta}
            <tr>
                <td style="vertical-align: middle">{$seduta.data_ora|l10n('date')} <small>{$seduta.data_ora|l10n('shorttime')}</small></td>
                <td style="vertical-align: middle">{attribute_view_gui attribute=$seduta.object.data_map.organo}</td>
                <td style="vertical-align: middle">{attribute_view_gui attribute=$seduta.object.data_map.luogo}</td>
                <td style="vertical-align: middle; text-align: center">{$politico.importo_gettone[$seduta.object.id]}
                    €
                </td>
                <td style="vertical-align: middle; text-align: center">
                    <a href="#" class="editable"
                       data-type="text"
                       data-name="km"
                       data-pk="{$seduta.object.id}"
                       data-url="{concat('/consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_km/', $seduta.object.id)|ezurl(no)}"
                       data-title="Aggiungi km">
                        0
                    </a>
                </td>
                <td style="vertical-align: middle; text-align: center">
                    <div class="lista-spese"
                         data-load_url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/load_spese/', $seduta.object.id )|ezurl(no)}">
                        {include uri="design:consiglio/gettoni/spese.tpl" seduta=$seduta.object.id politico=$politico.object.id}
                    </div>
                    <a href="#" class="btn btn-success btn-xs"
                       data-toggle="modal"
                       data-target="#addSpesaTemplate"
                       data-seduta="{$seduta.object.id)}"
                       data-name="spesa">
                    <i class="fa fa-plus"></i> Aggiungi spesa
                    </a>
                </td>
            </tr>
        {/foreach}
    </table>

    <h2>Informazioni</h2>

    <table class="table">
        <tr>
            <th><label for="iban">Coordinate Bancarie (codice IBAN)</label></th>
            <td>
                <a href="#" class="editable"
                   data-type="text"
                   data-name="iban"
                   data-pk="{$politico.object.id}"
                   data-url="{concat('/consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_iban')|ezurl(no)}"
                   data-title="Modifica IBAN">
                    {$iban}
                </a>
            </td>
        </tr>
        <tr>
            <th><label for="trattenute">Applicare trattenuta (valore in %)</label></th>
            <td>
                <a href="#" class="editable"
                   data-type="text"
                   data-name="trattenute"
                   data-pk="{$politico.object.id}"
                   data-url="{concat('/consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_trattenute')|ezurl(no)}"
                   data-title="Modifica percentuale trattenute">
                    {$trattenute}
                </a>
            </td>
        </tr>
    </table>
</form>

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'bootstrap-editable.min.js', 'jquery.fileupload.js' ) )}
{ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css', 'jquery.fileupload.css'))}
<script type="application/javascript" src="{'javascript/jQuery-webcam/jquery.webcam.js'|ezdesign(no)}"></script>

<script>{literal}
    $(document).ready(function () {
        $('.editable').editable({
            emptytext: 'Nessun valore inserito'
        });

        $(document).on('click','.remove-spesa',function(e){
            var removeSpesa = $(e.currentTarget);
            $.get(removeSpesa.data('url'),function(){
                reloadListaSpese();
            });
        })

        var reloadListaSpese = function() {
            var listaSpese = $('.lista-spese');
            listaSpese.each( function() {
                $(this).load($(this).data('load_url'))
            });
        };

        var cameraContainer = $('#cameraContainer');
        var modal = $('#addSpesaTemplate');
        var isFlashEnabled = function () {
            var hasFlash = false;
            try {
                var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
                if(fo) hasFlash = true;
            }catch(e){
                if(navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) hasFlash = true;
            }
            return hasFlash;
        };
        if ( isFlashEnabled ) cameraContainer.show();

        var pos = 0;
        var ctx = null;
        var cam = null;
        var image = null;
        var canvas = document.getElementById("canvas");

        var resetCanvas = function(){
            if (canvas.getContext) {
                ctx = document.getElementById("canvas").getContext("2d");
                ctx.clearRect(0, 0, 320, 240);
                var img = new Image();
                img.src = {/literal}{"images/blank.gif"|ezdesign()}{literal};
                img.onload = function() {
                    ctx.drawImage(img, 320, 240);
                };
                image = ctx.getImageData(0, 0, 320, 240);
            }else{
                cameraContainer.hide();
            }
        };
        resetCanvas();

        $("#camera").webcam({
            width: 320,
            height: 240,
            mode: "callback",
            swffile: {/literal}{"javascript/jQuery-webcam/jscam_canvas_only.swf"|ezdesign()}{literal},
            onTick: function() {},
            onSave: function(data) {
                var col = data.split(";");
                var img = image;
                var modal = $('.modal');
                for(var i = 0; i < 320; i++) {
                    var tmp = parseInt(col[i]);
                    img.data[pos + 0] = (tmp >> 16) & 0xff;
                    img.data[pos + 1] = (tmp >> 8) & 0xff;
                    img.data[pos + 2] = tmp & 0xff;
                    img.data[pos + 3] = 0xff;
                    pos+= 4;
                }
                if (pos >= 0x4B000) {
                    ctx.putImageData(img, 0, 0);
                    var postData = modal.find('form').serializeArray();
                    postData.push({name: 'type', value: "data"});
                    postData.push({name: 'image', value: canvas.toDataURL("image/png")});
                    $.post(cameraContainer.data('url'), postData, function () {
                        modal.find('form')[0].reset();
                        modal.find('form input#seduta').remove();
                        modal.modal('hide');
                        reloadListaSpese();
                        resetCanvas();
                    });
                    pos = 0;
                }
            },
            onCapture: function() {webcam.save();},
            debug: function() {},
            onLoad: function() {}
        });

        $('#cheese').bind('click',function(e){
            var postData = modal.find('form').serializeArray();
            var ok = true;
            $.each( postData, function(i,v){
                if (v.value.length == 0 ) ok = false;
            });
            if (!ok) {
                alert( "Completa tutti i campi" );
            }else {
                webcam.capture();
            }
            e.preventDefault();
        });

        $('.modal').on('show.bs.modal', function (event) {
            var sedutaId = $(event.relatedTarget).data('seduta');
            modal.find('form').append('<input type="hidden" id="seduta" name="seduta" value="'+sedutaId+'"/>');
            modal.find('.upload').fileupload({
                dropZone: modal,
                formData: function (form) {
                    return form.serializeArray();
                },
                dataType: 'json',
                submit: function (e, data) {
                    var postData = $(data.form).serializeArray();
                    var ok = true;
                    $.each( postData, function(i,v){
                        if (v.value.length == 0 ) ok = false;
                    });
                    if ( !ok ){
                        alert( "Completa tutti i campi" );
                        return false;
                    }
                    modal.find('.upload-form').hide();
                    modal.find('.upload-loading').show();
                },
                error: function (e, data) {
                    alert(data);
                    modal.find('.upload-form').show();
                    modal.find('.upload-loading').hide();
                },
                done: function (e, data) {
                    modal.find('.upload-form').show();
                    modal.find('.upload-loading').hide();
                    modal.find('form')[0].reset();
                    modal.find('form input#seduta').remove();
                    modal.modal('hide');
                    reloadListaSpese();
                }
            });
        });

    });
{/literal}</script>

<div id="addSpesaTemplate" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="previewLabel">Aggiungi spesa</h4>
            </div>
            <div class="modal-body">

                <form action="" enctype="multipart/form-data" method="post"
                      class="upload-form form-horizontal">
                    <div class="form-group">
                        <label for="SpesaTitle" class="col-sm-2 control-label">Descrizione</label>

                        <div class="col-sm-10">
                            <input class="form-control" name="Description" id="SpesaTitle"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="SpesaEuro" class="col-sm-2 control-label">Totale in euro</label>

                        <div class="col-sm-10">
                            <input class="form-control" name="Amount" id="SpesaEuro"/>
                        </div>
                    </div>

                    <div class="clearfix text-center">
                        <span class="btn btn-success fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>Scegli file dal tuo computer</span>
                            <input class="upload" type="file" name="File[]"
                                   data-url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_spesa' )|ezurl(no)}"/>
                        </span>
                    </div>

                    <div id="cameraContainer" class="clearfix text-center" style="display: none" data-url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_spesa' )|ezurl(no)}">
                        oppure
                        <p><a class="btn btn-success" href="#" id="cheese">Scatta una foto del documento</a></p>
                        <div id="camera"></div>
                        <canvas id="canvas"height="240" width="320"></canvas>
                    </div>


                </form>
                <div class="upload-loading" class="text-center" style="display: none">
                    <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
                </div>
            </div>
        </div>
    </div>
</div>