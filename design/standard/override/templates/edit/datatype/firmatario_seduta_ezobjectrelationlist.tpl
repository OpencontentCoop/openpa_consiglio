{let class_content=$attribute.class_content
     class_list=fetch( class, list, hash( class_filter, $class_content.class_constraint_list ) )
     can_create=true()
     new_object_initial_node_placement=false()
     browse_object_start_node=false()}

{default html_class='full' placeholder=false()}

{if $placeholder}<label>{$placeholder}</label>{/if}


{default attribute_base=ContentObjectAttribute}
{def $nestedNodesList = fetch( editorialstuff, posts, hash( factory_identifier, 'politico'))}

<input type="hidden" name="single_select_{$attribute.id}" value="1" />
{if ne( count( $nestedNodesList ), 0)}
    <select class="{$html_class}" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]">
        {if $attribute.contentclass_attribute.is_required|not}
            <option value="no_relation" {if eq( $attribute.content.relation_list|count, 0 )} selected="selected"{/if}> </option>
        {/if}
        {section var=post loop=$nestedNodesList}
            <option value="{$post.object.id}"
                    {if ne( count( $attribute.content.relation_list ), 0)}
                        {foreach $attribute.content.relation_list as $item}
                            {if eq( $item.contentobject_id, $post.object.id )}
                                selected="selected"
                                {break}
                            {/if}
                        {/foreach}
                    {/if}
                    >
                {$post.object.name|wash}</option>
        {/section}
    </select>
{/if}

{/default}
