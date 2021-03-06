{ezscript_require(array( 'ezjsc::jquery', 'plugins/chosen.jquery.js' ))}
{literal}
    <script type="text/javascript">
        jQuery(function($){
            $('#attribute_{/literal}{$attribute.contentclass_attribute.identifier}{literal}').chosen();
        });
    </script>
{/literal}

{if is_set($attribute_base)|not()}
    {def $attribute_base = 'ContentObjectAttribute'}
{/if}


{def $class_content = $attribute.class_content
     $nodesList = fetch( editorialstuff, posts, hash( factory_identifier, 'politico', sort, hash('attr_cognome_s', 'asc'), limit, 200))}     

    <input type="hidden" name="single_select_{$attribute.id}" value="1" />
    {if ne( count( $nodesList ), 0)}
        <select name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]" id="attribute_{$attribute.contentclass_attribute.identifier}" class="form-control" data-placeholder="Seleziona...">
            {if $attribute.contentclass_attribute.is_required|not}
                <option value="no_relation" {if eq( $attribute.content.relation_list|count, 0 )} selected="selected"{/if}></option>
            {/if}
            {section var=post loop=$nodesList}
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
                    {$post.object.data_map.cognome.content|wash} {$post.object.data_map.nome.content|wash}</option>
            {/section}
        </select>
    {/if}


{if eq( count( $nodesList ), 0 )}
    <p>{'There are no objects of allowed classes'|i18n( 'design/standard/content/datatype' )}.</p>
{/if}

