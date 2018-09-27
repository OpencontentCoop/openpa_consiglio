<div class="collapse navbar-collapse header-links">
<ul class="nav navbar-nav navbar-right">
  {if $current_user.is_logged_in}
    <li id="myprofile"><a href={"/user/edit/"|ezurl} title="{'My profile'|i18n('design/ocbootstrap/pagelayout')}">{'My profile'|i18n('design/ocbootstrap/pagelayout')} di <strong>{$current_user.contentobject.name|wash}</strong></a></li>
    <li id="logout"><a href={"/user/logout"|ezurl} title="{'Logout'|i18n('design/ocbootstrap/pagelayout')}">{'Logout'|i18n('design/ocbootstrap/pagelayout')}</a></li>  
  {else}
  	<li><a href={"/user/login/"|ezurl}>Utente anonimo</a></li>
  {/if}
</ul>
</div>

