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
        <th colspan="3"  class="text-center" style="vertical-align: middle">Quorum costitutivo: {$post_result.quorum_strutturale}</th>
    </tr>
{if $post.current_state.identifier|ne('closed')}
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
{/if}
{if $post.current_state.identifier|eq('closed')}
    {def $anomalie = $post_result.anomalie}
    <tr>
        <th style="vertical-align: middle">Presenti</th>
        <td style="vertical-align: middle" class="presenti">{$post_result.presenti_count}</td>
        <td class="presenti">
            {foreach $post_result.presenti as $user}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/user_in_votazione.tpl' votazione=$post user=$user anomalie=$anomalie} {/foreach}
        </td>
    </tr>
    <tr>
        <th style="vertical-align: middle">Assenti</th>
        <td style="vertical-align: middle" align="center">{$post_result.assenti_count}</td>
        <td class="assenti">
            {foreach $post_result.assenti as $user}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/user_in_votazione.tpl' votazione=$post user=$user anomalie=$anomalie} {/foreach}
        </td>
    </tr>
    <tr>
        <th colspan="4" class="text-center" style="vertical-align: middle">Quorum deliberativo: {$post_result.quorum_funzionale}</th>
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
                        {foreach $post_result.favorevoli as $user}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/user_in_votazione.tpl' votazione=$post user=$user anomalie=$anomalie} {/foreach}
                    </td>
                </tr>
                <tr>
                    <th style="vertical-align: middle">Contrari</th>
                    <td style="vertical-align: middle"
                        align="center">{$post_result.contrari_count}</td>
                    <td style="vertical-align: middle" class="contrari">
                        {foreach $post_result.contrari as $user}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/user_in_votazione.tpl' votazione=$post user=$user anomalie=$anomalie} {/foreach}
                    </td>
                </tr>
                <tr>
                    <th {*rowspan="2"*} style="vertical-align: middle">Astenuti</th>
                    <td style="vertical-align: middle"  align="center">{$post_result.astenuti_count}</td>
                    <td style="vertical-align: middle" class="astenuti">
                        {foreach $post_result.astenuti as $user}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/user_in_votazione.tpl' votazione=$post user=$user anomalie=$anomalie} {/foreach}
                    </td>
                </tr>
               {* <tr>
                    <td style="vertical-align: middle" align="center">{$post_result.non_votanti_count}</td>
                    <td style="vertical-align: middle" class="votanti">
                        <b><small>Astenuti perché non hanno espresso una preferenza:</small></b>
                        <small>{foreach $post_result.non_votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</small>
                    </td>
                </tr>*}
            </table>
        </td>
    </tr>
	<tr>
	    <th style="vertical-align: middle">Non hanno espresso una preferenza</th>
            <td style="vertical-align: middle" align="center">{$post_result.non_votanti_count}</td>
	    <td style="vertical-align: middle" class="non-votanti">{foreach $post_result.non_votanti as $user}{include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione/user_in_votazione.tpl' votazione=$post user=$user anomalie=$anomalie} {/foreach}</td>
	</tr>
    {undef $anomalie}
{/if}
</table>
