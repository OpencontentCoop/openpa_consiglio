<h3>Risultati votazione {attribute_view_gui attribute=$post.object.data_map.short_text}</h3>
<p class="text">{attribute_view_gui attribute=$post.object.data_map.text}</p>
{if $post.current_state.identifier|eq('closed')}
<dl class="dl-horizontal">
    <dt>Presenti</dt>
    <dd class="presenti">{attribute_view_gui attribute=$post.object.data_map.presenti}</dd>
    <dt>Votanti</dt>
    <dd class="votanti">{attribute_view_gui attribute=$post.object.data_map.votanti}</dd>
    <dt>Favorevoli</dt>
    <dd class="favorevoli">{attribute_view_gui attribute=$post.object.data_map.favorevoli}</dd>
    <dt>Contrari</dt>
    <dd class="contrari">{attribute_view_gui attribute=$post.object.data_map.contrari}</dd>
    <dt>Astenuti</dt>
    <dd class="astenuti">{attribute_view_gui attribute=$post.object.data_map.astenuti}</dd>
</dl>
{/if}