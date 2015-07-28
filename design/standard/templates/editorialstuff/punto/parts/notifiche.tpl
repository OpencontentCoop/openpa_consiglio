<div class="panel-body" style="background: #fff">

    {def $avvisi_da_inviare = fetch( 'consiglio', 'notification_items', hash( limit, 10, conditions, hash( 'object_id', $post.object_id, 'sent', 0 ), sort, hash( 'created_time', 'desc' ) ) )}

    {if count( $avvisi_da_inviare )}
        <h2>Avvisi in attesa di invio</h2>
        <table class="table table-striped">
            <tr>
                <th>Data di creazione</th>
                <th>Destinatario</th>
                <th>Oggetto</th>
                <th>Tipo di avviso</th>
                <th>Data di invio prevista</th>
            </tr>
            {foreach $avvisi_da_inviare as $avviso}
                <tr>
                    <td>
                        {$avviso.created_time|l10n('shortdatetime')}
                    </td>
                    <td>
                        {fetch( content, object, hash( 'object_id', $avviso.user_id )).name|wash()}
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
                    <td> {$avviso.expected_send_time|l10n('shortdatetime')}</td>
                </tr>
            {/foreach}
        </table>
    {/if}

    {def $avvisi = fetch( 'consiglio', 'notification_items', hash( limit, 10, conditions, hash( 'object_id', $post.object_id, 'sent', 1 ), sort, hash( 'created_time', 'desc' ) ) )}

    {if count( $avvisi )}
        <h2>Avvisi inviati</h2>
        <table class="table table-striped">
            <tr>
                <th>Data di creazione</th>
                <th>Destinatario</th>
                <th>Oggetto</th>
                <th>Tipo di avviso</th>
                <th>Data di invio</th>
            </tr>
            {foreach $avvisi as $avviso}
                <tr>
                    <td>
                        {$avviso.created_time|l10n('shortdatetime')}
                    </td>
                    <td>
                        {fetch( content, object, hash( 'object_id', $avviso.user_id )).name|wash()}
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
                    <td>
                        {if $avviso.sent}
                            {$avviso.sent_time|l10n('shortdatetime')}
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {/if}

    {def $utenti_per_notifiche = $post.notification_subscribers}
    <div class="row">
        <div class="col-xs-12">
            <h2>Iscrizioni avvisi</h2>
            <table class="table table-striped">
                <tbody>
                {foreach $utenti_per_notifiche as $utenti_per_notifica}
                    <tr>
                        <th>{$utenti_per_notifica.name|wash()}</th>
                        <td>
                            <ul class="list-inline">
                            {foreach $utenti_per_notifica.user_id_list as $user_id}<li>{fetch( content, object, hash( 'object_id', $user_id )).name|wash()}</li>{/foreach}
                            </ul>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
