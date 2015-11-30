{def $name = concat( $partecipante.object.data_map.nome.content|wash(), ' <strong>', $partecipante.object.data_map.cognome.content|wash(), '</strong>' )}
<a {if is_set($anomalie[$partecipante.object.id])}class="label label-{$anomalie[$partecipante.object.id]}"{/if}
   href="#{$partecipante.object.id}"
   data-url="{concat('layout/set/modal/consiglio/presenze/',$post.object.id, '/',$partecipante.object.id,'/',$votazione.object.id)|ezurl(no)}"
   data-toggle="modal"
   data-target="#detailPresenzeInVotazione">
    <small>{$name}</small>
</a>
{undef $name}