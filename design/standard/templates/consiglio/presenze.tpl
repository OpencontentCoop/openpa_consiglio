{def $color_in = '#5cb85c'}
{def $color_out = '#f0ad4e'}

<h1><a href="{$seduta.editorial_url|ezurl(no)|explode( '/layout/set/modal' )|implode('')}">{$seduta.object.name|wash()}</a></h1>
<h2><a href="{$politico.editorial_url|ezurl(no)|explode( '/layout/set/modal' )|implode('')}">{$politico.object.name|wash()}</a> <span class="label label-success">{$in_percent}%</span> <span class="label label-warning">{$out_percent}%</span></h2>

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
                        <p style="white-space: nowrap">
                            {foreach $event.items as $item}
                                {if $item.type|ne( 'custom' )}
                                    {if $item.type|eq('checkin')}
                                        <span class="fa-stack fa-lg"
                                              style="white-space:nowrap;margin-right:5px;color:{if $item.in_out|eq(1)}{$color_in}{else}{$color_out}{/if}">
                                            <i class="fa fa-check-circle fa-stack-2x"></i>
                                        </span>
                                        <small>{$item.created_time|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                                    {elseif $item.type|eq('beacons')}
                                        <span class="fa-stack fa-lg"
                                              style="white-space:nowrap;margin-right:5px;color:{if $item.in_out|eq(1)}{$color_in}{else}{$color_out}{/if}">
                                            <i class="fa fa-wifi fa-stack-2x"></i>
                                        </span>

                                        <small>{$item.created_time|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                                    {elseif $item.type|eq('manual')}
                                        <span class="fa-stack fa-lg"
                                              style="white-space:nowrap;margin-right:5px;color:{if $item.in_out|eq(1)}{$color_in}{else}{$color_out}{/if}">
                                            <i class="fa fa-thumbs-up fa-stack-2x"></i>
                                        </span>
                                        <small>{$item.created_time|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
                                    {/if}
                                {else}
                                    <span class="fa-stack fa-lg"
                                          style="white-space:nowrap;margin-right:5px;">
                                    <i class="fa {if is_set( $item.icon )}{$item.icon}{else}fa-star{/if} fa-stack-2x"></i>
                                </span>
                                    <small><strong>{$item.label}</strong> {$item.created_time|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</small>
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

{*{$calc|array_sum)}*}

<h3>Dettaglio rilevazioni</h3>
<table class="table table-striped">
    <tr>
        <th>ID</th>
        <th>Data ora</th>
        <th>Rilevazione</th>
        <th>Metodo</th>
    </tr>
    {foreach $detections as $detection}
        {if $detection.type|ne( 'custom' )}
        <tr class="{if $detection.in_out}success{else}warning{/if}">
            <td>{$detection.id}</td>
            <td>{$detection.created_time|datetime( 'custom', '%j/%m/%Y %H:%i:%s' )}</td>
            <td>{if $detection.in_out}Presente{else}Assente{/if}</td>
            <td>
                {if $detection.type|eq('checkin')}
                    <i class="fa fa-check-circle"></i> {if $detection.in_out}Checkin{else}Checkout{/if} dell'utente
                {elseif $detection.type|eq('beacons')}
                    <i class="fa fa-wifi"></i> Rilevazione automatica (beacons)
                {elseif $detection.type|eq('manual')}
                    <i class="fa fa-thumbs-up"></i> Intervento del segretario
                {/if}
            </td>
        </tr>
        {/if}
    {/foreach}
</table>