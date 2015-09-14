{set-block scope=root variable=subject}Pubblicazione del punto{/set-block}
{def $published_time = false()}
{foreach $punto.history as $time => $history_items}
    {if $published_time|not()}
        {foreach $history_items as $item}
            {if $published_time|not()}
                {if $item.action|eq('updateobjectstate')}
                    {if and( is_set($item.parameters.after_state_name), $item.parameters.after_state_name|eq('Pubblicato') )}
                        {set $published_time = $time|datetime( 'custom', '%l %j %F %Y alle ore %H:%i' )|downcase()}
                    {/if}
                {/if}
            {/if}
        {/foreach}
    {/if}
{/foreach}
{if $published_time}
    Il punto è stato pubblicato {$published_time}
{/if}