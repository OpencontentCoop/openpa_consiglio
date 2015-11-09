<h3>Seleziona il periodo di riferimento</h3>
<table class="table table-bordered">
{for $start to $end as $counter}
    <tr>
        <th rowspan="2" style="vertical-align: middle;text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-0')|ezurl(no)}">{$counter}</a></th>
        <td colspan="4" style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-I')|ezurl(no)}">Primo quadrimestre</a></td>
        <td colspan="4" style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-II')|ezurl(no)}">Secondo quadrimestre</a></td>
        <td colspan="4" style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-III')|ezurl(no)}">Terzo quadrimestre</a></td>
    </tr>
	<tr>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-1')|ezurl(no)}">Gennaio</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-2')|ezurl(no)}">Febbraio</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-3')|ezurl(no)}">Marzo</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-4')|ezurl(no)}">Aprile</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-5')|ezurl(no)}">Maggio</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-6')|ezurl(no)}">Giugno</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-7')|ezurl(no)}">Luglio</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-8')|ezurl(no)}">Agosto</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-9')|ezurl(no)}">Settembre</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-10')|ezurl(no)}">Ottobre</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-11')|ezurl(no)}">Novembre</a></td>
	  <td style="text-align: center"><a href="{concat('consiglio/gettoni/',$counter,'-12')|ezurl(no)}">Dicembre</a></td>
	</tr>
{/for}
</table>