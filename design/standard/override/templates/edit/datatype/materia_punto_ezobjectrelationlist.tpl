{ezscript_require(array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'plugins/chosen.jquery.js' ))}
{literal}
    <script type="text/javascript">

        function getOptions(inputID, targetID, attribute )
        {
            var inputField = $(inputID),
                targetField = $(targetID);
            if(inputField.length > 0) {
                inputField.each(function(i){
                    $(this).change(function(){

                        $.ez( 'openpaconsiglioxhr::getOptions', {objID: $(this).val(), attribute: attribute}, function( data ) {
                            $(targetField[i]).val(data.content);
                            $(targetField[i]).trigger("chosen:updated");
                        });
                    });
                });
            }
        }

        jQuery(function($){
            getOptions('#attribute_{/literal}{$attribute.contentclass_attribute.identifier}{literal}', '#attribute_referente_politico', 'referente_politico');
            getOptions('#attribute_{/literal}{$attribute.contentclass_attribute.identifier}{literal}', '#attribute_referente_tecnico', 'referente_tecnico');
            $('#attribute_{/literal}{$attribute.contentclass_attribute.identifier}{literal}').chosen();            
            if ($('#attribute_referente_politico').val() == 'no_relation' && $('#attribute_referente_tecnico').val() == 'no_relation'){
                $('#attribute_{/literal}{$attribute.contentclass_attribute.identifier}{literal}').trigger('change');
            }

        });
    </script>
{/literal}

{if is_set($attribute_base)|not()}
    {def $attribute_base = 'ContentObjectAttribute'}
{/if}

{def $class_content = $attribute.class_content
     $parent_node=cond( and( is_set( $class_content.default_placement.node_id ),
                           $class_content.default_placement.node_id|eq( 0 )|not ),
                           $class_content.default_placement.node_id, 1 )

     $nestedNodesList = fetch( content, tree, hash( parent_node_id, $parent_node,
                                                    class_filter_type,'include',
                                                    class_filter_array, array( 'materia' ),
                                                    sort_by, array( 'name',true() ),
                                                    main_node_only, true() ) )}

    <input type="hidden" name="single_select_{$attribute.id}" value="1" />
    {if ne( count( $nestedNodesList ), 0)}
        <select name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]" id="attribute_{$attribute.contentclass_attribute.identifier}" class="form-control" data-placeholder="Seleziona...">
            <option value="no_relation" {if eq( $attribute.content.relation_list|count, 0 )} selected="selected"{/if}></option>
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

