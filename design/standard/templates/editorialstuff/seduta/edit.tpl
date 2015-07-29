<div class="row">
    <div class="col-md-12">
        <h1>{$post.object.name|wash()}</h1>
        {include uri=concat('design:', $template_directory, '/parts/workflow.tpl') post=$post}
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-{if is_set( $post.object.data_map.internal_comments )}9{else}12{/if}">

        <div role="tabpanel">

            <ul class="nav nav-tabs" role="tablist">
                {foreach $post.tabs as $index=> $tab}
                    <li role="presentation"{if $index|eq(0)} class="active"{/if}>
                        <a href="#{$tab.identifier}" aria-controls="{$tab.identifier}"
                           role="tab" data-toggle="tab">{$tab.name}</a>
                    </li>
                {/foreach}
            </ul>

            <div class="tab-content">
                {foreach $post.tabs as $index=> $tab}
                <div role="tabpanel" class="tab-pane{if $index|eq(0)} active{/if}" id="{$tab.identifier}">
                    {include uri=$tab.template_uri post=$post}
                </div>
                {/foreach}
            </div>

        </div>

    </div>

    {if is_set( $post.object.data_map.internal_comments )}
        <div class="col-md-3">
            {include uri=concat('design:', $template_directory, '/parts/comments.tpl') post=$post}
        </div>
    {/if}

</div>


<div id="preview" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="previewlLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'bootstrap-tabdrop.js', 'jquery.editorialstuff_default.js', 'ezjsc::jqueryUI', 'bootstrap-editable.min.js' ) )}
{ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css'))}
{if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
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
{/if}
{/literal}