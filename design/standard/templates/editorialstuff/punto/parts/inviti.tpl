<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">
            {include uri=concat('design:', $template_directory, '/parts/inviti/data.tpl') post=$post}
        </div>
    </div>
{if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
    <hr />
    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <div class="well">
                <h2>Aggiungi invito</h2>
                <form action="{concat('editorialstuff/action/punto/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" id="add-invitato" class="form-horizontal">

                    <div class="form-group">
                        <label for="Invitato" class="col-sm-2 control-label">Nome</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="ActionParameters[invitato]" id="invitato">
                                <option></option>
                                {foreach fetch( 'editorialstuff', 'posts', hash( 'factory_identifier', 'invitato', 'limit', 100 ) ) as $invitato}
                                    <option value="{$invitato.object_id}">{$invitato.object.name|wash()}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="clearfix">
                        <input type="hidden" name="ActionIdentifier" value="AddInvitato" />
                        <input class="btn btn-success btn-lg fileinput-button pull-right" type="submit" name="AddInvitato" value="Aggiungi">
                    </div>

                </form>
                <div id="add-invitato-loading" class="text-center" style="display: none">
                    <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css','plugins/jquery.fileupload/jquery.fileupload.css'))}
{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryUI', 'bootstrap-editable.min.js', 'plugins/jquery.fileupload/jquery.fileupload.js' ) )}
{literal}
    <script>
        $(function () {
            $(document).on("click", ":submit", function(e){
                var currentAction = $(this).attr('name');
                var form =  $(this).parents('form');
                if ( form.attr('id') == 'add-invitato' ){
                    $('#add-invitato').hide();
                    $('#add-invitato-loading').show();
                    var data = form.serializeArray();
                    data.push({name:currentAction,value:''},{name:'AjaxMode',value:'1'});
                    $.ajax({
                        type: "POST",
                        url: form.attr('action'),
                        data: data,
                        success: function (response) {
                            var self = $('#tableinviti');
                            var url = self.data('url');
                            $.get(url,function (data) {
                                self.parent().html(data);
                                $('#add-invitato').show();
                                $('#add-invitato-loading').hide();
                            });
                        }
                    });
                    e.preventDefault();
                }
            });
            $('.edit-protocollo').editable({
                emptytext: 'nessuno',
                error: function(response, newValue) {if(response.responseJSON.status == 'error') return response.responseJSON.message;}
            });
        });
    </script>
{/literal}
{/if}