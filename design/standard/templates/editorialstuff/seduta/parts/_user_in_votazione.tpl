<a {if is_set($anomalie[$user.contentobject_id])}class="label label-{$anomalie[$user.contentobject_id]}"{/if}
   href="#{$user.contentobject_id}"
   data-url="{concat('layout/set/modal/consiglio/presenze/',$post.object.id, '/',$user.contentobject_id,'/',$votazione.object.id)|ezurl(no)}"
   data-toggle="modal"
   data-target="#detailPresenzeInVotazione">
    <small>{$user.contentobject.name|wash()}</small>
</a>