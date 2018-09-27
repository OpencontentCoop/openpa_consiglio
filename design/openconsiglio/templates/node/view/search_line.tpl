<div class="row">
<div class="col-sm-12">
  <div class="box">
    <div class="box-content">
      <h4>
        <a class="text-contrast" href="{concat('consiglio/redirect/', $node.contentobject_id)|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a>
        <br /><small>{$node.object.published|l10n(date)} - {$node.class_name}</small>
      </h4>            
    </div>
  </div>
</div>
</div>