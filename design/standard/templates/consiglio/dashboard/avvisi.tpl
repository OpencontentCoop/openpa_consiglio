{def $avvisi = fetch( 'consiglio', 'notification_items', hash( limit, 10,
                                                               conditions, hash( 'type', 'Mail', 'sent', true(), 'user_id', fetch( user, current_user ).contentobject_id ),
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
                    <a href="#" data-toggle="modal" data-target="#detail-{$avviso.id}">{$avviso.subject|wash()}</a>
                    <div class="modal fade" tabindex="-1" role="dialog" id="detail-{$avviso.id}">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="previewLabel">Dettaglio notifica</h4>
                                </div>

                                <div class="modal-body">
                                    <dl class="dl-horizontal">
                                        <dt>Oggetto</dt>
                                        <dd>{$avviso.subject|wash()}</dd>
                                        <dt>Testo</dt>
                                        <dd>{$avviso.body}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    {$avviso.type|wash()}
                </td>
            </tr>
            {undef $post}
        {/foreach}
    </table>
{/if}