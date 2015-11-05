{def $id = 1}
{def $politico_object = fetch( 'content', 'object', hash( 'object_id', $politico ) )
     $seduta_object = fetch( 'content', 'object', hash( 'object_id', $seduta ) )}

<table class="table table-bordered table-condensed">
    <tr>
        <td>{$politico}</td>
        <td>{$seduta}€</td>
        <td><a href="#" class="btn btn-danger btn-xs remove-spesa"
               data-url="{concat('consiglio/gettoni/',$interval,'/',$politico_object.id, '/remove_spesa/', $seduta, '-', $id )|ezurl(no)}"><i class="fa fa-trash"></i> </a> </td>
    </tr>
    <tr>
        <th>Totale</th>
        <td colspan="2">40€</td>
    </tr>
</table>
