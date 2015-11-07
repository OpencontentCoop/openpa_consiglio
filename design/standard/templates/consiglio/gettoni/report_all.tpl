<table class="table table-bordered">
    <tr>
        <th style="vertical-align: middle">Consiglieri</th>
        {foreach $sedute as $seduta}
            <th style="vertical-align: middle; text-align: center">
                <a href="{$seduta.editorial_url|ezurl(no)}">
                    {$seduta.competenza}<br />{$seduta.data_ora|datetime('custom', '%j %M <small>%H:%i</small>')}
                </a>
            </th>
        {/foreach}
        <th style="vertical-align: middle; text-align: center">Totale</th>
    </tr>
    {foreach $politici as $politico}
        {def $is_assessore = $politico.is_in['giunta']}
        <tr>
            <td style="vertical-align: middle">
                <a href="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id)|ezurl(no)}">
                    {$politico.object.name|wash()}
                    {if $is_assessore}(assessore){/if}
                </a>
            </td>
            {def $somma = array()}
            {foreach $sedute as $seduta}
                <td style="vertical-align: middle; text-align: center">
                    {if and( $seduta.competenza|eq('Giunta'), $is_assessore|not() )}{skip}{/if}
                    {def $progress = $politico.percentuale_presenza[$seduta.object.id]}
                    {if $progress}
                        {def $importo = $politico.importo_gettone[$seduta.object.id]}
                        <div class="progress" style="margin-bottom: 0">
                            <div class="progress-bar progress-bar-{if $progress|gt(75)}success{elseif $progress|gt(25)}warning{else}danger{/if}"
                                 style="min-width: 4em;width:{$progress}%;">
                                <a style="color:#fff" href="{concat('consiglio/presenze/',$seduta.object.id, '/',$politico.object.id)|ezurl(no)}">{$progress}%</a>
                            </div>
                        </div>
                        {set $somma = $somma|append( $importo )}
                        {undef $importo}
                    {/if}
                    {undef $progress}
                </td>
            {/foreach}
            <td style="vertical-align: middle; text-align: center">
                {$somma|array_sum()}€
            </td>
            {undef $somma}
        </tr>
        {undef $is_assessore}
    {/foreach}
</table>