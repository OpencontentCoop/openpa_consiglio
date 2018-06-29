{def $name = concat( $partecipante.object.data_map.nome.content|wash(), ' <strong>', $partecipante.object.data_map.cognome.content|wash(), '</strong>' )}
{if is_set($anomalie[$partecipante.object.id])}
    {if is_set( $is_monitor )|not()}
    <button class="btn btn-xs btn-{$anomalie[$partecipante.object.id]}"
            data-toggle="modal"
            data-target="#detailPresenzeInVotazione"
            data-modal_configuration="detailPresenzeInVotazione"
            data-load_url="{concat('layout/set/modal/consiglio/presenze/',$votazione.seduta_id, '/',$partecipante.object.id,'/',$votazione.object.id)|ezurl(no)}">
        {$name}
    </button>
    {else}
        <small class="label label-{$anomalie[$partecipante.object.id]}">{$name|wash()}</small>
    {/if}

{else}
    <small>{$name}</small>
{/if}
{undef $name}