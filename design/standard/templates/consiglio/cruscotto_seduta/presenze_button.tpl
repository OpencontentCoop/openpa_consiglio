{def $registro_presenze = $post.registro_presenze}
<a class="btn btn-info btn-lg pull-right" data-toggle="modal" data-target="#presenzeTemplate">Presenti <span class="badge">{$registro_presenze.in}/{$registro_presenze.total}</span> </a>
{undef $registro_presenze}