<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
  <a href="{$module_result.uri|explode( '/layout/set/modal' )|implode('')}" class="btn btn-info" target="_blank">Visualizza sul sito</a>
</div>
<div class="modal-body">


<div class="container-fluid">
  {$module_result.content}
</div>


</div>

{* Codice extra usato da plugin javascript *}
{include uri='design:page_extra.tpl'}


