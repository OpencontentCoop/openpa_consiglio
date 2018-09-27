<div class="panel-body" style="background: #fff">
    <div class="table-responsive">
        <table class="table table-striped">
            {foreach $post.areas as $area}
                <tr>
                    <td>{$area.object.name|wash()}</td>
                    <td>
                        {if $area.referenti_id_list|contains($post.object_id)}
                            <form action="{concat('editorialstuff/action/referentelocale/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post">
                                <input type="hidden" name="ActionIdentifier" value="RemoveFromArea"/>
                                <input type="hidden" name="ActionParameters[GroupNodeId]" value="{$area.group.main_node_id}"/>
                                <button type="submit" class="btn btn-danger" name="RemoveFromArea">Rimuovi dall'area</button>
                            </form>
                        {else}
                            <form action="{concat('editorialstuff/action/referentelocale/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post">
                                <input type="hidden" name="ActionParameters[GroupNodeId]" value="{$area.group.main_node_id}"/>
                                <input type="hidden" name="ActionIdentifier" value="AddToArea"/>
                                <button type="submit" class="btn btn-success" name="AddToArea">Aggiungi dall'area</button>
                            </form>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>