<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="{'stylesheets/print-default.css'|ezdesign(no,full)}" />
</head>
<body>
<div id="header">
</div>
<div id="footer">
    {*<span id="pagenumber"/> di <span id="pagecount"/>*}
</div>
<div id="content" style="line-height: {$line_height}em;">
    <p><i>Trento, {$seduta.object.data_map.orario_conclusione_effettivo.content.timestamp|datetime( 'custom', '%j %F %Y' )}</i></p>

    <div id="destinatari" style="padding-left: 300px;">
        <p style="line-height: 1.2em;">
          {if eq($sesso, 'Maschio')}Egregio Signor{else}Gent.ma Signora{/if}<br />
          {$politico.name|wash()}<br />{attribute_view_gui attribute=$politico.data_map.ruolo2}<br />
          {attribute_view_gui attribute=$politico.data_map.indirizzo}
        </p>
    </div>
<br />
    <p>OGGETTO: Attestazione di presenza</p>
<br />
    <p class="indent">Il sottoscritto, {$segretario}, Segretario con funzione verbalizzante la seduta, su richiesta del soggetto indirizzo</p>
<br />
    <p style="text-align: center">ATTESTA</p>
<br />
    <p class="indent">che {if eq($sesso, 'Maschio')}il Signor{else}la Signora{/if} {$politico.name|wash()} ha partecipato alla seduta
        {if $organo|eq('Consiglio')}
            di Consiglio delle Autonomie Locali
        {else}
            di Giunta del Consiglio delle autonomie locali
        {/if}
        il giorno {$seduta.data_ora|datetime( 'custom', '%j %F %Y' )}, dalle ore {$checkin|datetime( 'custom', '%H:%i' )} alle ore {$checkout|datetime( 'custom', '%H:%i' )}.</p>

    <p class="indent">In fede</p>
<br />
    <p id="firma">
        Il Segretario verbalizzante<br />
        {$segretario}<br />
        {if $firma}
            <img src="{$firma}" width="100"/>
        {/if}
    </p>
</div>
</body>
</html>