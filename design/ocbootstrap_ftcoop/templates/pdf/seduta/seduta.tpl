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
        Trento, {$data|datetime( 'custom', '%j %F %Y' )}<br />
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

    <p class="indent">Gentili signore e signori,<br />
        ho il piacere di invitarvi alla riunione del {$organo} fissata per il giorno</p>

    <p id="data_luogo" style="text-align: center">
        <strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y alle ore %H:%i' )|downcase()} {if $ora_conclusione}(termine seduta alle ore {$ora_conclusione.timestamp|l10n(shorttime)}){/if}</strong>
        <br />presso<br />
        {$luogo}
    </p>

    <p>per discutere il seguente ordine del giorno:</p>

    <div class="fake_list_container">
        {def $index = 0}
        {foreach $odg as $p}
            {set $index = $index|inc()}
            <p class="fake_list"><span class="odg-number">{$p.numero}.</span> <span class="odg-object">{$p.oggetto}{if $index|eq(count($odg))}.{else};{/if}</span></p>
        {/foreach}
    </div>

    <p class="indent" style="text-align: justify">Si forniscono quindi, in allegato, le informazioni ritenute opportune in merito ai procedimenti previsti per i diversi argomenti posti all'ordine del giorno.</p>
    <p class="indent" style="text-align: justify">PregandoVi di fornire preventiva comunicazione, qualora impossibilitati a partecipare, con l'occasione si porgono cordiali saluti.</p>

    {if $firmatario}
        <p id="firma">
            {$descrizione_firmatario}<br />
            {$firmatario}<br/>
            {if $firma}
                <img src="{$firma}" width="100"/>
            {/if}
        </p>
    {/if}

    <div style="page-break-before:always;">
        <p class="italic"><strong>ALLEGATO</strong></p>

        <div class="fake_list_container">
            {foreach $odg as $k => $v}
                <div class="allegato">
                    <p class="italic"><strong>PUNTO {$k} o.d.g.: {$v.oggetto}</strong></p>
                    {if $v.documenti|gt(0)}
                    <p style="margin:0">La documentazione di supporto alla discussione è pubblicata all'indirizzo cal.tn.it{if $v.data_doc} dal giorno {$v.data_doc|datetime( 'custom', '%j %F %Y' )}{/if}.</p>
                    {/if}
                    {if is_array($v.referente_politico)}
                      <p style="margin:0">Il referente istituzionale dell'argomento {if gt($v.referente_politico|count(), 1)}sono{else}è{/if} {$v.referente_politico|implode( ', ')|trim()}.</p>
                    {/if}
                    {if is_array($v.referente_tecnico)}
                        <p style="margin:0">Il referente tecnico dell'argomento {if gt($v.referente_tecnico|count(), 1)}sono{else}è{/if} {$v.referente_tecnico|implode( ', ')|trim()}.</p>                            
                    {/if}
                    <p style="margin:0">I Componenti interessati potranno rivolgersi ai referenti citati per ogni informazione ritenuta opportuna.</p>
                    {if $v.consenti_osservazioni}
                        <p style="margin:0">Considerazioni o osservazioni puntuali rispetto all'argomento dovranno essere inoltrate utilizzando l'area riservata del sito della Federazione delle cooperative, entro il giorno {$v.termine_osservazioni}.</p>
                    {/if}                    
                </div>
                {delimiter}<hr />{/delimiter}
            {/foreach}
        </div>

    </div>
</div>
</body>
</html>
