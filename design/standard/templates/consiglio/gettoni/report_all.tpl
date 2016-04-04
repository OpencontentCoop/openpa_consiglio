<input type="button" class="btn btn-xs btn-info tableToExcel" value="Esporta in formato Excel">
<table id="export-{$interval}" class="table table-bordered responsive-table" data-min="10" data-max="30">
    <thead>
	<tr>	  
        <th style="vertical-align: middle">Consiglieri</th>
        {foreach $sedute as $seduta}
            <th style="vertical-align: middle; text-align: center;{if $seduta.competenza|eq('Giunta')}background:#eee;{/if}">
                <a href="{$seduta.editorial_url|ezurl(no)}">
                    {$seduta.competenza}<br />{$seduta.data_ora|datetime('custom', '%j %M <small>%H:%i</small>')}
                </a>
            </th>
        {/foreach}
        <th style="vertical-align: middle; text-align: center">Totale</th>
    </tr>
	</thead>
	<tbody>
    {foreach $politici as $politico}
        {def $is_assessore = $politico.is_in['giunta']}
        <tr>
            <td style="vertical-align: middle">
                <a href="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id)|ezurl(no)}">
                    {$politico.object.name|wash()}
                    {if $is_assessore}(assessore){/if}
                </a>
            </td>
            {def $somma = array() $progress = false() $presenze = array()}
            {foreach $sedute as $seduta}
			
                {set $presenze = count($politico.rilevazioni_presenze[$seduta.object.id])}
				{set $progress = $politico.percentuale_presenza[$seduta.object.id]}
                <td style="vertical-align: middle; text-align: center; {if and( $seduta.competenza|eq('Giunta'), $is_assessore|not() )}background:#ccc;{elseif $seduta.competenza|eq('Giunta')}background:#eee;{/if}">
					
					{if and( $seduta.competenza|eq('Giunta'), $is_assessore|not() )}{skip}{/if}
					
                    {if $presenze|gt(0)}
                        {def $importo = $politico.importo_gettone[$seduta.object.id]}
                        <div class="progress" style="margin-bottom: 0">
                            <div class="progress-bar progress-bar-{if $progress|ge(75)}success{elseif $progress|ge(25)}warning{else}danger{/if}"
                                 style="min-width: 4em;width:{$progress}%;">                                
								 <a style="color:#fff" href="#{$politico.object.id}" data-url="{concat('layout/set/modal/consiglio/presenze/',$seduta.object.id, '/',$politico.object.id)|ezurl(no)}" data-toggle="modal" data-target="#detailPresenze">
								  {$importo}<span class="no-export">€</span>
								</a>
                            </div>
                        </div>
                        {set $somma = $somma|append( $importo )}
                        {undef $importo}
                    {else}
					  <a href="#{$politico.object.id}" data-url="{concat('layout/set/modal/consiglio/presenze/',$seduta.object.id, '/',$politico.object.id)|ezurl(no)}" data-toggle="modal" data-target="#detailPresenze">
						<span class="no-export">?</span>
					  </a>
					{/if}
                </td>                
            {/foreach}
            <td style="vertical-align: middle; text-align: center">
                {$somma|array_sum()}<span class="no-export">€</span>
            </td>
            {undef $somma $progress $presenze}
        </tr>
        {undef $is_assessore}
    {/foreach}
	</tbody>
</table>
{ezscript_require( array( 'jquery.base64.js','tableExport.js', 'jquery-responsiveTables.js' ) )}

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

        $(document).on('click', '.tableToExcel', function(e){
            var table = $(e.currentTarget).next();
            table.tableExport({type:'excel',escape:'false'});
        });
    });
{/literal}
</script>

<div id="detailPresenze" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"><em>Caricamento...</em></div>
    </div>
</div>