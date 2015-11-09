<form>
    <input type="button" class="btn btn-xs btn-info tableToExcel" value="Esporta in formato Excel">
    <table class="table">
        <thead>
          <tr>
            <th colspan="6"><h1>Nota spese {$politico.object.name|wash()} {if $politico.is_in['giunta']}(assessore){/if} <small>{$interval_name}</small></h1></th>
          </tr>
          <tr>
              <th>Data convocazione</th>
              <th>Motivo</th>
              <th>Sede</th>
              {*<th style="vertical-align: middle; text-align: center">Gettone</th>*}
              <th style="vertical-align: middle; text-align: center">Chilometri</th>
              <th style="vertical-align: middle; text-align: center">Spese</th>
          </tr>
        </thead>
        <tbody>
        {foreach $sedute as $seduta}
            {def $can_modify = $seduta.liquidata|not()}
            <tr>
                <td style="vertical-align: middle">{$seduta.data_ora|l10n('date')|wash()} <small>{$seduta.data_ora|l10n('shorttime')}</small></td>
                <td style="vertical-align: middle">{attribute_view_gui attribute=$seduta.object.data_map.organo}</td>
                <td style="vertical-align: middle">{attribute_view_gui attribute=$seduta.object.data_map.luogo}</td>
                {*<td style="vertical-align: middle; text-align: center">
                    <a href="{concat('consiglio/presenze/',$seduta.object.id, '/',$politico.object.id)|ezurl(no)}">
                        {$politico.importo_gettone[$seduta.object.id]}<span class="no-export">€</span>
                    </a>
                </td>*}
                <td style="vertical-align: middle; text-align: center">
                    {if $can_modify}
                    <a href="#" class="editable"
                       data-type="text"
                       data-name="km"
                       data-pk="{$seduta.object.id}"
                       data-url="{concat('/consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_km/', $seduta.object.id)|ezurl(no)}"
                       data-title="Aggiungi km">
                    {/if}
                        {def $km = fetch( content, object, hash( 'remote_id', concat( $seduta.object.id, '_', $politico.object.id ) ) )}
                        {if $km}{$km.data_map.amount.data_float}{else}0{/if}
                        {undef $km}
                    {if $can_modify}
                    </a>
                    {/if}
                </td>
                <td style="vertical-align: middle; text-align: center">
                    <div class="lista-spese" data-load_url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/load_spese/', $seduta.object.id )|ezurl(no)}">
                        {include uri="design:consiglio/gettoni/spese.tpl" seduta=$seduta politico=$politico}
                    </div>
                    {if $can_modify}
                    <a href="#" class="btn btn-success btn-xs no-export"
                       data-toggle="modal"
                       data-target="#addSpesaTemplate"
                       data-seduta="{$seduta.object.id}"
                       data-name="spesa">
                      <i class="fa fa-plus"></i> Aggiungi spesa
                    </a>
                    {/if}
                </td>
            </tr>
            {undef $can_modify}
        {/foreach}
        
          <tr>
            <th colspan="6"><h2>Informazioni</h2></th>
          </tr>
        
          <tr>
            <td colspan="3"><label for="iban">Coordinate Bancarie (codice IBAN)</label></th>
            <td colspan="3">
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
              <td colspan="3"><label for="trattenute">Applicare trattenuta (valore in %)</label></th>
              <td colspan="3">
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
        </tbody>
    </table>
</form>

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'bootstrap-editable.min.js', 'jquery.fileupload.js', 'photobooth_min.js', 'jquery.base64.js','tableExport.js' ) )}
{ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css', 'jquery.fileupload.css'))}
<style>{literal}.photobooth ul, .photobooth ul li{margin: 0; padding: 0; list-style: none}{/literal}</style>
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
            e.preventDefault();
        });

        var reloadListaSpese = function() {
            var listaSpese = $('.lista-spese');
            listaSpese.each( function() {
                $(this).load($(this).data('load_url'))
            });
        };

        var cameraContainer = $('#cameraContainer');
        var modal = $('#addSpesaTemplate');
        var camera = modal.find("#camera");
        var isCameraSupported = (
            navigator.getUserMedia ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia ||
            navigator.oGetUserMedia ||
            navigator.msieGetUserMedia ||
            false
        );
        var cameraIsLoaded = false;

        modal.on('show.bs.modal', function (event) {

            if ( isCameraSupported ) camera.show();
            else cameraContainer.hide();

            var sedutaId = $(event.relatedTarget).data('seduta');
            modal.find('form').append('<input type="hidden" id="seduta" name="seduta" value="'+sedutaId+'"/>');
            modal.find('.upload').fileupload({
                dropZone: modal,
                formData: function (form) {
                    return form.serializeArray();
                },
                dataType: 'json',
                send: function (e, data) {
                    var postData = $(data.form).serializeArray();
                    var ok = true;
                    $.each( postData, function(i,v){
                        if (v.value.length == 0 ) ok = false;
                    });
                    if ( !ok ){
                        alert( "Completa tutti i campi" );
                        return false;
                    }else{
                        modal.find('.upload-form').hide();
                        modal.find('.upload-loading').show();
                    }
                },
                error: function (e, data) {
                    alert(data);
                    modal.find('.upload-form').show();
                    modal.find('.upload-loading').hide();
                },
                done: function (e, data) {
                    modal.find('.upload-form').show();
                    modal.find('.upload-loading').hide();
                    modal.modal('hide');
                    reloadListaSpese();
                }
            });
        });

        modal.on('hide.bs.modal', function (event) {
            var photobooth = camera.data( "photobooth" );
            if ( typeof photobooth == 'object' && cameraIsLoaded) {
                photobooth.destroy();
            }
            camera.hide();
            modal.find("#gallery").empty().hide();
            modal.find("#submit").hide();
            modal.find('form')[0].reset();
            modal.find('form input#seduta').remove();
            cameraIsLoaded = false;
            $("#load-camera").show();
        });


        $(document).on('click', "#load-camera", function(){
            if ( !cameraIsLoaded ) {
                try {
                    var photobooth = camera.photobooth();
                    cameraIsLoaded = true;
                    $("#load-camera").hide();
                    photobooth.on("image", function (event, dataUrl) {
                        modal.find("#gallery").show().html('<img src="' + dataUrl + '" >');
                        modal.find("#submit").show();
                        event.preventDefault();
                    });
                } catch (e) {
                    cameraContainer.hide();
                }
            }
        });

        $(document).on('click', "#submit", function(){
            var postData = modal.find('form').serializeArray();
            var ok = true;
            $.each( postData, function(i,v){
                if (v.value.length == 0 ) ok = false;
            });
            if (!ok) {
                alert( "Completa tutti i campi" );
            }else {
                postData.push({name: 'type', value: "data"});
                postData.push({name: 'image', value: modal.find("#gallery img").attr('src')});
                $.post(cameraContainer.data('url'), postData, function () {
                    modal.modal('hide');
                    reloadListaSpese();
                });
            }
        });
        
        $(document).on('click', '.tableToExcel', function(e){
            var table = $(e.currentTarget).next();            
            table.tableExport({type:'excel',escape:'false'});
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
                            <input class="form-control" type="text" name="Description" id="SpesaTitle"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="SpesaEuro" class="col-sm-2 control-label">Totale in euro</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" name="Amount" id="SpesaEuro"/>
                        </div>
                    </div>

                    <div class="clearfix text-center">
                        <p>
                            <span class="btn btn-success fileinput-button">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>Scegli file dal tuo computer</span>
                                <input class="upload" type="file" name="File[]"
                                       data-url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_spesa' )|ezurl(no)}"/>
                            </span>
                        </p>
                    </div>

                    <div id="cameraContainer" class="clearfix text-center" data-url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/add_spesa' )|ezurl(no)}">
                        <p>oppure</p>
                        <p>
                            <a id="load-camera" href="#" class="btn btn-success">
                                <i class="fa fa-camera"></i> Scatta una foto del documento
                            </a>
                        </p>
                        <div id="camera" style="display: none;height:240px;width:320px" class="center-block"></div>
                        <div id="gallery" style="height:240px;width:320px;display: none" class="center-block"></div>
                        <p>
                            <a id="submit" href="#" class="btn btn-success" style="display: none">
                                <i class="fa fa-save"></i> Salva
                            </a>
                        </p>
                    </div>


                </form>
                <div class="upload-loading" class="text-center" style="display: none">
                    <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
                </div>
            </div>
        </div>
    </div>
</div>