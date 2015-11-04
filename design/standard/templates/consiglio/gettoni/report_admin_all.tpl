<table class="table table-bordered">
    <tr>
        <th style="vertical-align: middle">Consiglieri</th>
        {foreach $sedute as $seduta}
            <th style="vertical-align: middle">{$seduta.data_ora|datetime('custom', '%j %M<br /><small>%H:%i</small>')}</th>
        {/foreach}
    </tr>
{foreach $politici as $politico}
    <tr>
        <td style="vertical-align: middle"><a href="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id)|ezurl(no)}">{$politico.object.name|wash()}</a></td>
        {foreach $sedute as $seduta}
            <td style="vertical-align: middle">{$politico.percentuale_presenza[$seduta.object.id]}</td>
        {/foreach}
    </tr>
{/foreach}
</table>