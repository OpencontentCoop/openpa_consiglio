{def $editable = and( $post.current_state.identifier|eq('closed'), $post.object.can_edit )}
<div class="panel-body" style="background: #fff">
    <div class="row">
        <div class="col-xs-12">
            {if $editable}
            <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-horizontal" id="edit-verbale">
            <input type="hidden" name="ActionIdentifier" value="SaveVerbale" />
            <button type="submit" name="SaveVerbale" class="btn btn-danger pull-right">Salva Modifiche</button>
            {/if}
            <table class="table">
                <tbody>
                <tr>
                    <th>Generale</th>
                    <td>
                        <textarea {if $editable|not()}disabled="disabled"{/if} name="ActionParameters[Verbale][{$post.object.id}]" class="form-control" rows="10">{$post.verbale}</textarea>
                    </td>
                </tr>
                {foreach $post.odg as $punto}
                    <tr>
                        <th>{$punto.object.name|wash()}</th>
                        <td>
                            <textarea {if $editable|not()}disabled="disabled"{/if} name="ActionParameters[Verbale][{$punto.object.id}]" class="form-control" rows="10">{$punto.verbale}</textarea>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            {if $editable}</form>{/if}
        </div>
    </div>
</div>
{undef $editable}