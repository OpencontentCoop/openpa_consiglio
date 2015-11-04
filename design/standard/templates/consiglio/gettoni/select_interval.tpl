<h3>Seleziona il periodo di riferimento</h3>
<table class="table">
{for $start to $end as $counter}
    <tr>
        <th>{$counter}</th>
        <td><a href="{concat('consiglio/gettoni/',$counter,'-1')|ezurl(no)}">Primo quadrimestre</a></td>
        <td><a href="{concat('consiglio/gettoni/',$counter,'-2')|ezurl(no)}">Secondo quadrimestre</a></td>
        <td><a href="{concat('consiglio/gettoni/',$counter,'-3')|ezurl(no)}">Terzo quadrimestre</a></td>
    </tr>
{/for}
</table>