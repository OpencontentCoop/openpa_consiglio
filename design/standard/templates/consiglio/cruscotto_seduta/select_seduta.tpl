<div class="row">
  <div class="col col-md-12">
	{def $sedute = fetch( editorialstuff, posts, hash( factory_identifier, seduta, state, array( 'in_progress', 'sent' ) ) )}
	<h1>Seleziona seduta per cruscotto verbalizzatore</h1>
	<table class="table">
	{foreach $sedute as $seduta}
	  <tr>
		<td><h2>{$seduta.object.name|wash()} ore {attribute_view_gui attribute=$seduta.object.data_map.orario} <small>{$seduta.current_state.current_translation.name|wash()}</small></h2></td>
		<td><a class="btn btn-lg btn-primary" href="{concat('consiglio/cruscotto_seduta/', $seduta.object_id)|ezurl(no)}">Apri cruscotto</a></td>
		<td><a target="_blank" class="btn btn-lg btn-info" href="{concat('editorialstuff/edit/seduta/', $seduta.object_id)|ezurl(no)}">Modifica</a></td>
	  </tr>
	{/foreach}
	</table>
  </div>
</div>
