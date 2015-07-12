{ezscript_require(array( 'ezjsc::jquery', 'plugins/chosen.jquery.js' ))}
{literal}
    <script type="text/javascript">
        jQuery(function($){
            $('#attribute_{/literal}{$attribute.id}{literal}').chosen();
        });
    </script>
{/literal}

{def $attribute_base = ContentObjectAttribute
     $class_content = $attribute.class_content
     $parent_node=cond( and( is_set( $class_content.default_placement.node_id ),
                           $class_content.default_placement.node_id|eq( 0 )|not ),
                           $class_content.default_placement.node_id, 1 )

     $nestedNodesList = fetch( content, tree, hash( parent_node_id, $parent_node,
                                                    class_filter_type,'include',
                                                    class_filter_array, array( 'tecnico' ),
                                                    sort_by, array( 'name',true() ),
                                                    main_node_only, true() ) )}

    <input type="hidden" name="single_select_{$attribute.id}" value="1" />
    {if ne( count( $nestedNodesList ), 0)}
        <select name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]" id="attribute_{$attribute.id}" class="form-control">
            {if $attribute.contentclass_attribute.is_required|not}
                <option value="no_relation" {*if eq( $attribute.content.relation_list|count, 0 )} selected="selected"{/if*}>{'No relation'|i18n( 'design/standard/content/datatype' )}</option>
            {/if}
            {section var=node loop=$nestedNodesList}
                <option value="{$node.contentobject_id}"
                        {if ne( count( $attribute.content.relation_list ), 0)}
                            {foreach $attribute.content.relation_list as $item}
                                {if eq( $item.contentobject_id, $node.contentobject_id )}
                                    selected="selected"
                                    {break}
                                {/if}
                            {/foreach}
                        {/if}
                        >
                    {$node.name|wash}</option>
            {/section}
        </select>
    {/if}


{if eq( count( $nestedNodesList ), 0 )}
    {def $parentnode = fetch( 'content', 'node', hash( 'node_id', $parent_node ) )}
    {if is_set( $parentnode )}
        <p>{'Parent node'|i18n( 'design/standard/content/datatype' )}: {node_view_gui content_node=$parentnode view=objectrelationlist} </p>
    {/if}
    <p>{'Allowed classes'|i18n( 'design/standard/content/datatype' )}:</p>
    {if ne( count( $class_content.class_constraint_list ), 0 )}
         <ul>
         {foreach $class_content.class_constraint_list as $class}
               <li>{$class}</li>
         {/foreach}
         </ul>
    {else}
         <ul>
               <li>{'Any'|i18n( 'design/standard/content/datatype' )}</li>
         </ul>
    {/if}
    <p>{'There are no objects of allowed classes'|i18n( 'design/standard/content/datatype' )}.</p>
{/if}

