{def $odg = $post.odg}
<table class="table sorted_table" id="odg"
       data-url="{concat('/consiglio/data/seduta/',$post.object_id,'/parts:content:odg')|ezurl(no)}">
    <thead>
    <tr>
        <th width="1"></th>
        <th>Num</th>
        <th>Ora</th>
        <th>Oggetto</th>
        <th>Materia</th>
        <th>Documenti</th>
        <th>Invitati</th>
        <th>Oss</th>
        <th>Stato</th>
        {*<th width="1"></th>*}
    </tr>
    </thead>
    <tbody>
    {if count($odg)|gt(0)}
        {foreach $odg as $punto}
            {if $punto.seduta_id|eq($post.object_id)}
                <tr>
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/punto/', $punto.object.id )|ezurl('no')}"
                           title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td class='priority' data-name="n_punto"
                        data-pk="{$punto.object.data_map.n_punto.id}"
                        data-url="{concat('/edit/attribute/',$punto.object.id,'/n_punto')|ezurl(no)}">
                        {$punto.object.data_map.n_punto.data_int|wash()}
                    </td>
                    <td>
                        <a href="#" class="editable" data-type="text" data-name="orario_trattazione"
                           data-pk="{$punto.object.data_map.orario_trattazione.id}"
                           data-url="{concat('/edit/attribute/',$punto.object.id,'/orario_trattazione/1')|ezurl(no)}"
                           data-title="Imposta orario">
                            {attribute_view_gui attribute=$punto.object.data_map.orario_trattazione}
                        </a>
                    </td>
                    <td>
                        <a href="#" class="editable" data-type="text" data-name="oggetto"
                           data-pk="{$punto.object.data_map.oggetto.id}"
                           data-url="{concat('/edit/attribute/',$punto.object.id,'/oggetto/1')|ezurl(no)}"
                           data-title="Imposta oggetto">
                            {attribute_view_gui attribute=$punto.object.data_map.oggetto}
                        </a>
                    </td>
                    <td>{attribute_view_gui attribute=$punto.object.data_map.materia}</td>
                    <td>{$punto.count_documenti}</td>
                    <td>{$punto.count_invitati}</td>
                    <td>{$punto.count_osservazioni}</td>
                    <td>{include uri='design:editorialstuff/punto/parts/state.tpl' post=$punto}</td>
                    {*<td><i class="fa fa-reorder handle"></i> </td>*}
                </tr>
            {/if}
        {/foreach}
    {/if}
    </tbody>
</table>
