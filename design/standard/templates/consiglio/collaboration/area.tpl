<div class="clearfix">
    <div class="pull-left img-circle"
         style="margin-right:20px;width:120px; height:120px; background: url({if $referente|has_attribute( 'image' )}{$referente|attribute( 'image' ).content.medium.url|ezroot(no)}{/if}) top center no-repeat; background-size: cover;"></div>
    <h1>{$area.name|wash()}</h1>
</div>
<hr/>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <ul class="nav navbar-nav">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-tags"></i> Tematiche di discussione <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    {if count( $area_tags )|gt(0)}
                        {foreach $area_tags as $area_tag}
                            <li><a href="{concat('consiglio/collaboration/', $referente.id, '/tag-', $area_tag.node_id)|ezurl(no)}">
                                    <i class="fa fa-tag"></i> {$area_tag.name|shorten(45)wash()} <span class="badge">{$area_tag.children_count}</span>
                                </a></li>
                        {/foreach}
                        <li role="separator" class="divider"></li>
                        <li><a href="{concat('consiglio/collaboration/', $referente.id)|ezurl(no)}">Mostra tutto</a></li>
                    {/if}
                </ul>
            </li>
        </ul>
        {if fetch( user, current_user ).contentobject_id|eq($referente.id)}
            <form class="navbar-form navbar-left" method="post" action="{concat('consiglio/collaboration/', $referente.id, '/add_tag')|ezurl(no)}">
                <div class="form-group">
                    <label for="NewAreaName" class="sr-only">Aggiungi nuova tematica</label>
                    <input type="text" class="form-control" id="NewAreaName" name="NewTagName" placeholder="Aggiungi nuova tematica">
                </div>
                <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i></button>
            </form>
        {/if}
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-users"></i> Partecipanti <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    {if count($area_users)|gt(1)}
                        {foreach $area_users as $area_user}
                            <li><a href="#">{$area_user.name|wash()}</a></li>
                        {/foreach}
                    {else}
                        <li><a href="#">Al momento nessun referente locale è stato aggiunto a questa area. Contatta la segreteria per maggiori informazioni.</a></li>
                    {/if}
                </ul>
            </li>
        </ul>
    </div>
</nav>

{if $error}
    <div class="alert alert-danger">
        <h3 style="margin: 0">{$error|wash()}</h3>
    </div>
{/if}

<div class="row">
    <div class="col-md-8">
        {def $page_limit = 100
             $page_url = concat('consiglio/collaboration/', $referente.id )}
        {if $tag}
            <h2><i class="fa fa-tag"></i> {$tag.name|wash()}</h2>
            {set $page_url = concat('consiglio/collaboration/', $referente.id, '/', $tag.node_id )}
            {def $comments = fetch( content, list, hash( parent_node_id, $tag.node_id, class_filter_type, include, class_filter_array, array( 'comment' ), limit, $page_limit, offset, $view_parameters.offset, sort_by, array( published, desc ) ) )}
            {def $comments_count = fetch( content, list_count, hash( parent_node_id, $tag.node_id, class_filter_type, include, class_filter_array, array( 'comment' ) ))}
        {else}
            {def $comments = fetch( content, tree, hash( parent_node_id, $area.node_id, class_filter_type, include, class_filter_array, array( 'comment' ), limit, $page_limit, offset, $view_parameters.offset, sort_by, array( published, desc ) ) )}
            {def $comments_count = fetch( content, tree_count, hash( parent_node_id, $area.node_id, class_filter_type, include, class_filter_array, array( 'comment' ) ))}
        {/if}
        {if $comments_count|gt(0)}

            {foreach $comments as $comment}
                {node_view_gui content_node=$comment view='consiglio_comment_item'}
            {/foreach}

            {include name=navigator
                    uri='design:navigator/google.tpl'
                    page_uri=$page_url
                    item_count=$comments_count
                    view_parameters=$view_parameters
                    item_limit=$page_limit}

        {else}
            {if count( $area_tags )|gt(0)}
                <p>Nessun intervento presente. Intervieni per primo!</p>
            {elseif  fetch( user, current_user ).contentobject_id|eq($referente.id)}
                <div class="alert alert-warning">
                    Per iniziare inserisci una tematica di discussione
                </div>
            {else}
                <div class="alert alert-warning">
                    Nessuna tematica di discussione è attiva al momento.
                </div>
            {/if}
        {/if}

    </div>
    <div class="col-md-4">

        {if count( $area_tags )|gt(0)}
        <h3><i class="fa fa-plus"></i> Aggiungi il tuo intervento</h3>
        <div class="well well-sm clearfix">
            <form class="form" method="post" enctype="multipart/form-data" action="{concat('consiglio/collaboration/', $referente.id, '/add_comment')|ezurl(no)}">
                <div class="form-group">
                    <label for="Text" class="control-label">Testo</label>
                    <textarea class="form-control" rows="5" name="CommentText" id="Text"></textarea>
                </div>
                {if $tag}
                    <input type="hidden" name="Tag" value="{$tag.node_id}" />
                {else}
                    <div class="form-group">
                        <label for="Tag">Tematica</label>
                        <select name="Tag" id="Tag" class="form-control">
                            {foreach $area_tags as $area_tag}
                                <option value="{$area_tag.node_id}">{$area_tag.name|wash()}</option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
                <button type="submit" class="btn btn-success pull-right" name="PublishComment">Pubblica</button>
            </form>
        </div>
        {/if}

        {if count( $area_tags )|gt(0)}
            <h3><i class="fa fa-plus"></i> Aggiungi un file</h3>
            <div class="well well-sm clearfix">
                <form class="form" method="post" enctype="multipart/form-data" action="{concat('consiglio/collaboration/', $referente.id, '/add_file')|ezurl(no)}">
                    <div class="form-group">
                        <label for="File">File</label>
                        <input type="file" id="File" name="CommentFile" />
                    </div>
                    {if $tag}
                        <input type="hidden" name="Tag" value="{$tag.node_id}" />
                    {else}
                        <div class="form-group">
                            <label for="Tag">Tematica</label>
                            <select name="Tag" id="Tag" class="form-control">
                                {foreach $area_tags as $area_tag}
                                    <option value="{$area_tag.node_id}">{$area_tag.name|wash()}</option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                    <button type="submit" class="btn btn-success pull-right" name="PublishFile">Aggiungi</button>
                </form>
            </div>
        {/if}
{*
        <h3><i class="fa fa-tags"></i> Tematiche di discussione</h3>
        <div class="list-group">
            {foreach $area_tags as $area_tag}
                {if and( $tag, $tag.node_id|eq($area_tag.node_id) )}
                <a class="list-group-item active"
                   href="{concat('consiglio/collaboration/', $referente.id)|ezurl(no)}">
                    <span class="badge">{$area_tag.children_count}</span>
                    {$area_tag.name|wash()}
                </a>
                {else}
                    <a class="list-group-item"
                       href="{concat('consiglio/collaboration/', $referente.id, '/tag-', $area_tag.node_id)|ezurl(no)}">
                        <span class="badge">{$area_tag.children_count}</span>
                        {$area_tag.name|wash()}
                    </a>
                {/if}
            {/foreach}
        </div>

        <hr/>
        <h3><i class="fa fa-users"></i> Partecipanti</h3>
        <ul class="list-group">
            {if count($area_users)|gt(1)}
            {foreach $area_users as $area_user}
                <li class="list-group-item">{content_view_gui content_object=$area_user view="politico_line"}</li>
            {/foreach}
            {else}
                <div class="alert alert-warning">
                    Al momento nessun referente locale è stato aggiunto a questa area. Contatta la segreteria per maggiori informazioni.
                </div>
            {/if}
        </ul>
*}
    </div>
</div>