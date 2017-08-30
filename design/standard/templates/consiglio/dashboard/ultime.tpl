
{def $latest_content = fetch( 'consiglio', 'latest_osservazioni')}

{if $latest_content|count()}

    <table class="table table-striped">
        {foreach $latest_content as $latest_node}
            <tr>
                <td>
                    {$latest_node.object.modified|l10n('shortdate')}
                </td>
                <td>
                    <span class="label label-default">{$latest_node.class_name|wash()}</span>
                    <a href="{$latest_node.url_alias|ezurl('no')}" title="{$latest_node.name|wash()}">{$latest_node.name|shorten('30')|wash()}</a>
                    da  {$latest_node.object.current.creator.name|wash()}
                </td>
            </tr>
        {/foreach}
    </table>


{/if}

{undef $latest_content}
