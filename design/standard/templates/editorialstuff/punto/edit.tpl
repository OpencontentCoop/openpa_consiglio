<div class="row">
    <div class="col-md-12">
        <h1>
            {$post.object.name|wash()} <small>della {$post.seduta.object.name|wash()}</small>
        </h1>
        <h2>{$post.object.data_map.oggetto.content|wash()}</h2>
        {include uri=concat('design:', $template_directory, '/parts/workflow.tpl') post=$post}
        {if $post.can_share}
            {if $post.is_shared|not()}
                <form action="{concat('consiglio/share')|ezurl(no)}" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="Factory" value="punto"/>
                    <input type="hidden" name="Id" value="{$post.object_id}"/>
                    <p class="clearfix">
                        <button class="btn btn-primary" type="submit" name="Share"><i class="fa fa-share-alt"></i> Crea con questo punto una nuova discussione nell'area collaborativa</button>
                    </p>
                </form>
            {else}
                <a href="{$post.shared_url|ezurl(no)}" class="btn btn-default"><i class="fa fa-share-alt"></i> Punto in discussione nell'area collaborativa</a>
            {/if}
        {/if}
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

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'bootstrap-tabdrop.js', 'jquery.editorialstuff_default.js', 'ezjsc::jqueryUI', 'bootstrap-editable.min.js', ) )}
{ezcss_require(array('bootstrap3-editable/css/bootstrap-editable.css','jquery.fileupload.css'))}