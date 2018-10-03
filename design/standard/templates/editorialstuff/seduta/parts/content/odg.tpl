{def $odg = $post.odg 
     $is_editor=0}
{if and($post.object.can_edit, $post.can_modify_odg)}
	{set $is_editor=1}
{/if}

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
        <th>Osservazioni</th>	
        <th>Stato</th>
        <th>Autore</th>
    {if $is_editor}    
        <th></th>
	{/if}
        {*<th width="1"></th>*}
    </tr>
    </thead>
    <tbody>
    {if count($odg)|gt(0)}
        {foreach $odg as $punto}
            {if and( $punto.seduta_id|eq($post.object_id), $punto.object.can_read )}
                <tr>
                    <td class="text-center">
                        <a href="{concat( 'editorialstuff/edit/punto/', $punto.object.id )|ezurl('no')}"
                           title="Dettaglio" class="btn btn-info btn-xs">Dettaglio</a>
                    </td>
                    <td class='priority' {if $is_editor} data-name="n_punto"
                        data-pk="{$punto.object.data_map.n_punto.id}"
                        data-url="{concat('/edit/attribute/',$punto.object.id,'/n_punto')|ezurl(no)}" {/if}>
                        {$punto.object.data_map.n_punto.data_int|wash()}
                    </td>
                    <td>
                        {if $is_editor}<a href="#" class="editable"
                                          data-type="text"
                                          data-name="orario_trattazione"
                                          data-pk="{$punto.object.data_map.orario_trattazione.id}"
                                          data-url="{concat('/edit/attribute/',$punto.object.id,'/orario_trattazione/1')|ezurl(no)}"
                                          data-title="Imposta orario">{/if}
                            {attribute_view_gui attribute=$punto.object.data_map.orario_trattazione}
                        {if $is_editor}</a>{/if}
                    </td>
                    <td>
                        {if $is_editor}<a href="#" class="editable"
                                          data-name="oggetto"
                                          data-pk="{$punto.object.data_map.oggetto.id}"
                                          data-url="{concat('/edit/attribute/',$punto.object.id,'/oggetto/1')|ezurl(no)}"
                                          data-type="textarea"
                                          data-title="Imposta oggetto" style="white-space: normal">{/if}
                            {attribute_view_gui attribute=$punto.object.data_map.oggetto}
                        {if $is_editor}</a>{/if}
                        {if $punto.object.data_map.alert.has_content}
                            <div class="alert alert-warning">
                                {attribute_view_gui attribute=$punto.object.data_map.alert}
                            </div>
                        {/if}
                        {if and($is_editor, $punto.object.data_map.note.has_content)}
                        	<div>
                        		<small>
                        			<em>
                        				{attribute_view_gui attribute=$punto.object.data_map.note}
                    				</em>
	                        	</small>
	                        </div>
                        {/if}
                        {if $punto.proposte|count()|gt(0)}
                            <br /><small>Su proposta di: {foreach $punto.proposte as $proposta}<a href="{$proposta.editorial_url|ezurl(no)}">{$proposta.object.owner.name|wash()}</a>{/foreach}</small>
                        {/if}
                    </td>
                    <td>{attribute_view_gui attribute=$punto.object.data_map.materia}</td>
                    <td><a href="{concat('editorialstuff/edit/punto/',$punto.object.id,'/#tab_documenti')|ezurl(no)}">{$punto.count_documenti}</a></td>
                    <td><a href="{concat('editorialstuff/edit/punto/',$punto.object.id,'/#tab_inviti')|ezurl(no)}">{$punto.count_invitati}</a></td>
                    <td><a href="{concat('editorialstuff/edit/punto/',$punto.object.id,'/#tab_osservazioni')|ezurl(no)}">{$punto.count_osservazioni}</a></td>	                
                    <td>{include uri='design:editorialstuff/punto/parts/edit_state.tpl' post=$punto}</td>
                    <td>{$punto.object.owner.name|wash()}</td>
                    {if $is_editor}
                    <td>
                        <a href="{concat('consiglio/move/punto/',$punto.object.id)|ezurl(no)}" class="btn btn-warning btn-xs">Sposta</a>
                    </td>
	                {/if}
                    {*<td><i class="fa fa-reorder handle"></i> </td>*}
                </tr>
            {/if}
        {/foreach}
    {/if}
    </tbody>
</table>