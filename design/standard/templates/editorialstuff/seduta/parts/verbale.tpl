{def $editable = $post.can_edit_verbale
     $verbale_object = $post.verbale_object}
<div class="panel-body" style="background: #fff">
    {if $verbale_object}
        <div class="alert alert-warning">
            <a href="{$verbale_object.editorial_url|ezurl(no)}">Vai al verbale</a>
        </div>
    {/if}
    <div class="row">
        <div class="col-xs-12">
            {if $editable}
            <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-horizontal" id="download-verbale">
                <input type="hidden" name="ActionIdentifier" value="CreateVerbaleObject" />
                {if $post.current_state.identifier|ne('closed')}
                <button id="CreateVerbale" type="submit" name="CreateVerbaleObject" class="btn btn-success pull-left hide">Genera Verbale</button>
                <button id="ConfirmCreateVerbale" class="btn btn-warning pull-left">Genera Verbale</button>
                {else}
                <button id="CreateVerbale" type="submit" name="CreateVerbaleObject" class="btn btn-success pull-left">Genera Verbale</button>
                {/if}
            </form>
            <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-horizontal" id="edit-verbale">
            <input type="hidden" name="ActionIdentifier" value="SaveVerbale" />
            <button type="submit" name="SaveVerbale" class="btn btn-danger pull-right">Salva Modifiche</button>
            {/if}
            <table class="table">
                <tbody>
                {foreach $post.verbale_fields as $identifier => $params}
                <tr>
                    <th width="1" style="white-space: nowrap;">
                        {$params.name|wash()}   
                        {if and($editable, $params.default_value|ne(''))}
                        <div>
                            <a href="#" class="btn btn-danger btn-xs resetVerbale" data-identifier="{$identifier}" title="Ricarica valore di default">
                                {if $identifier|eq('partecipanti')}RICALCOLA{else}REIMPOSTA DEFAULT{/if}
                            </a>
                            <textarea style="display: none;">{$params.default_value}</textarea>
                        </div>
                        {/if}                     
                    </th>
                    <td>
                        {if $editable}
                            {if $params.type|eq('string')}
                                <input id="verbaleField-{$identifier}" {if $editable|not()}disabled="disabled"{/if} name="ActionParameters[Verbale][{$identifier}]" class="form-control{if $editable} verbaleField{/if}" value="{$post.verbale[$identifier]}" />
                            {else}
                                <textarea id="verbaleField-{$identifier}" {if $editable|not()}disabled="disabled"{/if} name="ActionParameters[Verbale][{$identifier}]" class="form-control{if $editable} verbaleField{/if}" rows="{$params.rows}">{$post.verbale[$identifier]}</textarea>
                            {/if}
                        {else}     
                            {$post.verbale[$identifier]}                   
                        {/if}
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