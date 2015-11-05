{def $politico_object = fetch( 'content', 'object', hash( 'object_id', $politico ) )
     $seduta_object = fetch( 'content', 'object', hash( 'object_id', $seduta ) )}

{def $seduta_can_modify = cond( object_handler($seduta_object).gestione_sedute_consiglio.stuff.liquidata|eq(0), true(), false() )}
{def $spese = fetch( ezfind, search, hash( class_id, array('rendiconto_spese'), filter, array( concat('meta_owner_id_si:', $politico ), concat('submeta_relations___id_si:', $seduta )  ) ))}
{def $sum = array()}
<table class="table table-bordered table-condensed">
{foreach $spese.SearchResult as $spesa}
    <tr>
        <td>
            <a href={concat("content/download/",$spesa.data_map.file.contentobject_id,"/",$spesa.data_map.file.id,"/file/",$spesa.data_map.file.content.original_filename)|ezurl}>
                {$spesa.name|wash()}
            </a>
        </td>
        <td>{attribute_view_gui attribute=$spesa.data_map.amount}€</td>
        {set $sum = $sum|append($spesa.data_map.amount.data_float)}
        {if $seduta_can_modify}
        <td>
            <a href="#" class="btn btn-danger btn-xs remove-spesa" data-url="{concat('consiglio/gettoni/',$interval,'/',$politico_object.id, '/remove_spesa/', $spesa.object.id )|ezurl(no)}">
                <i class="fa fa-trash"></i>
            </a>
        </td>
        {/if}
    </tr>
{/foreach}
    {if $spese.SearchCount|gt(0)}
    <tr>
        <th>Totale</th>
        <td colspan="2">{$sum|array_sum()}€</td>
    </tr>
    {/if}
</table>
{undef $sum}
{undef $seduta_can_modify}