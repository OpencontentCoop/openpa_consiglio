<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">
            {foreach $post.votazioni as $votazione}
            <table class="table table-bordered">
                <thead>
                    <tr class="info">
                        <th style="white-space: nowrap">Creata il</th>
                        <th style="white-space: nowrap">Tipo</th>
                        <th style="white-space: nowrap">Testo</th>
                        <th style="white-space: nowrap">Esito</th>
                        <th style="white-space: nowrap">Presenti</th>
                        <th style="white-space: nowrap">Assenti</th>
                        <th style="white-space: nowrap">Votanti</th>
                        <th style="white-space: nowrap">Non votanti</th>
                    </tr>
                </thead>
                <tbody>
                    {def $post_result = $votazione.result}
                    {if $votazione.current_state.identifier|eq('closed')}
                    <tr>
                        {*<td class="text-center;" style="vertical-align: middle">
                            <a href="{concat( 'editorialstuff/edit/votazione/', $votazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                        </td>*}
                        <td>{$votazione.object.published|l10n('shortdatetime')}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.type}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.short_text}</td>
                        <td>
                            {if $votazione.is_valid|not}<span class="label label-warning">QUORUM NON RAGGIUNTO</span>
                            {elseif $post_result.approvata}<span class="label label-success">APPROVATA</span>
                            {elseif $post_result.approvata|not}<span class="label label-danger">RESPINTA</span>{/if}
                        </td>
                        <td>{$post_result.presenti_count}</td>
                        <td>{$post_result.assenti_count}</td>
                        <td>
                            {$post_result.votanti_count}
                        </td>
                        <td>
                            {$post_result.non_votanti_count}
                        </td>
                    </tr>
                    <tr>
                        <td>Hanno espresso una preferenza</td>
                        <td colspan="7">
                            <table class="table table-condensed">
                                <tr>
                                    <th style="vertical-align: middle; border-top: none">Favorevoli</th>
                                    <td style="vertical-align: middle; border-top: none" align="center">{$post_result.favorevoli_count}</td>
                                    <td style="vertical-align: middle; border-top: none" class="favorevoli">
                                        <small>
                                            {foreach $post_result.favorevoli as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle; border-top: none">Contrari</th>
                                    <td style="vertical-align: middle; border-top: none" align="center">{$post_result.contrari_count}</td>
                                    <td style="vertical-align: middle; border-top: none" class="contrari">
                                        <small>
                                            {foreach $post_result.contrari as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle; border-top: none">Astenuti</th>
                                    <td style="vertical-align: middle; border-top: none" align="center">{$post_result.astenuti_count}</td>
                                    <td style="vertical-align: middle; border-top: none" class="astenuti">
                                        <small>
                                            {foreach $post_result.astenuti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>Non hanno espresso una preferenza</td>
                        <td colspan="7">
                            <table class="table table-condensed">
                                <tr>
                                    <td style="vertical-align: middle;border-top: none">
                                        <small>
                                            {foreach $post_result.non_votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    {else}
                    <tr>
                        <td>{$votazione.object.published|l10n('shortdatetime')}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.type}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.short_text}</td>
                        <td><span class="label label-info">NON EFFETTUATA</span></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    {/if}
                    {undef $post_result}
                </tbody>
            </table>
            {delimiter}<br />{/delimiter}
            {/foreach}
        </div>
    </div>
</div>