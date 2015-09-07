<div class="list-group">
    {foreach $post.votazioni as $votazione}
        <div class="list-group-item{if $votazione.current_state.identifier|eq('in_progress')} list-group-item-danger{elseif $votazione.current_state.identifier|eq('closed')} list-group-item-info{/if}">

            <a href="#"
               id="viewVotazione-{$votazione.object_id}"
               data-toggle="modal"
               data-target="#risultatiVotazioneTemplate"
               data-modal_configuration="infoVotazione"
               data-load_url="{concat('consiglio/data/votazione/',$votazione.object_id,'/parts:risultato_votazione')|ezurl(no)}">
                <b>{*$votazione.object.id|wash()*}{$votazione.object.name|wash()}</b>
                <small>{if $votazione.object.data_map.punto.has_content}{$votazione.object.data_map.punto.content.name|wash()}{else}seduta{/if}</small>
            </a>

            {if $votazione.current_state.identifier|eq('pending')}
                <a href="#" class="remove_votazione"
                   data-remove_votazione="{$votazione.object_id}"
                   data-remove_action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/removeVotazione')|ezurl(no)}">
                    <i class="fa fa-trash"></i>
                </a>
            {/if}

            {if $votazione.current_state.identifier|eq('closed')}
                <button class="btn btn-sm btn-block btn-info"
                        data-toggle="modal"
                        data-target="#risultatiVotazioneTemplate"
                        data-modal_configuration="risultatiVotazione"
                        data-load_url="{concat('consiglio/data/votazione/',$votazione.object_id,'/parts:risultato_votazione')|ezurl(no)}">
                    Risultati
                </button>
            {elseif $votazione.current_state.identifier|eq('pending')}
                <button class="btn btn-sm btn-block btn-warning start_votazione"
                        data-add_to_verbale="Inizio votazione {$votazione.object.name|wash()}"
                        data-verbale="{if $votazione.object.data_map.punto.has_content}{$votazione.object.data_map.punto.content.id}{else}{$post.object_id}{/if}"
                        data-votazione="{$votazione.object_id}"
                        data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/startVotazione')|ezurl(no)}">
                    Apri votazione
                </button>
            {elseif $votazione.current_state.identifier|eq('in_progress')}
                <button class="stopVotazione btn btn-sm btn-block btn-danger stop_votazione"
                        data-add_to_verbale="Fine votazione {$votazione.object.name|wash()}"
                        data-verbale="{if $votazione.object.data_map.punto.has_content}{$votazione.object.data_map.punto.content.id}{else}{$post.object_id}{/if}"
                        data-votazione="{$votazione.object_id}"
                        data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/stopVotazione')|ezurl(no)}">
                    Chiudi votazione
                </button>
            {/if}
            <br/>
        </div>
    {/foreach}
</div>

