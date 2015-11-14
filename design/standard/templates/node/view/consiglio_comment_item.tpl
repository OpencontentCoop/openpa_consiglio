{def $owner = $node.object.owner}
<div class="panel panel-default">
    <div class="panel-heading">
        <strong>
            {$owner.name|wash()}
        </strong>
        <small>{$node.object.published|l10n('date')} alle ore {$node.object.published|l10n('shorttime')} <span class="pull-right"><i class="fa fa-tag"></i> {$node.parent.name|wash()}</span> </small>
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