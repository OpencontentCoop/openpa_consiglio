{foreach $post.verbale_fields as $identifier => $params}
	<div id="{$identifier}" class="verbalePart" style="margin-bottom: 5px">
		<h4>{$params.name|wash()}</h4>
		<div>
			{$post.verbale[$identifier]}
		</div>
	</div>	
{/foreach}
