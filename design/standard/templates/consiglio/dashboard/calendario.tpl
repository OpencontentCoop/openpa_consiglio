{ezscript_require(array('ezjs::jquery','jquery.editorialstuff_default.js'))}
{def $materie_like = fetch( editorialstuff, notification_rules_post_ids, hash( type, 'materia/like', user_id, fetch(user,current_user).contentobject_id ) )}
{def $sedute = fetch( editorialstuff, posts, hash( factory_identifier, seduta, sort, hash( 'published', 'desc' ) ) )}

{foreach $sedute as $seduta}
    <div class="row">
        <div class="col-md-12">
            <h3>
                Seduta di {attribute_view_gui attribute=$seduta.object.data_map.organo show_link=false()}
                <a class="btn btn-primary btn-xs" href="{concat('editorialstuff/edit/seduta/', $seduta.object_id)|ezurl(no)}">LEGGI</a>
                <span class="pull-right">{include uri='design:editorialstuff/seduta/parts/state.tpl' post=$seduta}</span>
            </h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2 col-lg-1 events text-center">

            <div class="calendar-date" style="min-width: 50px">
                <span class="month">{$seduta.data_ora|datetime( 'custom', '%M' )}</span>
                <span class="day">{$seduta.data_ora|datetime( 'custom', '%j' )}</span>
                <strong>ore {attribute_view_gui attribute=$seduta.object.data_map.orario}</strong>
            </div>
        </div>
        <div class="col-sm-10 col-lg-11">

            <div class="table-responsive">
                <table class="table table-striped">
                    {foreach $seduta.odg as $punto}
                        {if $punto.object.can_read}
                        <tr>
                            <td>
                                {attribute_view_gui attribute=$punto.object.data_map.n_punto}
                            </td>
                            <td>
                                <strong>{attribute_view_gui attribute=$punto.object.data_map.orario_trattazione}</strong>
                            </td>
                            <td>
                                {if $punto.can_add_osservazioni}
                                    <a title="Aggiungi osservazione" data-toggle="tooltip" data-placement="top" class="btn btn-xs btn-info has-tooltip" href="{concat('editorialstuff/edit/punto/', $punto.object_id, '#tab_osservazioni')|ezurl(no)}">
                                        <i class="fa fa-plus"></i> Aggiungi osservazione
                                    </a>
                                {/if}
                            </td>
                            <td>
                                <a class="btn btn-primary btn-xs" href="{concat('editorialstuff/edit/punto/', $punto.object_id)|ezurl(no)}"><small>LEGGI</small></a>
                            </td>
                            <td>
                                {attribute_view_gui attribute=$punto.object.data_map.oggetto}
                            </td>
                            <td>
                                {if $punto.object.data_map.materia.has_content}
                                    {foreach $punto.object.data_map.materia.content.relation_list as $item}
                                        <span class="label {if $materie_like|contains($item.contentobject_id)}label-warning{else}label-default{/if}">
										  {fetch(content, object, hash(object_id,$item.contentobject_id)).name|shorten('30')|wash()}
										</span>
                                    {/foreach}
                                {/if}
                            </td>
                        </tr>
                        {/if}
                    {/foreach}
                </table>
            </div>
        </div>
    </div>
    {delimiter}<hr />{/delimiter}
{/foreach}



