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
                        <div class="async-load" data-identifier="{$tab.identifier}" data-load_url="{concat('consiglio/data/seduta/',$post.object_id, '/', $tab.async_template_uri)|ezurl(no)}">
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

{ezscript_require( array( 
    'modernizr.min.js', 
    'ezjsc::jquery', 
    'bootstrap-tabdrop.js', 
    'jquery.editorialstuff_default.js', 
    'ezjsc::jqueryUI', 
    'bootstrap/tooltip.js', 
    'bootstrap/popover.js', 
    'bootstrap-editable.min.js', 
    'dhtmlxgantt.js',
    'summernote/summernote.js',
    'jquery.confirm.min.js'
) )}
{ezcss_require(array(
    'bootstrap3-editable/css/bootstrap-editable.css', 
    'dhtmlxgantt.css',
    'summernote/summernote.css'
))}

<script>
    {literal}
    var loadVerbaleEvents = function(){
        $('#ConfirmCreateVerbale').on('click', function(e){
            e.preventDefault();
            $.confirm({
                text: 'Confermi la generazione del verbale?',
                confirmButton: "Confermo",
                cancelButton: "Annulla",
                confirm: function() {
                    $('#CreateVerbale').trigger('click');
                },
                cancel: function() {                    
                    return false;
                }
            });
        });
        $('.resetVerbale').on('click', function(e){            
            var text = $(this).next().val();            
            var identifier = $(this).data('identifier');
            var input = $(this).parents('tr').find('#verbaleField-'+identifier);
            if (!input.is(':disabled')){                
                if (input.prop("tagName") == 'INPUT'){
                    input.val(text);
                }else{                
                    input.summernote('code', text);
                }
            }
            e.preventDefault();
        });
        $('textarea.verbaleField').summernote({
            "toolbar":[
                ['style',  ['bold', 'italic', 'underline']],                
                ['para',   ['ul', 'ol']],
                ['insert', ['table', 'hr']]
            ]
        });
    };

    $(document).ready(function(){
        $('.async-load').each(function(){
            var container = $(this);
            container.load( container.data('load_url'), function(){
                if(container.data('identifier') == 'verbale') loadVerbaleEvents(); 
            });
        });
    });
    {/literal}

    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
    {literal}
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
                    container.load( container.data('load_url'), function(){
                        if(container.data('identifier') == 'verbale') loadVerbaleEvents(); 
                    });
                }
            });
            e.preventDefault();
        });        
    });
    {/literal}
    {/if}
</script>
