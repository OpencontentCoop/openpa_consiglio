<<<<<<< HEAD
Template not defined
=======
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
    <p>
        <i>Trento, {currentdate()|datetime( 'custom', '%j %F %Y' )}</i><br />
        {if $protocollo}<i>Prot. n. {$protocollo}</i>{/if}
    </p>

    <div id="destinatari">
        <p>
        {if $sesso}
            {if eq($sesso, 'Maschio')}Egregio Signor{elseif eq($sesso, 'Femmina')}Gent.ma Signora{else}Spettabile{/if}<br />
        {/if}
        {$invitato}{if $ruolo}<br />{$ruolo}{/if}<br />
        {if $indirizzo}{$indirizzo}{/if}</p>
    </div>

    <p id="oggetto">OGGETTO: convocazione seduta {if $organo|eq('Giunta')}della Giunta{/if} del Consiglio delle autonomie locali</p>

    <p class="indent">Con la presente ho il piacere di invitarla alla riunione
        {if $organo|eq('Giunta')}della Giunta{/if} del Consiglio delle autonomie locali,</p>

    <p id="data_luogo" style="text-align: center">
        <strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y' )|downcase()} alle ore {$ora_invito}</strong>
        <br />presso<br />
        {if $luogo}{$luogo}{else}Sala Consiglio - Via Torre Verde, 23 - TRENTO{/if}
    </p>

    {if gt($punti|count(), 1)}
        <p>Per la trattazione dei seguenti punti posti all'ordine del giorno, concernenti:</p>
    {else}
        <p>per la trattazione del punto {$punti[0].n_punto} posto all'ordine del giorno,
            concernente:</p>
    {/if}

    <div class="fake_list_container">
        {def $count = 1}
        {foreach $punti as $p}
            <p class="fake_list"><span class="odg-number">{$p.n_punto}.</span> <span class="odg-object">{$p.oggetto}{if $count|eq(count($punti))}.{else};{/if}</span></p>
            {set $count = $count|inc()}
        {/foreach}
    </div>


    <p class="indent">Confidando nella Sua partecipazione all'incontro, con l'occasione porgo distinti saluti.</p>

    {if $firmatario}
        <p id="firma">
            {$descrizione_firmatario}<br />
            {$firmatario}<br />
            {if $firma}
                <img src="{$firma}" width="100"/>
            {/if}
        </p>
    {/if}
</div>
</body>
</html>
>>>>>>> 3c3d14163dd47500dcd4d3c4e55dbe04b6e9aa26
