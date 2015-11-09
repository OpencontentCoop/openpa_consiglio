<h3>Seleziona il periodo di riferimento</h3>
<table class="table">
{for $start to $end as $counter}
    <tr>
        <th><a href="{concat('consiglio/gettoni/',$counter,'-0')|ezurl(no)}">{$counter}</a></th>
        <td><a href="{concat('consiglio/gettoni/',$counter,'-I')|ezurl(no)}">Primo quadrimestre</a></td>
        <td><a href="{concat('consiglio/gettoni/',$counter,'-II')|ezurl(no)}">Secondo quadrimestre</a></td>
        <td><a href="{concat('consiglio/gettoni/',$counter,'-III')|ezurl(no)}">Terzo quadrimestre</a></td>
    </tr>
{/for}
</table>