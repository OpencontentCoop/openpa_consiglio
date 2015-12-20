<div class="clearfix">
    <h1>{$area.object.name|wash()}</h1>
</div>
<hr/>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <ul class="nav navbar-nav">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-tags"></i> Tematiche di discussione <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="{concat('consiglio/collaboration/', $area.object.id)|ezurl(no)}">Indice</a></li>
                    {if count( $area_rooms )|gt(0)}
                        <li role="separator" class="divider"></li>
                        {foreach $area_rooms as $area_room}
                            {if $area_room.is_hidden|not()}
                            <li><a href="{concat('consiglio/collaboration/', $area.object.id, '/room-', $area_room.node_id)|ezurl(no)}">
                                    <i class="fa fa-tag"></i> {$area_room.name|shorten(45)|wash()}
                                </a></li>
                            {/if}
                        {/foreach}
                    {/if}
                </ul>
            </li>
        </ul>
        {if $area.politici_id_list|contains( fetch( user, current_user ).contentobject_id )}
            <form class="navbar-form navbar-left" method="post" action="{concat('consiglio/collaboration/', $area.object.id, '/add_room')|ezurl(no)}">
                <div class="form-group">
                    <label for="NewAreaName" class="sr-only">Aggiungi nuova tematica</label>
                    <input type="text" class="form-control" id="NewAreaName" name="NewRoomName" placeholder="Aggiungi nuova tematica">
                </div>
                <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i></button>
            </form>
        {/if}
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-users"></i> Partecipanti <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    {if count($area_users)|gt(0)}
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

{if $room}
<div class="row">
    <div class="col-md-8">
        {def $page_limit = 100
             $page_url = concat('consiglio/collaboration/', $area.object.id, '/', $room.node_id )}
        <h2><i class="fa fa-tag"></i> {$room.name|wash()}</h2>
        {def $comments = fetch( content, list, hash( parent_node_id, $room.node_id, class_filter_type, include, class_filter_array, array( 'openpa_consiglio_collaboration_comment' ), limit, $page_limit, offset, $view_parameters.offset, sort_by, array( published, desc ) ) )}
        {def $comments_count = fetch( content, list_count, hash( parent_node_id, $room.node_id, class_filter_type, include, class_filter_array, array( 'openpa_consiglio_collaboration_comment' ) ))}

        {if $comments_count|gt(0)}

            {foreach $comments as $comment}
                {node_view_gui content_node=$comment view='consiglio_collaboration_comment_item'}
            {/foreach}

            {include name=navigator
                    uri='design:navigator/google.tpl'
                    page_uri=$page_url
                    item_count=$comments_count
                    view_parameters=$view_parameters
                    item_limit=$page_limit}

        {else}
            <p>Nessun intervento presente. Intervieni per primo!</p>
        {/if}

        {if $can_participate}
        <h3><i class="fa fa-plus"></i> Aggiungi il tuo intervento</h3>
        <div class="well well-sm clearfix">
            <form class="form" method="post" enctype="multipart/form-data" action="{concat('consiglio/collaboration/', $area.object.id, '/add_comment')|ezurl(no)}">
                <div class="form-group">
                    <label for="Text" class="control-label">Testo</label>
                    <textarea class="form-control" rows="5" name="CommentText" id="Text"></textarea>
                </div>
                <input type="hidden" name="Room" value="{$room.node_id}" />
                <button type="submit" class="btn btn-success pull-right" name="PublishComment">Pubblica</button>
            </form>
        </div>
        {/if}
    </div>
    <div class="col-md-4">

        {def $files = fetch( content, list, hash( parent_node_id, $room.node_id, class_filter_type, include, class_filter_array, array( 'openpa_consiglio_collaboration_file' ), sort_by, array( published, desc ) ) )}
        {def $files_count = fetch( content, list_count, hash( parent_node_id, $room.node_id, class_filter_type, include, class_filter_array, array( 'openpa_consiglio_collaboration_file' ) ))}

        {if $files_count|gt(0)}
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>File allegati</strong>
            </div>
            <table class="table">
                {foreach $files as $file}
                    <tr>
                        <td>{node_view_gui content_node=$file view='consiglio_collaboration_file_item'}</td>
                        <td style="white-space: nowrap">{include uri="design:parts/toolbar/node_edit.tpl" current_node=$file}
                            {include uri="design:parts/toolbar/node_trash.tpl" current_node=$file}</td>
                    </tr>
                {/foreach}
                </table>
        </div>
        {/if}

        {if $can_participate}
        <h3><i class="fa fa-plus"></i> Aggiungi un file</h3>
        <div class="well well-sm clearfix">
            <form class="form" method="post" enctype="multipart/form-data" action="{concat('consiglio/collaboration/', $area.object.id, '/add_file')|ezurl(no)}">
                <div class="form-group">
                    <label for="File">File</label>
                    <input type="file" id="File" name="CommentFile" />
                </div>
                <input type="hidden" name="Room" value="{$room.node_id}" />
                <button type="submit" class="btn btn-success pull-right" name="PublishFile">Aggiungi</button>
            </form>
        </div>
        {/if}
    </div>
</div>

{else}
    <div class="row">
        <div class="col-md-12">
        {if count( $area_rooms )|gt(0)}
            <table class="table">
                <tr>
                    <th>Titolo</th>
                    <th style="white-space: nowrap">Creata il</th>
                    <th style="white-space: nowrap">Ultima modifica</th>
                    <th></th>
                    <th></th>
                    {if $area.politici_id_list|contains( fetch( user, current_user ).contentobject_id )}
                        <th></th>
                    {/if}
                </tr>
                {foreach $area_rooms as $area_room}
                    <tr>
                        <td>
                            {if $area_room.is_hidden|not}
                            <a href="{concat('consiglio/collaboration/', $area.object.id, '/room-', $area_room.node_id)|ezurl(no)}">
                                <i class="fa fa-tag"></i> {$area_room.name|wash()}
                            </a>
                            {else}
                                <i class="fa fa-tag"></i> {$area_room.name|wash()}
                            {/if}
                        </td>
                        <td style="white-space: nowrap">
                            {$area_room.object.published|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}
                        </td>
                        <td style="white-space: nowrap">
                            {$area_room.modified_subnode|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}
                        </td>
                        <td style="white-space: nowrap">
                            {$area_room.children_count} interventi
                        </td>
                        <td>
                            {if $area_room.is_hidden|not}
                                <a class="btn btn-primary btn-sm" href="{concat('consiglio/collaboration/', $area.object.id, '/room-', $area_room.node_id)|ezurl(no)}">Accedi</a>
                            {/if}
                        </td>
                        {if $area.politici_id_list|contains( fetch( user, current_user ).contentobject_id )}
                            {if $area_room.is_hidden}
                                <td><a class="btn btn-danger btn-sm" href="{concat('consiglio/collaboration/', $area.object.id, '/hide-', $area_room.node_id)|ezurl(no)}">Rivela</a></td>
                            {else}
                                <td><a class="btn btn-success btn-sm" href="{concat('consiglio/collaboration/', $area.object.id, '/show-', $area_room.node_id)|ezurl(no)}">Nascondi</a></td>
                            {/if}

                        {/if}
                    </tr>
                {/foreach}
            </table>
        {elseif  fetch( user, current_user ).contentobject_id|eq($area.object.id)}
            <div class="alert alert-warning">
                Per iniziare inserisci una tematica di discussione
            </div>
        {else}
            <div class="alert alert-warning">
                Nessuna tematica di discussione è attiva al momento.
            </div>
        {/if}
        </div>
    </div>
{/if}