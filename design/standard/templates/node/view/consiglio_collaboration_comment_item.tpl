{def $owner = $node.object.owner}
<div class="clearfix">
    <div class="panel panel-default {if $owner.id|eq(fetch(user,current_user).contentobject_id)}pull-right panel-success{else}pull-left panel-info{/if}">
        <div class="panel-heading">
            <strong>
                {$owner.name|wash()}
            </strong>
            <small>{$node.object.published|l10n('date')} alle ore {$node.object.published|l10n('shorttime')} {*<span class="pull-right"><i class="fa fa-tag"></i> {$node.parent.name|wash()}</span> *}</small>
            {include uri="design:parts/toolbar/node_edit.tpl" current_node=$node}
            {include uri="design:parts/toolbar/node_trash.tpl" current_node=$node}
        </div>
        <div class="panel-body">
            {if $node|has_attribute('message')}
                <p>{attribute_view_gui attribute=$node|attribute('message')}</p>
            {/if}
            {if $node|has_attribute('file')}
                <div>{attribute_view_gui attribute=$node|attribute('file')}</div>
            {/if}
        </div>
    </div>
</div>