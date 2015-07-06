<div class="panel-body" style="background: #fff">
  <div class="table-responsive">
	<table class="table table-striped">
	  <tr>    
		  <th>Data</th>
		  <th>Autore</th>
		  <th>Azione</th>        
	  </tr>
	  
	  {foreach $post.history as $time => $history_items}    
		{foreach $history_items as $item}    
		<tr>          
		  <td>{$time|l10n( shortdatetime )}</td>
		  <td>{fetch( content, object, hash( 'object_id', $item.user ) ).name|wash()}</td>
		  <td>{switch match=$item.action}
			
			{case match='createversion'}
			  Creata versione <a href={concat( '/content/versionview/', $post.object.id, '/', $item.parameters.version )|ezurl}">{$item.parameters.version}</a> del contenuto
			{/case}
			
			{case match='updateobjectstate'}
			  Modificato stato da {cond( and( is_set( $item.parameters.before_state_name ), $item.parameters.before_state_name|null|not() ), $item.parameters.before_state_name, $item.parameters.before_state_id )} a {cond( and( is_set($item.parameters.after_state_name), $item.parameters.after_state_name|null|not() ), $item.parameters.after_state_name, $item.parameters.after_state_id )}
			{/case}
			
			{case match='addimage'}
			  Aggiunta immagine {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}
			
			{case match='removeimage'}
			  Rimossa immagine {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}
			
			{case match='addvideo'}
			  Aggiunto video {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}
			
			{case match='removevideo'}
			  Rimosso video {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}
			
			{case match='addaudio'}
			  Aggiunto audio {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}

			{case match='removeaudio'}
			  Rimosso audio {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}
      
            {case match='defaultimage'}
			  Impostata immagine default {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}
			{/case}

			{case}
			{$item.action|wash()} {if $item.parameters|count()}{foreach $item.parameters as $name => $value}{$name|wash()}: {$value|wash()} {/foreach}{/if}
			{/case}
			
		  {/switch}</td>
		</tr>    
		{/foreach}
	  {/foreach}
	</table>
  </div>
</div>