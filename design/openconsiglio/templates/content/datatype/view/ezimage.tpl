{*
Input:
 image_class - Which image alias to show, default is large
 css_class     - Optional css class to wrap around the <img> tag, the
                 class will be placed in a <div> tag.
 alignment     - How to align the image, use 'left', 'right' or false().
 link_to_image - boolean, if true the url_alias will be fetched and
                 used as link.
 href          - Optional string, if set it will create a <a> tag
                 around the image with href as the link.
 border_size   - Size of border around image, default is 0
*}
{default image_class=large
         css_class=false()
         image_css_class=false()
         alignment=false()
         link_to_image=false()
         href=false()
         target=false()
         hspace=false()
         border_size=0
         border_color=''
         border_style=''
         margin_size=''
         alt_text=''
         id=false()
         fluid=true()
         title=''}

{let image_content = $attribute.content}

{if $image_content.is_valid}

    {let image        = $image_content[$image_class]
         inline_style = ''
		 image_css_classes = array()}

	{if $fluid}
	  {set $image_css_classes = $image_css_classes|append("img-responsive")}
	{/if}
	
	{if $image_css_class}
	  {set $image_css_classes = $image_css_classes|merge($image_css_class|explode(" "))}
	{/if}
    
	{if $link_to_image}
        {set href = $image_content['original'].url|ezroot}
    {/if}
    {switch match=$alignment}
    {case match='left'}
        <div class="pull-left">
    {/case}
    {case match='right'}
        <div class="pull-right">
    {/case}
	{case match='center'}
        {set $image_css_classes = $image_css_classes|append("center-block")}
    {/case}
    {case/}
    {/switch}

    {if $css_class}
        <div class="{$css_class|wash}">
    {/if}

    {if and( is_set( $image ), $image )}
        {if $alt_text|not}
            {if $image.text}
                {set $alt_text = $image.text}
            {else}
                {*set $alt_text = $attribute.object.name*}
                {set $alt_text = ""}
            {/if}
        {/if}
        {if $title|not}
            {set $title = $alt_text}
        {/if}
        {if $border_size|trim|ne('')}
            {set $inline_style = concat( $inline_style, 'border: ', $border_size, 'px ', $border_style, ' ', $border_color, ';' )}
        {/if}
        {if $margin_size|trim|ne('')}
            {set $inline_style = concat( $inline_style, 'margin: ', $margin_size, 'px;' )}
        {/if}
        {if $href}<a title="{$title|wash(xhtml)}" href={$href}{if and( is_set( $link_class ), $link_class )} class="{$link_class}"{/if}{if and( is_set( $link_id ), $link_id )} id="{$link_id}"{/if}{if $target} target="{$target}"{/if}>{/if}
        <img {if $id}id="{$id}"{/if} src={$image.url|ezroot} {if $image_css_classes|count()|gt(0)}class="{$image_css_classes|implode(" ")}"{/if} {if and(is_set($inline_style), ne($inline_style, ''))}{concat('style="', $inline_style, '"')}{/if} {if $hspace}hspace="{$hspace}"{/if} alt="{$alt_text|wash(xhtml)}" title="{$title|wash(xhtml)}" />
        {if $href}</a>{/if}
    {/if}

    {if $css_class}
        </div>
    {/if}

    {switch match=$alignment}
    {case match='left'}
        </div>
    {/case}
    {case match='right'}
        </div>
    {/case}
    {case/}
    {/switch}

    {/let}

{/if}

{/let}

{/default}
