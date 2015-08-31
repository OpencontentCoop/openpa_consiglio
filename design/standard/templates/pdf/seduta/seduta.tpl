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
    <div class="nota">
        <hr style="margin: 0; border: 0; height: 4px; background: #000;" />
        <hr style="margin: 0; border: 0; height: 4px; background: #fff;" />
        <hr style="margin: 0; border: 0; height: 1px; background: #000;" />
        <p style="text-align:justify; margin: 0;font-size: .92em">
            <strong>
                * SI PRECISA CHE LA CONVOCAZIONE VIENE INVIATA PER CONOSCENZA A TUTTI I SINDACI DEI COMUNI TRENTINI, AI SENSI DELL'ART.7, COMMA 3, DELLA L.P.7/2005
            </strong>
        </p>
    </div>
    {*<span id="pagenumber"/> di <span id="pagecount"/>*}
</div>
<div id="content" style="line-height: {$line_height}em;">

    <p>
        <i>Trento, {$data|datetime( 'custom', '%j %F %Y' )}</i><br />
        <i>Prot. n. {$protocollo}</i>
    </p>

    <div id="destinatari">
        {if $organo|eq('Consiglio')}
            <p>Ai Signori Componenti il<br />Consiglio delle autonomie locali<br />- LL.SS.-</p>
        {else}
            <p>Ai Signori Componenti la<br />GIUNTA del Consiglio delle autonomie locali<br />- LL.SS.-
            </p>
        {/if}

        <p class="cc">e p.c. Egregio Signor<br />dott. Ugo Rossi<br />Presidente<br />della Provincia
            Autonoma di Trento<br />Piazza Dante, 15<br />38122 TRENTO</p>

        <p>Egregio Signor<br />dott. Bruno Dorigatti<br />Presidente<br />del Consiglio Provinciale<br />Via
            Manci, 27<br />38122 TRENTO</p>

        <p>A tutti i Comuni *</p>
    </div>

    <p id="oggetto">
        {if $organo|eq('Consiglio')}
            OGGETTO: convocazione seduta di {$organo}
        {else}
            OGGETTO: convocazione seduta di Giunta del Consiglio delle autonomie locali
        {/if}
    </p>

    <p class="indent">Con la presente si informa che la seduta
        di {if $organo|eq('Giunta')}Giunta del Consiglio delle autonomie locali{else}{$organo}{/if}
        è fissata per il giorno</p>

    <p id="data_luogo" style="text-align: center">
        <strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y alle ore %H:%i' )|downcase()}</strong>
        <br />presso<br />
        {if $luogo}{$luogo}{else}Sala Consiglio - Via Torre Verde, 23 - TRENTO{/if}
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
            Il Presidente<br/>
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
                      <p style="margin:0">Il referente politico dell'argomento {if gt($v.referente_politico|count(), 1)}sono{else}è{/if} {$v.referente_politico|implode( ', ')}.</p>
                    {/if}
                    {if is_array($v.referente_tecnico)}
                        <p style="margin:0">Il referente tecnico dell'argomento {if gt($v.referente_tecnico|count(), 1)}sono{else}è{/if} {$v.referente_tecnico|implode( ', ')}.</p>                            
                    {/if}
                    <p style="margin:0">I Consiglieri interessati potranno rivolgersi ai referenti citati per ogni informazione ritenuta opportuna.</p>
                    {if $v.consenti_osservazioni}
                        <p style="margin:0">Considerazioni o osservazioni puntuali rispetto all'argomento dovranno essere inoltrate utilizzando il sistema Comunweb, accedendo all'indirizzo cal.tn.it, entro il giorno {$v.termine_osservazioni}.</p>
                    {/if}                    
                </div>
                {delimiter}<hr />{/delimiter}
            {/foreach}
        </div>

    </div>
</div>
</body>
</html>