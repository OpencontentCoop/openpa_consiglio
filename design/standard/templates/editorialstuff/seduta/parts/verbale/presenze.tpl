{def $registro_presenze = $seduta.registro_presenze.hash_user_id}

<table class="table table-bordered">
	<tbody>
	{foreach $seduta.partecipanti as $politico}
	<tr>
		<td>{$politico.object.name|wash()}</td>
		<td>
			{if $seduta.current_state.identifier|eq( 'closed' )}
				{if $seduta.percentuale_presenza[$post.object.id]|gt(25)}presente{else}assente{/if}
			{else}
			{if and(is_set($registro_presenze[$politico.object.id]), $registro_presenze[$politico.object.id]|eq(1))}presente{else}assente{/if}
			{/if}
		</td>
	</tr>
	{/foreach}
	</tbody>
</table>