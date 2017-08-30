{def $module_params = module_params()
     $module = $module_params.module_name
     $function = $module_params.function_name
     $param = cond( is_set( $module_params.parameters.FactoryIdentifier ), $module_params.parameters.FactoryIdentifier, false() )}
{def $current_module = concat( $module, '/', $function, '/', $param )}
{def $root_node = fetch( 'content', 'node', hash( 'node_id', $pagedata.root_node ) )}
{def $active_dashboards = fetch(consiglio, active_dashboards)}

<div class="nav-main container">
    <div class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="container">
            <div class="row">

                <div class="col-lg-3">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-navbar">
                        <span class="sr-only">Mostra navigazione</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="brand center-block" href={$root_node.url_alias|ezurl} title="{$root_node.name|wash}">
                        <img class="img-responsive center-block"
                             src={'logo.png'|ezimage()} alt="{$root_node.name|wash}"/>
                    </a>
                </div>

                <div class="col-lg-9">

                    <div class="row">
                        <div class="col-lg-4 col-lg-offset-8">
                            {include uri='design:page_header_searchbox.tpl'}
                        </div>
                        <div class="col-lg-12">
                            <div class="collapse navbar-collapse" id="main-navbar">
                                <ul class="nav navbar-nav">
                                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'use' ))}
                                        <li class="menu-item{if $current_module|eq('consiglio/dashboard/')} current{/if}">
                                            <a href="{'consiglio/dashboard'|ezurl(no)}"><b>Area riservata</b></a></li>
                                    {/if}

                                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'collaboration' ))}
                                        <li class="menu-item{if $current_module|eq('consiglio/collaboration/')} current{/if}">
                                            <a href="{'consiglio/collaboration'|ezurl(no)}"><b>Area
                                                    collaborativa</b></a></li>
                                    {/if}

                                    {if fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'admin' ))}
                                        <li class="menu-item{if or($current_module|eq('editorialstuff/dashboard/seduta'),$current_module|eq('editorialstuff/dashboard/audizione'))} current{/if}">
                                            <a href="#" class="dropdown-toggle"
                                               data-toggle="dropdown"><b>Attivit&agrave;</b> <i
                                                        class="fa fa-chevron-down"></i></a>
                                            <ul class="nav dropdown-menu">
                                                {if is_set($active_dashboards.seduta)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/seduta'|ezurl(no)}">Sedute</a>
                                                    </li>
                                                {/if}
                                                {if is_set($active_dashboards.audizione)}
                                                    <li><a href="{'editorialstuff/dashboard/audizione'|ezurl(no)}">Audizioni</a>
                                                    </li>
                                                {/if}
                                                {if is_set($active_dashboards.designazione)}
                                                    <li><a href="{'editorialstuff/dashboard/designazione'|ezurl(no)}">Designazioni</a>
                                                    </li>
                                                {/if}
                                                {if is_set($active_dashboards.parere)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/parere'|ezurl(no)}">Pareri</a>
                                                    </li>
                                                {/if}
                                            </ul>
                                        </li>
                                        <li class="menu-item{if or($current_module|eq('editorialstuff/dashboard/areacollaborativa'),$current_module|eq('editorialstuff/dashboard/materia'),$current_module|eq('editorialstuff/dashboard/politico'),$current_module|eq('editorialstuff/dashboard/tecnico'),$current_module|eq('editorialstuff/dashboard/invitato'),$current_module|eq('editorialstuff/dashboard/referentelocale'))} current{/if}">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><b>Gestione</b>
                                                <i class="fa fa-chevron-down"></i></a>
                                            <ul class="nav dropdown-menu">
                                                {if is_set($active_dashboards.materia)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/materia'|ezurl(no)}">Materie</a>
                                                    </li>
                                                {/if}
                                                {if is_set($active_dashboards.politico)}
                                                    <li><a href="{'editorialstuff/dashboard/politico'|ezurl(no)}">Politici</a>
                                                    </li>
                                                {/if}
                                                {if is_set($active_dashboards.tecnico)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/tecnico'|ezurl(no)}">Tecnici</a>
                                                    </li>
                                                {/if}
                                                {if is_set($active_dashboards.seduta)}
                                                    <li><a href="{'editorialstuff/dashboard/invitato'|ezurl(no)}">Invitati</a>
                                                    </li>
                                                {/if}
                                                    <li><a href="{'consiglio/gettoni'|ezurl(no)}">Gettoni di presenza</a></li>
                                                {if is_set($active_dashboards.referentelocale)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/referentelocale'|ezurl(no)}">Referenti locali</a></li>
                                                {/if}
                                                {if is_set($active_dashboards.areacollaborativa)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/areacollaborativa'|ezurl(no)}">Aree collaborative</a></li>
                                                {/if}
                                                {if is_set($active_dashboards.organo)}
                                                    <li>
                                                        <a href="{'editorialstuff/dashboard/organo'|ezurl(no)}">Organi sociali</a></li>
                                                {/if}
                                            </ul>
                                        </li>
                                    {elseif fetch( 'user', 'has_access_to', hash( module, 'consiglio', function, 'use' ))}
                                        {if is_set($active_dashboards.seduta)}
                                            <li class="menu-item{if $current_module|eq('editorialstuff/dashboard/seduta')} current{/if}">
                                                <a href="{'editorialstuff/dashboard/seduta'|ezurl(no)}"><b>Archivio sedute</b></a></li>
                                        {/if}
                                        {if is_set($active_dashboards.audizione)}
                                            <li class="menu-item{if $current_module|eq('editorialstuff/dashboard/audizione')} current{/if}">
                                                <a href="{'editorialstuff/dashboard/audizione'|ezurl(no)}"><b>Archivio audizioni</b></a></li>
                                        {/if}
                                        {if is_set($active_dashboards.parere)}
                                            <li class="menu-item{if $current_module|eq('editorialstuff/dashboard/parere')} current{/if}">
                                                <a href="{'editorialstuff/dashboard/parere'|ezurl(no)}"><b>Archivio pareri</b></a></li>
                                        {/if}
                                    {/if}
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
