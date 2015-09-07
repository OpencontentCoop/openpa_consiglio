{if $post.current_state.identifier|eq('closed')}
    <div class="text-center" style="font-size:2em">
        {if $post.is_valid|not}
            <span class="label label-warning">QUORUM NON RAGGIUNTO</span>
        {elseif $post_result.approvata}
            <span class="label label-success">APPROVATA</span>
        {elseif $post_result.approvata|not}
            <span class="label label-danger">RESPINTA</span>
        {/if}
    </div>
{/if}

<table class="table">
    <tr>
        <th colspan="3"  class="text-center" style="vertical-align: middle">Quorum strutturale: {$post_result.quorum_strutturale}</th>
    </tr>
    <tr>
        <th style="vertical-align: middle">Presenti</th>
        <td style="vertical-align: middle" class="presenti">{$post_result.presenti_count}</td>
        <td class="presenti">
            <small>{foreach $post_result.presenti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
        </td>
    </tr>
    <tr>
        <th style="vertical-align: middle">Assenti</th>
        <td style="vertical-align: middle" align="center">{$post_result.assenti_count}</td>
        <td class="assenti">
            <small>{foreach $post_result.assenti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
        </td>
    </tr>

{if $post.current_state.identifier|eq('closed')}
        <tr>
            <th colspan="4" class="text-center" style="vertical-align: middle">Quorum funzionale: {$post_result.quorum_funzionale}</th>
        </tr>
        <tr>
            <th style="vertical-align: middle">Hanno espresso una preferenza</th>
            <td style="vertical-align: middle" align="center">{$post_result.votanti_count}</td>
            {*<td style="vertical-align: middle" class="votanti"><small>{foreach $post_result.votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small></td>*}
            <td>
                <table class="table table-bordered">
                    <tr>
                        <th style="vertical-align: middle">Favorevoli</th>
                        <td style="vertical-align: middle"
                            align="center">{$post_result.favorevoli_count}</td>
                        <td style="vertical-align: middle" class="favorevoli">
                            <small>{foreach $post_result.favorevoli as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle">Contrari</th>
                        <td style="vertical-align: middle"
                            align="center">{$post_result.contrari_count}</td>
                        <td style="vertical-align: middle" class="contrari">
                            <small>{foreach $post_result.contrari as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
                        </td>
                    </tr>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle">Astenuti</th>
                        <td style="vertical-align: middle"  align="center">{$post_result.astenuti_count}</td>
                        <td style="vertical-align: middle" class="astenuti">
                            <small>{foreach $post_result.astenuti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle" align="center">{$post_result.non_votanti_count}</td>
                        <td style="vertical-align: middle" class="votanti">
                            <b><small>Astenuti perché non hanno espresso una preferenza:</small></b>
                            <small>{foreach $post_result.non_votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
{/if}
</table>
