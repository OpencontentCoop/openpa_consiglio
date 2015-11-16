<div class="panel-body" style="background: #fff">
    <div class="table-responsive">
        <table class="table table-striped">
            {foreach $post.politici as $politicoAcces}
                <tr>
                    <td>{$politicoAcces.area.name|wash()}</td>
                    <td>
                        {if $politicoAcces.is_active}
                            <form action="{concat('editorialstuff/action/referentelocale/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post">
                                <input type="hidden" name="ActionIdentifier" value="RemoveFromArea"/>
                                <input type="hidden" name="ActionParameters[GroupNodeId]" value="{$politicoAcces.area_group.main_node_id}"/>
                                <button type="submit" class="btn btn-danger" name="RemoveFromArea">Rimuovi dall'area</button>
                            </form>
                        {else}
                            <form action="{concat('editorialstuff/action/referentelocale/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post">
                                <input type="hidden" name="ActionParameters[GroupNodeId]" value="{$politicoAcces.area_group.main_node_id}"/>
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