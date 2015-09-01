{def $userAccount = fetch(user,current_user)}
<form action="{"user/edit/"|ezurl(no)}" method="post"
      name="Edit">

    <dl class="dl-horizontal">
        <dt>{"Username"|i18n("design/ocbootstrap/user/edit")}</dt>
        <dd>{$userAccount.login|wash}</dd>

        <dt>{"Email"|i18n("design/ocbootstrap/user/edit")}</dt>
        <dd>{$userAccount.email|wash(email)}</dd>

        <dt>{"Name"|i18n("design/ocbootstrap/user/edit")}</dt>
        <dd>{$userAccount.contentobject.name|wash}</dd>
    </dl>
    <input class="button" type="submit" name="EditButton"
           value="{'Edit profile'|i18n('design/ocbootstrap/user/edit')}"/>
    <input class="button" type="submit" name="ChangePasswordButton"
           value="{'Change password'|i18n('design/ocbootstrap/user/edit')}"/>

</form>
{undef $userAccount}