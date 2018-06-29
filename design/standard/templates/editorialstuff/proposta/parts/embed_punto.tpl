{set template_directory = $post.template_directory}
<div class="embed_punto">
	<h3>{$post.object.name|wash()} della {$post.seduta.object.name|wash()}</h3>	

	<div role="tabpanel">

	    <ul class="nav nav-tabs" role="tablist">
	        {foreach $post.tabs as $index=> $tab}
	            {if array('content', 'documenti', 'osservazioni', 'history')|contains($tab.identifier)}
	            <li role="presentation"{if $index|eq(0)} class="active"{/if}>
	                <a href="#punto_{$tab.identifier}" aria-controls="{$tab.identifier}"
	                   role="tab" data-toggle="tab">{$tab.name}</a>
	            </li>
	            {/if}
	        {/foreach}
	    </ul>

	    <div class="tab-content">
	        {foreach $post.tabs as $index=> $tab}
	        {if array('content', 'documenti', 'osservazioni', 'history')|contains($tab.identifier)}
	        <div role="tabpanel" class="tab-pane{if $index|eq(0)} active{/if}" id="punto_{$tab.identifier}">
	            {include uri=$tab.template_uri post=$post}
	        </div>
	        {/if}
	        {/foreach}
	    </div>

	</div>

</div>

{literal}
<style>
.embed_punto form, .embed_punto .well {display: none !important}	
</style>
{/literal}