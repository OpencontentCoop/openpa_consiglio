<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body id="pdf-content" style="line-height: {$line_height}pt;">

    <p><i>Trento, {$data}</i></p>

    <p style="text-align: right">{$invitato}<br>{$ruolo}<br>{$indirizzo}</p>

    <p id="oggetto"><strong>OGGETTO: convocazione seduta di {$organo}</strong></p>

    <p>Con la presente ho il piacere di invitarla alla riunione di {$organo},</p>

    <p style="text-align: center"><strong>{$data_seduta}</strong><br>presso la sede<br>[indicare sede]</p>

    <p>Per la trattazione del punto {$n_punto} posto all'ordine del giorno, concernente:</p>

    <p>{$n_punto} {$oggetto}</p>

    <p>Confidando nella Sua partecipazione all'incontro, con l'occasione porgo distinti saluti.</p>

    {if $firmatario}
        <p id="firma" style="text-align: right">{$firmatario}<br><img src="http://localhost.openpa/extension/openpa_consiglio/design/standard/images/pdf/{$firma}" width="100" ></p>
    {/if}
</body>
</html>