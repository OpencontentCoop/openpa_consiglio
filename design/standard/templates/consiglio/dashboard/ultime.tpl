{def $latest_content = fetch( 'consiglio', 'latest_osservazioni')}
{if $latest_content|count()}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Le tue ultime osservazioni</h3>
    </div>
    <div class="panel-body">
        <table class="table table-striped">
            {foreach $latest_content as $latest_node}
                <tr>
                    <td>
                        {$latest_node.object.modified|l10n('shortdate')}
                    </td>
                    <td>
                        <span class="label label-default">{$latest_node.class_name|wash()}</span>
                        <a href="{concat('editorialstuff/edit/osservazioni/', $latest_node.object.id)|ezurl(no)}" title="{$latest_node.name|wash()}">{$latest_node.name|shorten('30')|wash()}</a>
                        da  {$latest_node.object.current.creator.name|wash()}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>
{/if}
{undef $latest_content}