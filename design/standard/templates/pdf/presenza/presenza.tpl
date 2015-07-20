<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body id="pdf-content" style="line-height: {$line_height}pt; height:643px; background-image: url('http://localhost.openpa/extension/openpa_consiglio/design/standard/images/pdf/corner-content.jpg'); background-attachment: fixed; background-position: bottom right">
    <div style="margin: 0 100px">
        <p><i>Trento, {$data}</i></p>

        <p style="text-align: right">{$politico}{if $ruolo}<br>{$ruolo}{/if}{if $indirizzo}<br>{$indirizzo}{/if}</p>

        <p><strong>OGGETTO: Attestazione di presenza</strong></p>

        <p>Il sottoscritto, dott., Segretario con funzione verbalizzante la seduta, su richiesta del soggetto indirizzo</p>

        <p style="text-align: center"><strong>ATTESTA</strong></p>

        <p>Che il Signor {$politico} ha partecipato:<br>alla seduta di Consiglio delle Autonomie Locali il giorno {$giorno}, dalle ore {$dalle} alle ore {$alle}.</p>

        <p>In fede</p>

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