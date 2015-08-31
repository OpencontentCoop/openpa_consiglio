<h3>Risultati votazione {attribute_view_gui attribute=$post.object.data_map.short_text}</h3>
<p class="text">{attribute_view_gui attribute=$post.object.data_map.text}</p>
{if $post.current_state.identifier|eq('closed')}
<table class="list">
    <tr>
      <th>Presenti</th>
      <td class="presenti">{attribute_view_gui attribute=$post.object.data_map.presenti}</td>
    </tr>
    <tr>
      <th>Votanti</th>
      <td class="votanti">{attribute_view_gui attribute=$post.object.data_map.votanti}</td>
    </tr>
    <tr>
      <th>Favorevoli</th>
      <td class="favorevoli">{attribute_view_gui attribute=$post.object.data_map.favorevoli}</td>
    </tr>
    <tr>
      <th>Contrari</th>
      <td class="contrari">{attribute_view_gui attribute=$post.object.data_map.contrari}</td>
    </tr>
    <tr>
      <th>Astenuti</th>
      <td class="astenuti">{attribute_view_gui attribute=$post.object.data_map.astenuti}</td>
    </tr>
</table>
{/if}