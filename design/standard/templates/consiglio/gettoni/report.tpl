<table class="table">
    {foreach $sedute as $seduta}
        <tr>
            <td>{$seduta.object.name|wash()}</td>
        </tr>
    {/foreach}
</table>