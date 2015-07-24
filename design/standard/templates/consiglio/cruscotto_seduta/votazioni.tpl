<div class="widget_title">
    <h3>Votazioni</h3>
</div>
<div class="widget_content">

    <ul class="side_menu">
        {foreach $post.votazioni as $votazione}
            <li>
                <a href="#">
                    <b>{$votazione.object.id|wash()} {$votazione.object.name|wash()}</b>
                    <small>{if $votazione.object.data_map.punto.has_content}{$votazione.object.data_map.punto.content.name|wash()}{else}seduta{/if}</small>
                </a>
                {if $votazione.current_state.identifier|eq('closed')}
                    <button class="btn btn-md btn-block btn-info">
                        Risultati
                    </button>
                {elseif $votazione.current_state.identifier|eq('pending')}
                    <button class="btn btn-md btn-block btn-warning" data-toggle="modal"
                            data-votazione_title="{$votazione.object.name|wash()}"
                            data-votazione="{$votazione.object_id}"
                            data-action="startVotazione"
                            data-target="#startVotazioneTemplate">
                        Apri votazione
                    </button>
                {/if}
                <br/>
            </li>
        {/foreach}
    </ul>
</div>
<a id="seduta_startstop_button" class="btn btn-danger btn-lg btn-block"
   data-toggle="modal"
   data-action="creaVotazione"
   data-target="#creaVotazioneTemplate">
    <i class="fa fa-plus"></i> Crea votazione
</a>
