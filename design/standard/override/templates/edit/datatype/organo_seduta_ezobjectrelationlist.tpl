{let class_content=$attribute.class_content
     class_list=fetch( class, list, hash( class_filter, $class_content.class_constraint_list ) )}

{default html_class='full' placeholder=false()}
{if $placeholder} <label>{$placeholder}</label>{/if}

{default attribute_base=ContentObjectAttribute}
{let
    parent_node= cond( and( is_set( $class_content.default_placement.node_id ),
                       $class_content.default_placement.node_id|eq( 0 )|not ),
                       $class_content.default_placement.node_id, 1)

    nodesList= cond( and( is_set( $class_content.class_constraint_list ), $class_content.class_constraint_list|count|ne( 0 ) ),
                    fetch( content, tree, hash( parent_node_id, $parent_node,
                                                class_filter_type,'include',
                                                class_filter_array, $class_content.class_constraint_list,
                                                sort_by, array( 'priority',true() ),main_node_only, true() ) ),
                    fetch( content, list, hash( parent_node_id, $parent_node,
                                                sort_by, array( 'priority', true() ) ) ) ) }


<input type="hidden" name="single_select_{$attribute.id}" value="1" />

{section var=node loop=$nodesList}
    <div class="radio">
        <label>
            <input type="radio" data-value="{$node.name|wash|downcase()}" class="consiglio_select_organo_seduta" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]" value="{$node.contentobject_id}"
                    {if ne( count( $attribute.content.relation_list ), 0)}
                        {foreach $attribute.content.relation_list as $item}
                            {if eq( $item.contentobject_id, $node.contentobject_id )}
                                checked="checked"
                                {break}
                            {/if}
                        {/foreach}
                    {/if}
                    >
            {$node.name|wash}
        </label>
    </div>
{/section}

{literal}
<script>
    $(document).ready(function(){
        var setOrario = function( $hour, $minute ){
            var container = $('.ezcca-edit-orario');
            container.find("input[name*='_time_hour_']").val( $hour );
            container.find("input[name*='_time_minute_']").val( $minute );
        };

        $(document).on( 'change', '.consiglio_select_organo_seduta', function(){
            var organo = $(this).parents( '.radio').find( 'input:checked').data( 'value' );
            if ( organo == 'consiglio' ){
                setOrario( 14, 30 );
            }else if ( organo == 'giunta' ){
                setOrario( 15, 00 );
            }
        });
    });
</script>
{/literal}

{/let}
{/default}