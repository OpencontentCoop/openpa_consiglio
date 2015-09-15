<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">
            <table class="table">
                <tbody>
                {foreach $post.votazioni as $votazione}
                    <tr>
                        {*<td class="text-center;" style="vertical-align: middle">
                            <a href="{concat( 'editorialstuff/edit/votazione/', $votazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                        </td>*}
                        <td>{$votazione.object.published|l10n('shortdatetime')}</td>
                        <td>
                            {include uri='design:editorialstuff/consiglio_default/parts/risultato_votazione_monitor.tpl' post=$votazione}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>