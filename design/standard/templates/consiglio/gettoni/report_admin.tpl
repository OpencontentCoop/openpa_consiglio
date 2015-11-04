<form>
    <h1>{$politico.object.name|wash()}</h1>

    <table class="table">
        <tr>
            <th>Convocazione</th>
            <th>Sede</th>
            <th>Km</th>
            <th>Spese</th>
        </tr>
        {foreach $sedute as $seduta}
            <tr>
                <td>{$seduta.object.name|wash()}</td>
                <td>{attribute_view_gui attribute=$seduta.object.data_map.luogo}</td>
                <td><input class="form-control" type="text" value="" name="Km[{$seduta.object.id}]"/></td>
                <td></td>
            </tr>
        {/foreach}
    </table>

    <h2>Informazioni</h2>

    <table class="table">
        <tr>
            <th><label for="iban">Coordinate Bancarie (codice IBAN)</label></th>
            <td>
                <input id="iban" class="form-control" type="text" value="" name="Iban"/>
            </td>
        </tr>
        <tr>
            <th><label for="trattenute">Applicare trattenuta</label></th>
            <td>
                <input id="trattenute" class="form-control" type="text" value="" name="Trattenute"/>
            </td>
        </tr>
    </table>
</form>