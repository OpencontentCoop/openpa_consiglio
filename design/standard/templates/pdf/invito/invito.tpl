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

    <p style="text-align: center"><strong>{$data_seduta} alle ore {$ora}</strong><br>presso la sede<br>{$luogo}</p>

    {if gt($punti|count(), 1)}
        <p>Per la trattazione dei seguenti punti posti all'ordine del giorno, concernenti:</p>
        {else}
        <p>Per la trattazione del punto {$n_punto} posto all'ordine del giorno, concernente:</p>
    {/if}

    <ul>
        {foreach $punti as $p}
            <li style="list-style-type:none;">{$p.n_punto}. {$p.oggetto}</li>
        {/foreach}
    </ul>

    <p>Confidando nella Sua partecipazione all'incontro, con l'occasione porgo distinti saluti.</p>

    {if $firmatario}
        <p id="firma" style="text-align: right">{$firmatario}<br><img src="http://localhost.openpa/extension/openpa_consiglio/design/standard/images/pdf/{$firma}" width="100" ></p>
    {/if}
</body>
</html>