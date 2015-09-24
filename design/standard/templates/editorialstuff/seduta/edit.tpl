<div class="row">
    <div class="col-md-12">
        <h1>{$post.object.name|wash()} <small> ore {attribute_view_gui attribute=$post.object.data_map.orario}</small></h1>
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
                    {if is_set( $tab.async_template_uri )}
                        <div class="async-load" data-load_url="{concat('consiglio/data/seduta/',$post.object_id, '/', $tab.async_template_uri)|ezurl(no)}">
                            <p class="text-center"><i class="fa fa-gear fa-spin fa-2x"></i></p>
                        </div>
                    {else}
                        {include uri=$tab.template_uri post=$post}
                    {/if}
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

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'bootstrap-tabdrop.js', 'jquery.editorialstuff_default.js', 'ezjsc::jqueryUI', 'bootstrap-editable.min.js', 'dhtmlxgantt.js' ) )}
{ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css', 'dhtmlxgantt.css'))}

<script>
    {literal}
    $(document).ready(function(){
        $('.async-load').each(function(){
            var container = $(this);
            container.load( container.data('load_url') );
        });
    {/literal}

    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
    {literal}
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

        $(document).on( 'submit', '#edit-verbale', function(e){
            var self = $(this);
            var values = self.serializeArray();
            values.push({name:'SaveVerbale', value:''});
            $.ajax({
                url: self.attr('action'),
                method: 'POST',
                data: values,
                success: function (data) {
                    var container = self.parents('.async-load');
                    container.load( container.data('load_url') );
                }
            });
            e.preventDefault();
        });
    });
    {/literal}
    {/if}
</script>
