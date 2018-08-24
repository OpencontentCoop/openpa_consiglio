{def $currentPunto = 0}{foreach $post.odg as $index => $punto}{if $punto.current_state.identifier|eq('in_progress')}{set $currentPunto = $punto.object.id}{/if}{/foreach}
<form>
    <table class="table">
        <tbody>
        {foreach $post.verbale_fields as $identifier => $params}
        <tr class="verbaleRow" data-verbale_id="{$identifier}">            
            <td>
                <div class="clearfix" style="padding-bottom: 5px;">
                    <strong>{$params.name|wash()}</strong>
                    
                    <div style="white-space: nowrap;" class="pull-right">
                        {if $params.default_value|ne('')}
                        <a class="btn btn-info btn-xs launch_monitor_verbale" data-action_url="{concat('consiglio/cruscotto_seduta/',$seduta.object_id,'/launchMonitorVerbale/', $identifier)|ezurl(no)}" href="#"><i class="fa fa-desktop"></i></a>
                        <a href="#" class="btn btn-danger btn-xs resetVerbale" data-verbale_id="{$identifier}" title="Ricarica valore di default">RICALCOLA</a>
                        <textarea id="defaultVerbale-{$identifier}" style="display: none;">{$params.default_value}</textarea>
                        {/if}                     
                        {*<a href="#" data-verbale_id="{$identifier}" class="add-timeholder btn btn-danger btn-xs"><i class="fa fa-clock-o"></i></a>*}
                        <a href="#" data-verbale_id="{if is_numeric($identifier)}{$identifier}{else}all{/if}" class="save-verbale btn btn-xs btn-success">SALVA</a>                    
                    </div>                
                </div>
                {if $params.type|eq('string')}
                    <input id="verbaleField-{$identifier}" name="Verbale[{$identifier}]" class="form-control verbaleField{if $identifier|eq($currentPunto)} current {/if}" value="{$post.verbale[$identifier]}" />
                {else}
                    <textarea id="verbaleField-{$identifier}" name="Verbale[{$identifier}]" class="form-control verbaleField{if $identifier|eq($currentPunto)} current {/if}" rows="{$params.rows}">{$post.verbale[$identifier]}</textarea>
                {/if}                        
            </td>
        </tr>                
        {/foreach}
        </tbody>
    </table>
</form>