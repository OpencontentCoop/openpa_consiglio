{let class_content=$attribute.class_content
     class_list=fetch( class, list, hash( class_filter, $class_content.class_constraint_list ) )
     can_create=true()
     new_object_initial_node_placement=false()
     browse_object_start_node=false()}

{default html_class='full' placeholder=false()}

{if $placeholder}
<label>{$placeholder}</label>
{/if}


{default attribute_base=ContentObjectAttribute}
{let nodesList=fetch( editorialstuff, posts, hash( factory_identifier, 'politico', sort, hash('attr_cognome_s', 'asc'), limit, 200))}

{section var=post loop=$nodesList}
    <div class="checkbox">
      <label>
        <input type="checkbox" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[{$post.object.main_node_id}]" value="{$post.object.id}"
          {if ne( count( $attribute.content.relation_list ), 0)}
          {foreach $attribute.content.relation_list as $item}
               {if eq( $item.contentobject_id, $post.object.id )}
                      checked="checked"
                      {break}
               {/if}
          {/foreach}
          {/if}/>
        {$post.object.name|wash} ({$post.emails|implode(', ')})
      </label>
    </div>
{/section}

{/let}        
{/default}
