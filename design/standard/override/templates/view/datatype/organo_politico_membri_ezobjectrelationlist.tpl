{section show=$attribute.content.relation_list}
<table class="table">
{section var=Relations loop=$attribute.content.relation_list}
{if $Relations.item.in_trash|not()}
    {def $content_object=fetch( content, object, hash( object_id, $Relations.item.contentobject_id ) )}    
    <tr>    	    	
		<td><strong>{$content_object.name|wash()}</strong></td>
		{if is_set($content_object.data_map.user_account)}
			<td>{$content_object.data_map.user_account.content.email}</td>
		{/if}
    </tr>
    {undef $content_object}
{/if}
{/section}
</table>
{/section}
