{default $view_parameters            = array()
         $attribute_categorys        = ezini( 'ClassAttributeSettings', 'CategoryList', 'content.ini' )
         $attribute_default_category = ezini( 'ClassAttributeSettings', 'DefaultCategory', 'content.ini' )}

{def $is_admin = cond(fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' )), true(), false())}
{def $count = 0}
{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
{if $attribute_group|eq('hidden')}
    {skip}
{/if}
{if and($attribute_group|eq('admin'), $is_admin|not())}
    {skip}
{/if}
{set $count = $count|inc()}
{/foreach}

{if $count|gt(1)}
{set $count = 0}
<ul class="nav nav-tabs">
{set $count = 0}
{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
{if $attribute_group|eq('hidden')}
    {skip}
{/if}
{if and($attribute_group|eq('admin'), $is_admin|not())}
    {skip}
{/if}
<li class="{if $count|eq(0)} active{/if}">
    <a data-toggle="tab" href="#attribute-group-{$attribute_group}">{$attribute_categorys[$attribute_group]}</a>
</li>
{set $count = $count|inc()}
{/foreach}
<li class="pull-right"><a data-toggle="tab" href="#contentactions">Informazioni generali</a></li>
</ul>
{/if}

<div class="tab-content">
{set $count = 0}
{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
<div class="clearfix attribute-edit tab-pane{if $count|eq(0)} active{/if}" id="attribute-group-{$attribute_group}" {if $attribute_group|eq('hidden')}style="display: none" {/if}>
{set $count = $count|inc()}

{foreach $content_attributes_grouped as $attribute_identifier => $attribute}
{def $contentclass_attribute = $attribute.contentclass_attribute}
<div class="row edit-row ezcca-edit-datatype-{$attribute.data_type_string} ezcca-edit-{$attribute_identifier}">
{* Show view GUI if we can't edit, otherwise: show edit GUI. *}
{if and( eq( $attribute.can_translate, 0 ), ne( $object.initial_language_code, $attribute.language_code ) )}
    <label>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
        {if $attribute.can_translate|not} <span class="nontranslatable">({'not translatable'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:
        {if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
    </label>
    {if $is_translating_content}
        <div class="original">
        {attribute_view_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
        <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
        </div>
    {else}
        {attribute_view_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
        <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
    {/if}
{else}
    {if $is_translating_content}
        <label{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
            {if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
            {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:
            {if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
        </label>
        <div class="original">
        {attribute_view_gui attribute_base=$attribute_base attribute=$from_content_attributes_grouped_data_map[$attribute_group][$attribute_identifier] view_parameters=$view_parameters}
        </div>
        <div class="translation">
        {if $attribute.display_info.edit.grouped_input}
            <fieldset>
            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
            </fieldset>
        {else}
            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
        {/if}
        </div>
    {else}
        {if $attribute.display_info.edit.grouped_input}
            <div class="col-md-3">
                <p{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                    {if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
                    {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}                    
                </p>
            </div>
            <div class="col-md-9">
                {if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
                {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
            </div>
        {else}
            <div class="col-md-3">
                <p{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                    {if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
                    {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}                    
                </p>
            </div>
            <div class="col-md-9">
                {if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
                {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
                <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
            </div>
        {/if}
    {/if}
{/if}
</div>
{undef $contentclass_attribute}
{/foreach}
    </div>
{/foreach}
    <div class="clearfix attribute-edit tab-pane" id="contentactions">
        {include uri="design:content/edit_right_menu.tpl"}
    </div>
</div>