<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">
            <table class="table">
                <thead>
                <tr>
                    <th width="1"></th>
                    <th>Data di creazione</th>
                    <th>Titolo breve</th>
                    <th>Testo</th>
                    <th>Risultati</th>
                </tr>
                </thead>
                <tbody>
                {foreach $post.votazioni as $votazione}
                    <tr>
                        <td class="text-center">
                            <a href="{concat( 'editorialstuff/edit/votazioni/', $votazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                        </td>
                        <td>{$votazione.object.published|l10n('shortdate')}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.short_text}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.text}</td>
                        <td>
                            {if $post.current_state.identifier|eq('closed')}
                                <table class="list">
                                    <tr>
                                        <th>Presenti</th>
                                        <td class="presenti">{attribute_view_gui attribute=$votazione.object.data_map.presenti}</td>
                                        <td class="presenti">{foreach $votazione.presenti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
                                    </tr>
                                    <tr>
                                        <th>Votanti</th>
                                        <td class="votanti">{attribute_view_gui attribute=$votazione.object.data_map.votanti}</td>
                                        <td class="votanti">{foreach $votazione.votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
                                    </tr>
                                    <tr>
                                        <th>Favorevoli</th>
                                        <td class="favorevoli">{attribute_view_gui attribute=$votazione.object.data_map.favorevoli}</td>
                                        <td class="favorevoli">{foreach $votazione.favorevoli as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
                                    </tr>
                                    <tr>
                                        <th>Contrari</th>
                                        <td class="contrari">{attribute_view_gui attribute=$votazione.object.data_map.contrari}</td>
                                        <td class="contrari">{foreach $votazione.contrari as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}){/foreach}</td>
                                    </tr>
                                    <tr>
                                        <th>Astenuti</th>
                                        <td class="astenuti">{attribute_view_gui attribute=$votazione.object.data_map.astenuti}</td>
                                        <td class="astenuti">{foreach $votazione.astenuti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
                                    </tr>
                                </table>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>