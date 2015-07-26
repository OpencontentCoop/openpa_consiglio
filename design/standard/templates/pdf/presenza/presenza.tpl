<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    {ezcss_load( array( 'pdf.css', 'print-default.css' ) )}
</head>
<body>
<div id="header">
    <img src="{'images/pdf/logo.jpg'|ezdesign(no, full)}" height="50" style="margin: 30px 30px 0 30px" />
</div>
<div id="footer">
    {*<span id="pagenumber"/> di <span id="pagecount"/>*}
</div>
<div id="content" style="line-height: {$line_height}em;">
    <p><i>Trento, {$data}</i></p>

    <p style="text-align: right">{$politico}{if $ruolo}<br />{$ruolo}{/if}{if $indirizzo}<br />{$indirizzo}{/if}</p>

    <p><strong>OGGETTO: Attestazione di presenza</strong></p>

    <p>Il sottoscritto, dott., Segretario con funzione verbalizzante la seduta, su richiesta del soggetto indirizzo</p>

    <p style="text-align: center"><strong>ATTESTA</strong></p>

    <p>Che il Signor {$politico} ha partecipato:<br />alla seduta di Consiglio delle Autonomie Locali il giorno {$giorno}, dalle ore {$dalle} alle ore {$alle}.</p>

    <p>In fede</p>

    {if $firmatario}
        <p id="firma" style="text-align: right">{$firmatario}<br />
            {if $firma}
                <img src="{$firma}" width="100" >
            {/if}
        </p>
    {/if}
</div>
</body>
</html>