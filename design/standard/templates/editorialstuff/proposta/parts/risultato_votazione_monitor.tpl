{def $post_result = $post.result}
{def $anomalie = $post_result.anomalie}
<h2 class="text_center">
    Esito della votazione {attribute_view_gui attribute=$post.object.data_map.short_text} <small>{attribute_view_gui attribute=$post.object.data_map.type}</small>
    <div class="text-center"style="font-size:1.5em">
    {if $post.is_valid|not}<span class="label label-warning">QUORUM NON RAGGIUNTO</span>
    {elseif $post_result.approvata}<span class="label label-success">APPROVATA</span>
    {elseif $post_result.approvata|not}<span class="label label-danger">RESPINTA</span>{/if}
    </div>
</h2>

<hr />

<table class="table" style="font-size:1.2em">
    <tr>
        <td style="vertical-align: middle">Presenti</td>
        <td style="vertical-align: middle" class="presenti">{$post_result.presenti_count}</td>
        <td style="vertical-align: middle">Assenti</td>
        <td style="vertical-align: middle" class="presenti">{$post_result.assenti_count}</td>
    </tr>
    <tr>
        <td style="vertical-align: middle">Votanti</td>
        <td style="vertical-align: middle" class="presenti">{$post_result.votanti_count}</td>
        <td style="vertical-align: middle">Non votanti</td>
        <td style="vertical-align: middle" class="presenti">{$post_result.non_votanti_count}</td>
    </tr>
</table>

<hr />

<table class="table table-bordered">
    <tr>
        <th style="vertical-align: middle">Hanno espresso una preferenza</th>
        <td align="center" style="vertical-align: middle">{$post_result.votanti_count}</td>
        <td>
            <table class="table table-bordered">
                <tr>
                    <th style="vertical-align: middle">Favorevoli</th>
                    <td style="vertical-align: middle" align="center">{$post_result.favorevoli_count}</td>
                    <td style="vertical-align: middle" class="favorevoli">
                        {foreach $post_result.favorevoli as $partecipante}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/partecipante_in_votazione.tpl' is_monitor=true() votazione=$post partecipante=$partecipante anomalie=$anomalie}{delimiter} {/delimiter}{/foreach}
                    </td>
                </tr>
                <tr>
                    <th style="vertical-align: middle">Contrari</th>
                    <td style="vertical-align: middle" align="center">{$post_result.contrari_count}</td>
                    <td style="vertical-align: middle" class="contrari">
                        {foreach $post_result.contrari as $partecipante}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/partecipante_in_votazione.tpl' is_monitor=true() votazione=$post partecipante=$partecipante anomalie=$anomalie}{delimiter} {/delimiter}{/foreach}
                    </td>
                </tr>
                <tr>
                    <th style="vertical-align: middle">Astenuti</th>
                    <td style="vertical-align: middle" align="center">{$post_result.astenuti_count}</td>
                    <td style="vertical-align: middle" class="astenuti">
                        {foreach $post_result.astenuti as $partecipante}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/partecipante_in_votazione.tpl' is_monitor=true() votazione=$post partecipante=$partecipante anomalie=$anomalie}{delimiter} {/delimiter}{/foreach}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <th style="vertical-align: middle">Non hanno espresso una preferenza</th>
        <td align="center" style="vertical-align: middle">{$post_result.non_votanti_count}</td>
        <td style="vertical-align: middle" class="astenuti">
            {foreach $post_result.non_votanti as $partecipante}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/partecipante_in_votazione.tpl' is_monitor=true() votazione=$post partecipante=$partecipante anomalie=$anomalie}{delimiter} {/delimiter}{/foreach}
        </td>
    </tr>
</table>

{undef $post_result}
{undef $anomalie}
