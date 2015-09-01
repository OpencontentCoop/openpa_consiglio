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
            {$post.object.name|wash()}
            <span>{include uri='design:editorialstuff/seduta/parts/state.tpl' post=$post}</span>
            <a class="btn btn-primary btn-xs" href="{concat('editorialstuff/edit/seduta/', $post.object_id)|ezurl(no)}">Vai al dettaglio</a>
        </h3>
    </div>
</div>
<hr />