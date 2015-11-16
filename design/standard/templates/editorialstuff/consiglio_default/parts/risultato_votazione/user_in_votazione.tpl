{if is_set($anomalie[$user.contentobject_id])}
    {if is_set( $is_monitor )|not()}
    <button class="btn btn-xs btn-{$anomalie[$user.contentobject_id]}"
            data-toggle="modal"
            data-target="#detailPresenzeInVotazione"
            data-modal_configuration="detailPresenzeInVotazione"
            data-load_url="{concat('layout/set/modal/consiglio/presenze/',$votazione.seduta_id, '/',$user.contentobject_id,'/',$votazione.object.id)|ezurl(no)}">
        {$user.contentobject.name|wash()}
    </button>
    {else}
        <small class="label label-{$anomalie[$user.contentobject_id]}">{$user.contentobject.name|wash()}</small>
    {/if}

{else}
    <small>{$user.contentobject.name|wash()}</small>
{/if}