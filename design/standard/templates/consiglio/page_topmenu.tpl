{def $module_params = module_params()
     $module = $module_params.module_name
     $function = $module_params.function_name
     $param = cond( is_set( $module_params.parameters.FactoryIdentifier ), $module_params.parameters.FactoryIdentifier, false() )}
{def $current_module = concat( $module, '/', $function, '/', $param )}

<div class="container">
    <div id="navigation" class="menu_wrap">

        <button id="menu_button">
            <span class="centered_db "></span>
            <span class="centered_db "></span>
            <span class="centered_db "></span>
        </button>

        <div class="main-nav" role="navigation">
            <ul class="horizontal_list main_menu clearfix">
                {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'use' ))}
                    <li class="menu-item{if $current_module|eq('consiglio/dashboard/')} current{/if}"><a href="{'consiglio/dashboard'|ezurl(no)}"><b>Bacheca</b></a></li>
                {/if}

                {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'collaboration' ))}
                    <li class="menu-item{if $current_module|eq('consiglio/collaboration/')} current{/if}"><a href="{'consiglio/collaboration'|ezurl(no)}"><b>Area collaborativa</b></a></li>
                {/if}

                {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                <li class="menu-item{if or($current_module|eq('editorialstuff/dashboard/seduta'),$current_module|eq('editorialstuff/dashboard/audizione'))} current{/if}">
                    <a href="#"><b>Attivit&agrave;</b></a>
                    <div class="sub_menu_wrap">
                        <ul class="sub_menu">
                            <li><a href="{'editorialstuff/dashboard/seduta'|ezurl(no)}">Sedute</a></li>
                            <li><a href="{'editorialstuff/dashboard/audizione'|ezurl(no)}">Audizioni</a></li>
                            <li><a href="{'editorialstuff/dashboard/designazione'|ezurl(no)}">Designazioni</a></li>
                            <li><a href="{'editorialstuff/dashboard/parere'|ezurl(no)}">Pareri</a></li>
                        </ul>
                    </div>
                </li>
                <li class="menu-item{if or($current_module|eq('editorialstuff/dashboard/materia'),$current_module|eq('editorialstuff/dashboard/politico'),$current_module|eq('editorialstuff/dashboard/tecnico'),$current_module|eq('editorialstuff/dashboard/invitato'),$current_module|eq('editorialstuff/dashboard/referentelocale'))} current{/if}">
                    <a href="#"><b>Gestione</b></a>
                    <div class="sub_menu_wrap">
                        <ul class="sub_menu">
                            <li><a href="{'editorialstuff/dashboard/materia'|ezurl(no)}">Materie</a></li>
                            <li><a href="{'editorialstuff/dashboard/politico'|ezurl(no)}">Politici</a></li>
                            <li><a href="{'editorialstuff/dashboard/tecnico'|ezurl(no)}">Tecnici</a></li>
                            <li><a href="{'editorialstuff/dashboard/invitato'|ezurl(no)}">Invitati</a></li>
                            <li><a href="{'consiglio/gettoni'|ezurl(no)}">Gettoni di presenza</a></li>
                            <li><a href="{'editorialstuff/dashboard/referentelocale'|ezurl(no)}">Referenti locali</a></li>
                        </ul>
                    </div>
                </li>
                {elseif fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'use' ))}
                  <li class="menu-item{if $current_module|eq('editorialstuff/dashboard/seduta')} current{/if}"><a href="{'editorialstuff/dashboard/seduta'|ezurl(no)}"><b>Archivio sedute</b></a></li>
                  <li class="menu-item{if $current_module|eq('editorialstuff/dashboard/audizione')} current{/if}"><a href="{'editorialstuff/dashboard/audizione'|ezurl(no)}"><b>Archivio audizioni</b></a></li>
                  <li class="menu-item{if $current_module|eq('editorialstuff/dashboard/parere')} current{/if}"><a href="{'editorialstuff/dashboard/parere'|ezurl(no)}"><b>Archivio pareri</b></a></li>
                {/if}
            </ul>
        </div>

    </div>
</div>

