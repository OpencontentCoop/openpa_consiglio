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
    <p><i>Trento, {$data|datetime( 'custom', '%j %F %Y' )}</i></p>

    <div id="destinatari">
        <p>{$invitato}{if $ruolo}<br />{$ruolo}{/if}{if $indirizzo}<br />{$indirizzo}{/if}</p>
    </div>

    <p id="oggetto">OGGETTO: convocazione seduta
        di {if $organo|eq('Giunta')}Giunta del Consiglio delle autonomie locali{else}{$organo}{/if}</p>

    <p class="indent">Con la presente ho il piacere di invitarla alla riunione
        di {if $organo|eq('Giunta')}Giunta del Consiglio delle autonomie locali{else}{$organo}{/if}
        ,</p>

    <p id="data_luogo" style="text-align: center">
        <strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y, alle ore %H:%i' )}</strong>
        <br />presso la sede<br />
        {if $luogo}{$luogo}{else}Sala Consiglio - Via Torre Verde, 23 - TRENTO{/if}
    </p>

    {if gt($punti|count(), 1)}
        <p>Per la trattazione dei seguenti punti posti all'ordine del giorno, concernenti:</p>
    {else}
        <p>Per la trattazione del punto {$punti[0].n_punto} posto all'ordine del giorno,
            concernente:</p>
    {/if}

    <div class="fake_list_container">
        {foreach $punti as $p}
            <p class="fake_list"><span>{$p.n_punto}.</span> {$p.oggetto}</p>
        {/foreach}
    </div>


    <p class="indent">Confidando nella Sua partecipazione all'incontro, con l'occasione porgo
        distinti saluti.</p>

    {if $firmatario}
        <p id="firma">
            Il Presidente<br />
            {$firmatario}<br />
            {if $firma}
                <img src="{$firma}" width="100"/>
            {/if}
        </p>
    {/if}
</div>
</body>
</html>