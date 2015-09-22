<div id="fix">
    <!-- Maincontent START -->

    <div class="box-header">
        <div class="box-ml">

            <h1 class="context-title">Configurazioni ComunWeb Consiglio</h1>

            <div class="header-mainline"></div>

        </div>
    </div>

    <div class="box-bc">
        <div class="box-ml">
            <div class="box-content">

                <div class="context-attributes">

                    <form name="consiglio_settings" method="post" action={"consiglio/settings/"|ezurl}>

                            {foreach $settings as $identifier => $data}
                                <p>
                                    <label for="setting_{$identifier}">{$data.name|wash()}</label>
                                    <small>{$data.help_text|wash()}</small>
                                    <input id="setting_{$identifier}" class="box" type="text" name="GlobalSettings_{$identifier}" value="{$data.value.value|wash}" />
                                </p>
                            {/foreach}

                    <div class="block">
                        <input class="defaultbutton" type="submit" name="StoreGlobalSettings" value="Salva"/>
                    </div>

                    </form>

                </div>

            </div>
        </div>
    </div>


    <!-- Maincontent END -->
</div>