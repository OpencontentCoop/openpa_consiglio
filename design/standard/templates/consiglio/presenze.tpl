{def $color_in = '#3c763d'}
{def $color_out = '#f0ad4e'}

<h1>{$seduta.object.name|wash()}</h1>
<h2>{$politico.object.name|wash()} <span class="label label-danger">{$percent}%</span></h2>

<div class="row">
    <div class="col-md-6 col-md-offset-4">


        {foreach $events as $event}

            {if $event.type|eq('event')}
                {if is_set( $event.items )|not()}
                    <div style="margin: 10px 0">
                        <p>
                    <span class="fa-stack fa-lg">
                        <i class="fa fa-clock-o fa-stack-2x"></i>
                    </span>
                            <strong>{$event.name}</strong>
                            <small>{$event.timestamp|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                        </p>
                    </div>
                {else}
                    <div style="margin: 10px 0">
                        <p>
                            {foreach $event.items as $item}
                                {if $item.label|eq('checkin')}
                                    <span class="fa-stack fa-lg"
                                          style="margin-right:10px;color:{if $item.in_out|eq(1)}{$color_in}{else}{$color_out}{/if}">
                                        <i class="fa fa-check-circle fa-stack-2x"></i>
                                    </span>
                                    {*<strong>Intervento dell'utente</strong>*}
                                    <small>{$event.timestamp|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                                {elseif $item.label|eq('beacons')}
                                    <span class="fa-stack fa-lg"
                                          style="margin-right:10px;color:{if $item.in_out|eq(1)}{$color_in}{else}{$color_out}{/if}">
                                        <i class="fa fa-wifi fa-stack-2x"></i>
                                    </span>
                                    {*<strong>Rilevazione automatica</strong>*}
                                    <small>{$event.timestamp|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                                {elseif $item.label|eq('manual')}
                                    <span class="fa-stack fa-lg"
                                          style="margin-right:10px;color:{if $item.in_out|eq(1)}{$color_in}{else}{$color_out}{/if}">
                                        <i class="fa fa-thumbs-up fa-stack-2x"></i>
                                    </span>
                                    {*<strong>Intervento del segretario</strong>*}
                                    <small>{$event.timestamp|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                                {/if}
                            {/foreach}
                        </p>
                    </div>
                {/if}
            {/if}

            {if $event.type|eq('interval')}
                <div style="margin-left: 18px; max-height: 200px; min-height: 30px; display: block; border-left: 4px solid {if $event.is_in}{$color_in}{else}{$color_out}{/if}; height: {$event.percent|mul(2)}px">
                    <p style="line-height:{if $event.percent|mul(2)|gt(30)}{$event.percent|mul(2)}px{else}30px{/if}; color:{if $event.is_in}{$color_in}{else}{$color_out}{/if}">
                        <i class="fa fa-play"></i> <strong>{if $event.is_in}PRESENTE{else}ASSENTE{/if} {$event.percent}%</strong>
                    </p>
                </div>
            {/if}

        {/foreach}
    </div>
</div>

<h3>Dettaglio rilevazioni</h3>
<table class="table table-striped">
    <tr>
        <th>ID</th>
        <th>Data ora</th>
        <th>Metodo</th>
        <th>Rilevazione</th>
    </tr>
    {foreach $detections as $detection}
        <tr>
            <td>{$detection.id}</td>
            <td>{$detection.timestamp|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</td>
            <td>{$detection.label}</td>
            <td>{$detection.in_out}</td>
        </tr>
    {/foreach}
</table>