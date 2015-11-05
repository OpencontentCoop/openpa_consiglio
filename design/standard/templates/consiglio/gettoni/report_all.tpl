<table class="table table-bordered">
    <tr>
        <th style="vertical-align: middle">Consiglieri</th>
        {foreach $sedute as $seduta}
            <th style="vertical-align: middle; text-align: center">
                {$seduta.competenza} - {$seduta.data_ora|datetime('custom', '%j %M <small>%H:%i</small>')}
            </th>
        {/foreach}
        <th style="vertical-align: middle; text-align: center">Totale</th>
    </tr>
    {foreach $politici as $politico}
        <tr>
            <td style="vertical-align: middle">
                <a href="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id)|ezurl(no)}">
                    {$politico.object.name|wash()}
                    {if $politico.is_in['giunta']}(assessore){/if}
                </a>
            </td>
            {def $somma = array()}
            {foreach $sedute as $seduta}
                <td style="vertical-align: middle; text-align: center">
                    {def $progress = $politico.percentuale_presenza[$seduta.object.id]}
                    {def $importo = $politico.importo_gettone[$seduta.object.id]}
                    <div class="progress" style="margin-bottom: 0">
                        <div class="progress-bar progress-bar-{if $progress|gt(75)}success{elseif $progress|gt(25)}warning{else}danger{/if}"
                             style="min-width: 4em;width:{$progress}%;">
                            {$progress}%
                        </div>
                    </div>
                    {set $somma = $somma|append( $importo )}
                    {undef $progress}
                    {undef $importo}
                </td>
            {/foreach}
            <td style="vertical-align: middle; text-align: center">
                {$somma|array_sum()}€
            </td>
            {undef $somma}
        </tr>
    {/foreach}
</table>