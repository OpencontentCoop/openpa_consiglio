{def $materie_like = fetch( editorialstuff, notification_rules_post_ids, hash( type, 'materia/like', user_id, fetch(user,current_user).contentobject_id ) )}
{def $materie = fetch( editorialstuff, posts, hash( factory_identifier, materia, sort, hash( 'name', 'asc' ) ) )}

<form class="form" action="{'consiglio/like'|ezurl(no)}">
<table class="table table-striped">
{foreach $materie as $materia}
    <tr>
        <td>{$materia.object.name|wash()}</td>
        <td>
        {if $materie_like|contains($materia.object_id)}
            <button type="submit" name="RemoveMateria" value="{$materia.object_id}" class="btn btn-danger"><i class="fa fa-times"></i></button>
        {else}
            <button type="submit" name="AddMateria" value="{$materia.object_id}" class="btn btn-success"><i class="fa fa-check"></i></button>
        {/if}
        </td>
    </tr>
{/foreach}
</table>
</form>

{undef $materie_like $materie}