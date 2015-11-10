<div class="list-group">
    {foreach $post.odg as $index => $punto}
        <div class="list-group-item{if $punto.current_state.identifier|eq('in_progress')} list-group-item-success{elseif $punto.current_state.identifier|eq('closed')} list-group-item-info{/if}">

            <a href="#" data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/launchMonitorPunto/',$punto.object_id|ezurl())}" class="btn btn-info btn-xs launch_monitor_punto pull-right"><i class="fa fa-desktop"></i></a>

            <p>
                <a href="#" class="show-verbale" data-verbale_id="{$punto.object.id}">
                    <strong>{$punto.object.name|wash()}</strong><br/>
                    {$punto.object.data_map.oggetto.content|wash()}
                </a>
            </p>
            {if $punto.seduta.current_state.identifier|eq('in_progress')}
                {if and( $punto.current_state.identifier|eq('published'), $punto.seduta.current_punto|not() )}
                    <p>
                        <a class="btn btn-success btn-sm btn-block punto_start_stop"
                           data-punto_id="{$punto.object_id}"
                           data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/startPunto/',$punto.object_id)|ezurl(no)}"
                           data-add_to_verbale="Inizio trattazione">
                            Inizia trattazione
                        </a>
                    </p>
                {elseif $punto.current_state.identifier|eq('in_progress')}
                    <p>
                        <a class="btn btn-danger btn-sm btn-block punto_start_stop"
                           data-punto_id="{$punto.object_id}"
                           data-action_url="{concat('consiglio/cruscotto_seduta/',$post.object_id,'/stopPunto/',$punto.object_id)|ezurl(no)}"
                           data-add_to_verbale="Fine trattazione">
                            Concludi trattazione
                        </a>
                    </p>
                {/if}
            {/if}
        </div>
    {/foreach}
</div>