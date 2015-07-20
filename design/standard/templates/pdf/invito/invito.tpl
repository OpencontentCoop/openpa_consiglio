<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body id="pdf-content" style="line-height: {$line_height}pt; background-repeat: no-repeat; height:643px; background-image: url('http://localhost.openpa/extension/openpa_consiglio/design/standard/images/pdf/corner-content.jpg'); background-attachment: fixed; background-position: bottom right">
    <div style="margin: 0 100px">
        <p><i>Trento, {$data}</i></p>

        <p style="text-align: right">{$invitato}{if $ruolo}<br>{$ruolo}{/if}{if $indirizzo}<br>{$indirizzo}{/if}</p>

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
            <p id="firma" style="text-align: right">{$firmatario}<br>
                {if $firma}
                    <img src="{$firma}" width="100" >
                {/if}
            </p>
        {/if}
    </div>
</body>
</html>