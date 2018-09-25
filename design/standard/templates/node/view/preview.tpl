{def $default_category = ezini('ClassAttributeSettings', 'DefaultCategory', 'content.ini')}
<div class="content-view-full class-{$node.class_identifier} row">
  <div class="content-main wide">
    
    <h1>{$node.name|wash()}</h1>
    
	{foreach $node.object.contentobject_attributes as $attribute}
	  {if and($node|has_attribute( $attribute.contentclass_attribute_identifier ), or($attribute.contentclass_attribute.category|eq($default_category), $attribute.contentclass_attribute.category|eq('')))}
	  <dl class="dl-horizontal attribute-{$attribute.contentclass_attribute_identifier}">
		<dt>{$attribute.contentclass_attribute_name}</dt>
		<dd>
		  {attribute_view_gui attribute=$attribute}
		</dd>
	  </dl>
	  {/if}
	{/foreach}
    
  </div>
</div>