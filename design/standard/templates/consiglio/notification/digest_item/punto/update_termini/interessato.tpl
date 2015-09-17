{set-block scope=root variable=subject}Modifica del termine delle osservazioni{/set-block}
{if is_set( $diff.termine_osservazioni )}
    Il nuovo termine delle osservazioni è {attribute_view_gui attribute=$diff.termine_osservazioni.new}.
{/if}