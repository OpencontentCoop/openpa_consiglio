<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">

            {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
            <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post">
                <input type="hidden" name="ActionIdentifier" value="ExportVotazioni"/>
                <p class="clearfix">
                    <button class="btn btn-info btn-xs pull-right" type="submit" name="ExportVotazioni"><i class="fa fa-download"></i> Esporta tutto in formato Excel</button>
                </p>
            </form>
            {/if}

            {foreach $post.votazioni as $votazione}

            {def $post_result = $votazione.result}
            {if $votazione.current_state.identifier|eq('closed')}
                {def $anomalie = $post_result.anomalie}
                <table class="table table-bordered" id="votazione-{$votazione.object.id}">
                  <thead>
                    <tr class="info">
                        <th style="white-space: nowrap">Chiusa il</th>
                        <th style="white-space: nowrap">Tipo</th>
                        <th style="white-space: nowrap">Testo</th>
                        <th style="white-space: nowrap">Esito</th>
                    </tr>
                  </thead>
                  <tbody>                    
                    <tr>
                        {*<td class="text-center;" style="vertical-align: middle">
                            <a href="{concat( 'editorialstuff/edit/votazione/', $votazione.object.id )|ezurl('no')}" title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                        </td>*}
                        <td>{$votazione.object.modified|l10n('shortdatetime')}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.type}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.short_text}</td>
                        <td>
                            {if $votazione.is_valid|not}<span class="label label-warning">QUORUM NON RAGGIUNTO</span>
                            {elseif $post_result.approvata}<span class="label label-success">APPROVATA</span>
                            {elseif $post_result.approvata|not}<span class="label label-danger">RESPINTA</span>{/if}
                        </td>
                    </tr>
                  </tbody>
                </table>
                <table class="table table-bordered">
                    <tr>
                        <td rowspan="2" style="vertical-align: middle;border-right: 0">Presenti</td>
                        <td rowspan="2" style="vertical-align: middle;border-left: 0">{$post_result.presenti_count}</td>
                        <td style="vertical-align: middle;border-right: 0">Votanti</td>
                        <td style="vertical-align: middle;border-left: 0">{$post_result.votanti_count}</td>
                        <td>
                            <table class="table table-condensed">
                                <tr>
                                    <th style="vertical-align: middle; border-top: none">Favorevoli</th>
                                    <td style="vertical-align: middle; border-top: none" align="center">{$post_result.favorevoli_count}</td>
                                    <td style="vertical-align: middle; border-top: none" class="favorevoli">
                                        {foreach $post_result.favorevoli as $user}{include uri='design:editorialstuff/seduta/parts/_user_in_votazione.tpl'}{delimiter}, {/delimiter}{/foreach}
                                    </td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle; border-top: none">Contrari</th>
                                    <td style="vertical-align: middle; border-top: none" align="center">{$post_result.contrari_count}</td>
                                    <td style="vertical-align: middle; border-top: none" class="contrari">
                                        {foreach $post_result.contrari as $user}{include uri='design:editorialstuff/seduta/parts/_user_in_votazione.tpl'}{delimiter}, {/delimiter}{/foreach}
                                    </td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle; border-top: none">Astenuti</th>
                                    <td style="vertical-align: middle; border-top: none" align="center">{$post_result.astenuti_count}</td>
                                    <td style="vertical-align: middle; border-top: none" class="astenuti">
                                        {foreach $post_result.astenuti as $user}{include uri='design:editorialstuff/seduta/parts/_user_in_votazione.tpl'}{delimiter}, {/delimiter}{/foreach}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;border-right: 0">Non votanti</td>
                        <td style="vertical-align: middle;border-left: 0">{$post_result.non_votanti_count}</td>
                        <td>
                            <table class="table table-condensed">
                                <tr>
                                    <td style="vertical-align: middle;border-top: none">
                                        {foreach $post_result.non_votanti as $user}{include uri='design:editorialstuff/seduta/parts/_user_in_votazione.tpl'}{delimiter}, {/delimiter}{/foreach}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
					<tr>
                        <td style="vertical-align: middle;border-right: 0">Assenti</td>
                        <td style="vertical-align: middle;border-left: 0">{$post_result.assenti_count}</td>
                        <td colspan="3">
                            <table class="table table-condensed">
                                <tr>
                                    <td style="vertical-align: middle;border-top: none">
                                        {foreach $post_result.assenti as $user}{include uri='design:editorialstuff/seduta/parts/_user_in_votazione.tpl'}{delimiter}, {/delimiter}{/foreach}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                {undef $anomalie}
                {else}
                <table class="table table-bordered" id="votazione-{$votazione.object.id}">
                  <thead>
                    <tr class="info">
                        <th style="white-space: nowrap">Creata il</th>
                        <th style="white-space: nowrap">Tipo</th>
                        <th style="white-space: nowrap">Testo</th>
                        <th style="white-space: nowrap">Esito</th>
                    </tr>
                  </thead>
                  <tbody> 
                    <tr>
                        <td>{$votazione.object.published|l10n('shortdatetime')}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.type}</td>
                        <td>{attribute_view_gui attribute=$votazione.object.data_map.short_text}</td>
                        <td><span class="label label-info">NON EFFETTUATA</span></td>
                    </tr>
                  </tbody>
                </table>
              {/if}
              {undef $post_result}
            {delimiter}<br />{/delimiter}
            {/foreach}
        </div>
    </div>
</div>

<div id="detailPresenzeInVotazione" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"><em>Caricamento...</em></div>
    </div>
</div>

{ezscript_require( array( 'ezjsc::jquery' ) )}
<script type="application/javascript">
{literal}
$(document).ready(function(){
    $('#detailPresenzeInVotazione').on('show.bs.modal', function (event) {
        var url = $(event.relatedTarget).data('url');
        $(this).find('.modal-content').load(url);
    }).on('hide.bs.modal', function (event) {
        $(this).find('.modal-content').html('<em>Caricamento...</em>');
    });
});
{/literal}
</script>