<form action={concat($module.functions.edit.uri,"/",$userID)|ezurl} method="post" name="Edit">

<h1 class="long">{"User profile"|i18n("design/ocbootstrap/user/edit")}</h1>

<div class="user-edit row">

  <div class="col-md-6">      

    <div class="block">
      <label>{"Username"|i18n("design/ocbootstrap/user/edit")}</label><div class="labelbreak"></div>
      <p class="box">{$userAccount.login|wash}</p>
    </div>

    <div class="block">
      <label>{"Email"|i18n("design/ocbootstrap/user/edit")}</label><div class="labelbreak"></div>
      <p class="box">{$userAccount.email|wash(email)}</p>
    </div>

    <div class="block">
      <label>{"Name"|i18n("design/ocbootstrap/user/edit")}</label><div class="labelbreak"></div>
      <p class="box">{$userAccount.contentobject.name|wash}</p>
    </div>

  </div>

  <div class="col-md-6">
    <input class="button" type="submit" name="EditButton" value="{'Edit profile'|i18n('design/ocbootstrap/user/edit')}" />
    <input class="button" type="submit" name="ChangePasswordButton" value="{'Change password'|i18n('design/ocbootstrap/user/edit')}" />
  </div>

</div>

</form>
