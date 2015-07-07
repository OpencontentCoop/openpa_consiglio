<div class="panel-body" style="background: #fff">
    {def $utenti_per_notifiche = $post.notification_subscribers}
    <div class="row">
        <div class="col-xs-12">
            <h2>Iscrizioni notifiche</h2>
            <table class="table table-striped">
                <tbody>
                {foreach $utenti_per_notifiche as $utenti_per_notifica}
                    <tr>
                        <th>{$utenti_per_notifica.name|wash()}</th>
                        <td>{foreach $utenti_per_notifica .user_id_list as $user_id}{$user_id}{delimiter},{/delimiter}{/foreach}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
