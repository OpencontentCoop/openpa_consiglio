{def $seduta_can_modify = $seduta.liquidata|not()}
{def $spese = fetch( ezfind, search, hash( class_id, array('rendiconto_spese'), filter, array( concat('meta_owner_id_si:', $politico.object.id ), concat('rendiconto_spese/relations/id:', $seduta.object.id )  ) ))}

{def $sum = array()}
<div class="no-export">
<table class="table table-bordered table-condensed">
{foreach $spese.SearchResult as $spesa}
    <tr>
        <td>
            <a href={concat("content/download/",$spesa.data_map.file.contentobject_id,"/",$spesa.data_map.file.id,"/file/",$spesa.data_map.file.content.original_filename)|ezurl}>
                {$spesa.name|wash()}
            </a>
        </td>
        <td>{attribute_view_gui attribute=$spesa.data_map.amount}<span class="no-export">€</span></td>
        {set $sum = $sum|append($spesa.data_map.amount.data_float)}
        {if $seduta_can_modify}
        <td>
            <a href="#" class="btn btn-danger btn-xs remove-spesa" data-url="{concat('consiglio/gettoni/',$interval,'/',$politico.object.id, '/remove_spesa/', $spesa.object.id )|ezurl(no)}">
                <i class="fa fa-times"></i>
            </a>
        </td>
        {/if}
    </tr>
{/foreach}
    {if $spese.SearchCount|gt(0)}
    <tr>
        <th>Totale</th>
        <td colspan="2">{$sum|array_sum()}<span class="no-export">€</span></td>
    </tr>
    {/if}
</table>
</div>
{if $spese.SearchCount|gt(0)}<span style="visibility: hidden">{$sum|array_sum()}</span>{/if}
{undef $sum}
{undef $seduta_can_modify $spese}
