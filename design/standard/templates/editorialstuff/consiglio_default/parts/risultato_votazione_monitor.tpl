{def $post_result = $post.result}

<h3 class="text_center">
    Esito della votazione {attribute_view_gui attribute=$post.object.data_map.short_text} <small>{attribute_view_gui attribute=$post.object.data_map.type}</small>
    <div class="text-center"style="font-size:1.5em">
    {if $post.is_valid|not}<span class="label label-warning">QUORUM NON RAGGIUNTO</span>
    {elseif $post_result.approvata}<span class="label label-success">APPROVATA</span>
    {elseif $post_result.approvata|not}<span class="label label-danger">RESPINTA</span>{/if}
    </div>
</h3>

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
        <th style="vertical-align: middle">Favorevoli</th>
        <td style="vertical-align: middle" align="center">{$post_result.favorevoli_count}</td>
        <td style="vertical-align: middle" class="favorevoli"><small>{foreach $post_result.favorevoli as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small></td>
    </tr>
    <tr>
        <th style="vertical-align: middle">Contrari</th>
        <td style="vertical-align: middle" align="center">{$post_result.contrari_count}</td>
        <td style="vertical-align: middle" class="contrari"><small>{foreach $post_result.contrari as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small></td>
    </tr>
    <tr>
        <th style="vertical-align: middle">Astenuti</th>
        <td style="vertical-align: middle" align="center">{$post_result.astenuti_count}</td>
        <td style="vertical-align: middle" class="astenuti"><small>{foreach $post_result.astenuti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small></td>
    </tr>
    <tr>
        <th style="vertical-align: middle">Non votanti</th>
        <td style="vertical-align: middle" align="center">{$post_result.non_votanti_count}</td>
        <td style="vertical-align: middle" class="astenuti"><small>{foreach $post_result.non_votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small></td>
    </tr>
</table>


{undef $post_result}