<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">
            <table class="table">
                <thead>
                <tr>
                    <th width="1"></th>
                    <th>Data di creazione</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $post.votazioni as $votazione}
                    <tr>
                        <td class="text-center;" style="vertical-align: middle">
                            <a href="{concat( 'editorialstuff/edit/votazione/', $votazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                        </td>
                        <td style="vertical-align: middle">{$votazione.object.published|l10n('shortdate')}</td>
                        <td>
                            {include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione.tpl' post=$votazione}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>