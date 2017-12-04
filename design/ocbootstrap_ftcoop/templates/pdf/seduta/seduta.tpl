<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="{'stylesheets/print-default.css'|ezdesign(no,full)}" />
</head>
<body>
    <div id="header"></div>
    <div id="footer">
        <strong>Federazione Trentina della Cooperazione Società Cooperativa</strong><br />
        Via Segantini, 10 - 38122 Trento – Tel. +39 0461.898111 – Fax 0461.985431 – e-mail: ftcoop@ftcoop.it – ftcoop@pec.cooperazionetrentina.it<br />
        Iscrizione Registro Imprese TN, Cod. Fisc. E Part. IVA 00110640224 – Iscrizione Albo Nazionale Enti Cooperativi MU-CAL n. A157943.
    </div>
    
    <div id="content" style="line-height: {$line_height}em;">
        <p>
            Trento, {$data|datetime( 'custom', '%j %F %Y' )|downcase()}<br />
            Prot. n. {$protocollo}
        </p>

        <div id="destinatari">
            <p>Gentili Signore e Signori <br />
                <strong>Componenti il {$organo}</strong><br />
                della Federazione Trentina della Cooperazione
            </p>
        </div>

        <p id="oggetto">
            Convocazione {$organo}
        </p>

        <p>Gentili signore e signori,</p>

        <p class="indent">ho il piacere di invitarvi alla riunione del {$organo} fissata per il giorno</p>

        <p id="data_luogo" style="text-align: center">            
            <strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y' )|downcase()}</strong><br />
            <strong>{$data_seduta|datetime( 'custom', 'alle ore %H:%i' )|downcase()}</strong>
            {if $ora_conclusione}<br /><small>(termine seduta alle ore {$ora_conclusione.timestamp|l10n(shorttime)})</small>{/if}
        </p>
        <p style="text-align: center">                          
            presso {$luogo}
        </p>

        <p>per discutere e deliberare i seguenti temi all'ordine del giorno:</p>

        <div class="fake_list_container">
            {def $index = 0}
            {foreach $odg as $p}
                {set $index = $index|inc()}
                <p class="fake_list"><span class="odg-number">{$p.numero}.</span> <span class="odg-object">{$p.oggetto|trim()}{if $index|eq(count($odg))}.{else};{/if}</span></p>
            {/foreach}
        </div>

        <p class="indent">Nell'area del sito a voi riservata sono a disposizione le informazioni utili ad un preventivo esame dei temi all'ordine del giorno.</p>

        <p class="indent">Chiedendovi la cortesia di comunicarci preventivamente se sarete impossibilitati a partecipare, vi saluto cordialmente.</p>

        {if $firmatario}
            <p id="firma">
                {$descrizione_firmatario}<br/>
                {$firmatario}<br/>
                {if $firma}
                    <img src="{$firma}" width="100"/>
                {/if}
            </p>
        {/if}

    </div>
</body>
</html>
