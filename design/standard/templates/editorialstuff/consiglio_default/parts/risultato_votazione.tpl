<h3>{if $post.current_state.identifier|eq('closed')}Risultati votazione{else}Votazione{/if} {attribute_view_gui attribute=$post.object.data_map.short_text}</h3>
<p class="text">{attribute_view_gui attribute=$post.object.data_map.text}</p>
{if $post.current_state.identifier|eq('closed')}
<table class="list">
    <tr>
      <th>Presenti</th>
      <td class="presenti">{attribute_view_gui attribute=$post.object.data_map.presenti}</td>
      <td class="presenti">{foreach $post.presenti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
    </tr>
    <tr>
      <th>Votanti</th>
      <td class="votanti">{attribute_view_gui attribute=$post.object.data_map.votanti}</td>
        <td class="votanti">{foreach $post.votanti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
    </tr>
    <tr>
      <th>Favorevoli</th>
      <td class="favorevoli">{attribute_view_gui attribute=$post.object.data_map.favorevoli}</td>
        <td class="favorevoli">{foreach $post.favorevoli as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
    </tr>
    <tr>
      <th>Contrari</th>
      <td class="contrari">{attribute_view_gui attribute=$post.object.data_map.contrari}</td>
        <td class="contrari">{foreach $post.contrari as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}){/foreach}</td>
    </tr>
    <tr>
      <th>Astenuti</th>
      <td class="astenuti">{attribute_view_gui attribute=$post.object.data_map.astenuti}</td>
        <td class="astenuti">{foreach $post.astenuti as $user}{$user.contentobject.name|wash()}{delimiter}, {/delimiter}{/foreach}</td>
    </tr>
</table>
{/if}