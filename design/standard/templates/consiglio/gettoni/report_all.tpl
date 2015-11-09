<input type="button" class="btn btn-xs btn-info" onclick="tableToExcel('export-{$interval}', 'Presenze')" value="Esporta in formato Excel">
<table id="export-{$interval}" class="table table-bordered responsive-table" data-min="10" data-max="30">
    <tr>
        <th style="vertical-align: middle">Consiglieri</th>
        {foreach $sedute as $seduta}
            <th style="vertical-align: middle; text-align: center">
                <a href="{$seduta.editorial_url|ezurl(no)}">
                    {$seduta.competenza}<br />{$seduta.data_ora|datetime('custom', '%j %M <small>%H:%i</small>')}
                </a>
            </th>
        {/foreach}
        <th style="vertical-align: middle; text-align: center">Totale</th>
    </tr>
    {foreach $politici as $politico}
        {def $is_assessore = $politico.is_in['giunta']}
        <tr>
            <td style="vertical-align: middle">
                <a href="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id)|ezurl(no)}">
                    {$politico.object.name|wash()}
                    {if $is_assessore}(assessore){/if}
                </a>
            </td>
            {def $somma = array()}
            {foreach $sedute as $seduta}
                {def $progress = $politico.percentuale_presenza[$seduta.object.id]}
                <td style="vertical-align: middle; text-align: center"{if and( $seduta.competenza|eq('Giunta'), $is_assessore|not() )}class="active"{/if}>
                    {if and( $seduta.competenza|eq('Giunta'), $is_assessore|not() )}{skip}{/if}
                    {if and( $progress, $progress|gt(0) )}
                        {def $importo = $politico.importo_gettone[$seduta.object.id]}
                        <div class="progress" style="margin-bottom: 0">
                            <div class="progress-bar progress-bar-{if $progress|gt(75)}success{elseif $progress|gt(25)}warning{else}danger{/if}"
                                 style="min-width: 4em;width:{$progress}%;">                                
								 <a style="color:#fff" href="#{$politico.object.id}" data-url="{concat('layout/set/modal/consiglio/presenze/',$seduta.object.id, '/',$politico.object.id)|ezurl(no)}" data-toggle="modal" data-target="#detailPresenze">
								  {$importo}€
								</a>
                            </div>
                        </div>
                        {set $somma = $somma|append( $importo )}
                        {undef $importo}
                    {/if}
                </td>
                {undef $progress}
            {/foreach}
            <td style="vertical-align: middle; text-align: center">
                {$somma|array_sum()}€
            </td>
            {undef $somma}
        </tr>
        {undef $is_assessore}
    {/foreach}
</table>
{ezscript_require( array( 'table2excel.js', 'jquery-responsiveTables.js' ) )}

<script type="text/javascript">
{literal}
    $(document).ready(function() {
        var table = $('table.responsive-table');
		if (table.find('th').length > 8) table.responsiveTables();
		$('#detailPresenze').on('show.bs.modal', function (event) {
            var url = $(event.relatedTarget).data('url');
            $(this).find('.modal-content').load(url);
        }).on('hide.bs.modal', function (event) {
            $(this).find('.modal-content').html('<em>Caricamento...</em>');
        });
    });
{/literal}
</script>

<div id="detailPresenze" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"><em>Caricamento...</em></div>
    </div>
</div>