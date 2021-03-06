{def $partecipanti = $post.partecipanti}
{def $componenti_non_consiglieri_id_list = $post.organo.componenti_non_consiglieri_id_list}
<table class="table table-striped">
    {foreach $partecipanti as $partecipante}
        <tr class="partecipante{if $registro_presenze.hash_user_id[$partecipante.object_id]|not} blurred{/if}"
            data-partecipante="{$partecipante.object_id}"
            data-last_update="{$registro_presenze.hash_user_id_presenza[$partecipante.object_id].created_time}">
            <td class="stato-presenza"{if $use_app|not()} style="display: none" {/if}>
                <span class="checkin {if $registro_presenze.hash_user_id_presenza[$partecipante.object_id].has_checkin}text-success{else}text-muted{/if}"><i class="fa fa-check-circle"></i></span>
                <span class="beacons {if $registro_presenze.hash_user_id_presenza[$partecipante.object_id].has_beacons}text-success{else}text-muted{/if}"><i class="fa fa-wifi"></i></span>
                <span class="manual {if $registro_presenze.hash_user_id_presenza[$partecipante.object_id].has_manual}text-success{else}text-muted{/if}"><i class="fa fa-thumbs-up"></i></span>
            </td>
            <td class="foto">
                <span style="background-image: url({content_view_gui content_object=$partecipante.object view="image_src" image_class='small'})"></span>
            </td>
            <td class="nome">{if ezini('DebugSettings', 'DebugOutput')|eq('enabled')}{$partecipante.object_id|wash()} {/if}
                {if $componenti_non_consiglieri_id_list|contains($partecipante.object_id)}<em>{$partecipante.object.name|wash()}</em>{else}{$partecipante.object.name|wash()}{/if}
            </td>
            <td class="actions">
                <div class="btn-group" style="white-space: nowrap">
                    <a class="mark-as-in btn btn-success btn-{if $enable_votazione}xs{else}md{/if}"
                       style="float: none;"
                       data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/markPresente?uid=',$partecipante.object_id )|ezurl(no)}"
                       title="Segna presente">
                        <i class="fa fa-thumbs-up"></i></a>
                    <a class="mark-as-out btn btn-danger btn-{if $enable_votazione}xs{else}md{/if}"
                       style="float: none;"
                       data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/markAssente?uid=',$partecipante.object_id )|ezurl(no)}"
                       title="Segna assente">
                        <i class="fa fa-close fa-times"></i></a>
                </div>
            </td>
            <td class="stato-votazione"{if $enable_votazione|not()} style="display: none" {/if}>
                <a style="display:none" class="btn btn-default btn-xs mark_invalid"
                   data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/markVotoInvalid?uid=',$partecipante.object_id )|ezurl(no)}">
                    <i class="fa fa-warning"></i>
                </a>
            </td>
        </tr>
    {/foreach}
</table>
