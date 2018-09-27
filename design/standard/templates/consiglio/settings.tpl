<h1 class="context-title">Configurazioni OpenConsiglio</h1>

<form name="consiglio_settings" method="post" class="form" action={"consiglio/settings/"|ezurl}>

    {foreach $settings as $identifier => $data}
        {if $data.type|eq('text')}
        <div class="form-group">
            <label for="setting_{$identifier}">{$data.name|wash()}</label>
            <p class="help-block">{$data.help_text|wash()}</p>
            <input id="setting_{$identifier}" class="form-control" type="text" name="GlobalSettings_{$identifier}" value="{$data.value.value|wash}"/>
        </div>
        {/if}

        {if $data.type|eq('checkbox')}
            <strong>{$data.name|wash()}</strong>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="GlobalSettings_{$identifier}" {if $data.value.value|eq(1)}checked="checked"{/if}> {$data.help_text|wash()}
                </label>
            </div>
        {/if}

        {if $data.type|eq('checkbox-list')}
            <strong>{$data.name|wash()}</strong>
            {foreach $data.list as $item}
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="GlobalSettings_{$identifier}[]" value="{$item}" {if $data.value.value|explode('-')|contains($item)}checked="checked"{/if}> {$item|wash()}
                </label>
            </div>
            {/foreach}
        {/if}
    {/foreach}

    <div class="block">
        <input class="defaultbutton pull-right" type="submit" name="StoreGlobalSettings" value="Salva"/>
    </div>

</form>

