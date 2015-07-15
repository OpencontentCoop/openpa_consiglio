{def $avvisi = fetch( 'consiglio', 'notification_items', hash( limit, 10,
                                                               conditions, hash( 'user_id', fetch( user, current_user ).contentobject_id ),
                                                               sort, hash( 'created_time', 'desc' ) ) )}

{if count( $avvisi )}
    <table class="table table-striped">
        {foreach $avvisi as $avviso}
            {def $post = $avviso.post_object}
            <tr>
                <td>
                    {if $avviso.sent}<i class="fa fa-check"></i> {/if}
                </td>
                <td>
                    {$avviso.created_time|l10n('shortdate')}
                </td>
                <td>
                    {if $post}
                        <a href="{$post.editorial_url|ezurl(no)}">{$avviso.subject|wash()}</a>
                    {else}
                        {$avviso.subject|wash()}
                    {/if}
                </td>
                <td>
                    {$avviso.type|wash()}
                </td>
            </tr>
            {undef $post}
        {/foreach}
    </table>
{/if}