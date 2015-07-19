{def $registro_presenze = $post.registro_presenze}
<a class="btn btn-info btn-lg pull-right" data-toggle="modal" data-whatever="@presenze" data-target="#presenzeTemplate">Presenti <span class="badge">{$registro_presenze.in}/{$registro_presenze.total}</span> </a>

<div class="modal fade" id="presenzeTemplate" tabindex="-1" role="dialog" aria-labelledby="previewLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="previewLabel">Presenze</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                {foreach $post.partecipanti as $partecipante}
                    <div class="col-xs-2"{if $registro_presenze.hash_user_id[$partecipante.object_id]|not} style="opacity: .4"{/if}>
                        {content_view_gui content_object=$partecipante.object view="politico_box"}
                    </div>
                {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>