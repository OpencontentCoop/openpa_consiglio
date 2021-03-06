<div class="row">
    <div class="col-sm-2 col-lg-1 events text-center">
        <div class="calendar-date" style="min-width: 50px">
            <span class="month">{$post.data_ora|datetime( 'custom', '%M' )}</span>
            <span class="day">{$post.data_ora|datetime( 'custom', '%j' )}</span>
            <strong>ore {attribute_view_gui attribute=$post.object.data_map.orario}</strong>
        </div>
    </div>
    <div class="col-sm-10 col-lg-11">
        <h3>
            Seduta di {attribute_view_gui attribute=$post.object.data_map.organo show_link=false()}
            <span>{include uri='design:editorialstuff/seduta/parts/state.tpl' post=$post}</span>
            <a class="btn btn-primary btn-xs" role="button" data-toggle="collapse" href="#detail-{$post.object_id}" aria-expanded="false" aria-controls="collapseExample">Ordine del giorno</a>
            <a class="btn btn-primary btn-xs" href="{concat('editorialstuff/edit/seduta/', $post.object_id)|ezurl(no)}">Vai al dettaglio</a>
        </h3>
        {if array('sent')|contains($post.current_state.identifier)}
            <strong>Convocazione:</strong>
            {attribute_view_gui attribute=$post.object.data_map.convocazione}
        {/if}
    </div>
</div>
<div class="row collapse" id="detail-{$post.object_id}">
    <div class="col-sm-12 col-lg-12">

        <div class="table-responsive">
            <table class="table table-striped">
                {foreach $post.odg as $punto}
                    {if and($punto.object.can_read, array('published','in_progress','closed')|contains($punto.current_state.identifier))}
                        <tr>
                            <td>
                                {attribute_view_gui attribute=$punto.object.data_map.n_punto}
                            </td>
                            <td>
                                <strong>{attribute_view_gui attribute=$punto.object.data_map.orario_trattazione}</strong>
                            </td>
                            <td>
                                {if $punto.object|has_attribute('termine_osservazioni')}
                                <small>
                                    <strong>Termine osservazioni:</strong><br />
                                    {attribute_view_gui attribute=$punto.object.data_map.termine_osservazioni}
                                </small>
                                {/if}
                                {if $punto.can_add_osservazioni}
                                    <a title="Aggiungi osservazione" data-toggle="tooltip" data-placement="top" class="btn btn-xs btn-info has-tooltip" href="{concat('editorialstuff/edit/punto/', $punto.object_id, '#tab_osservazioni')|ezurl(no)}">
                                        <i class="fa fa-plus"></i> Aggiungi osservazione
                                    </a>
                                {/if}
                            </td>
                            <td>
                                <a class="btn btn-primary btn-xs" href="{concat('editorialstuff/edit/punto/', $punto.object_id)|ezurl(no)}"><small>Vai al dettaglio</small></a>
                            </td>
                            <td>
                                {attribute_view_gui attribute=$punto.object.data_map.oggetto}
                                {if $punto.count_documenti}
                                <ul class="list-unstyled">
                                {foreach $punto.documenti as $allegato}
                                    <li>{attribute_view_gui attribute=$allegato.object.data_map.file}</li>
                                {/foreach}
                                </ul>
                                {/if}
                            </td>
                            <td>
                                {if $punto.object.data_map.materia.has_content}
                                    {foreach $punto.object.data_map.materia.content.relation_list as $item}
                                        {def $materia = fetch(content, object, hash(object_id,$item.contentobject_id))}
                                        <p class="{*if $materie_like|contains($item.contentobject_id)}text-warning{/if*}"><small><strong>Materia</strong>: {$materia.name|wash()}</small></p>
                                        {undef $materia}
                                    {/foreach}
                                {/if}
                                {if $punto.object|has_attribute('referente_politico')}
                                <p><small><strong>Referente istituzionale</strong>: {attribute_view_gui attribute=$punto.object.data_map.referente_politico}</small></p>
                                {/if}
                                {if $punto.object|has_attribute('referente_tecnico')}
                                <p><small><strong>Referente tecnico</strong>: {attribute_view_gui attribute=$punto.object.data_map.referente_tecnico}</small></p>
                                {/if}
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            </table>
        </div>
    </div>
</div>
<hr />
