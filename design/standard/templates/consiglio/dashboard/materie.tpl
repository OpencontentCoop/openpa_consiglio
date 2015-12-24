{def $materie_like = fetch( editorialstuff, notification_rules_post_ids, hash( type, 'materia/like', user_id, fetch(user,current_user).contentobject_id ) )}
{def $materie = fetch( editorialstuff, posts, hash( factory_identifier, materia, limit, 1000, sort, hash( 'name', 'asc' ) ) )}

<form class="form" action="{'consiglio/like'|ezurl(no)}">
<table class="table table-striped">
{foreach $materie as $materia}
    <tr>
        <td>{$materia.object.name|wash()}</td>
        <td>
        {if $materie_like|contains($materia.object_id)}
            <button data-url="{'consiglio/like'|ezurl(no)}" type="submit" name="RemoveMateria" value="{$materia.object_id}" class="btn btn-danger change-like"><i class="fa fa-times"></i> Rimuovi dai preferiti</button>
        {else}
            <button data-url="{'consiglio/like'|ezurl(no)}" type="submit" name="AddMateria" value="{$materia.object_id}" class="btn btn-success change-like"><i class="fa fa-check"></i> Aggiungi ai preferiti</button>
        {/if}
        </td>
    </tr>
{/foreach}
</table>
</form>

{undef $materie_like $materie}

{ezscript_require( array( 'ezjsc::jquery' ) )}
<script>{literal}
$(document).ready(function(){
   $(document).on( 'click', 'button.change-like', function(e){
       var button = $(e.currentTarget);
       var data = {};
       var action = button.attr('name');
       data[action] = button.attr('value');
       data['AjaxMode'] = true;
       var originalContent = button.html();
       button.html('<i class="fa fa-gear fa-spin"></i> Caricamento');
       $.get(button.data('url'),data,function(response){
           if ( response == 1 ){
              if (action=='RemoveMateria') {
                  button.html('<i class="fa fa-check"></i> Aggiungi ai preferiti');
                  button.removeClass('btn-danger').addClass('btn-success');
                  button.attr( 'name', 'AddMateria' );
              }else if (action=='AddMateria'){
                  button.html('<i class="fa fa-times"></i> Rimuovi dai preferiti');
                  button.removeClass('btn-success').addClass('btn-danger');
                  button.attr( 'name', 'RemoveMateria' );
              }
          }else{
              button.html(originalContent);
          }
       });
       e.preventDefault();
   });
});
{/literal}</script>