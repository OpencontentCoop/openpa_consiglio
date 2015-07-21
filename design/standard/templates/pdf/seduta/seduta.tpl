<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body id="pdf-content" style="padding: 0;margin: 0; background-repeat: no-repeat; background-image: url('http://{ezini('SiteSettings','SiteURL')}/{"images/pdf/corner-content.jpg"|ezdesign(no)}'); background-position: right 560px;">

<div style="margin: 0 100px; line-height: {$line_height}em;height:643px;">

    <p><i>Trento, {$data|datetime( 'custom', '%j %F %Y' )}</i></p>

    <div id="destinatari">
        {if $organo|eq('Consiglio')}
            <p>Ai Signori Componenti il<br>Consiglio delle autonomie locali<br>- LL.SS.-</p>
        {else}
            <p>Ai Signori Componenti la<br>GIUNTA del Consiglio delle autonomie locali<br>- LL.SS.-
            </p>
        {/if}

        <p class="cc">e p.c. Egregio Signor<br>dott. Ugo Rossi<br>Presidente<br>della Provincia
            Autonoma di Trento<br>Piazza Dante, 15<br>38122 TRENTO</p>

        <p>Egregio Signor<br>dott. Bruno Dorigatti<br>Presidente<br>del Consiglio Provinciale<br>Via
            Manci, 27<br>38122 TRENTO</p>

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
        <strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y, alle ore %H:%i' )}</strong>
        <br>presso la sede<br>
        {if $luogo}{$luogo}{else}Sala Consiglio - Via Torre Verde, 23 - TRENTO{/if}
    </p>

    <p>per discutere il seguente ordine del giorno:</p>

    <ol>
        {def $index = 0}
        {foreach $odg as $p}
            {set $index = $index|inc()}
            <li>{$p.oggetto}{if $index|eq(count($odg))}.{else};{/if}</li>
        {/foreach}
    </ol>

    <p class="indent">Si forniscono quindi, in allegato, le informazioni ritenute opportune in merito ai procedimenti previsti per i diversi argomenti posti all'ordine del giorno.</p>
    <p class="indent">PregandoVi di fornire preventiva comunicazione, qualora impossibilitati a partecipare, con l'occasione si porgono cordiali saluti.</p>

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


        {foreach $odg as $k => $v}
            <div class="allegato">
                <p class="italic"><strong>PUNTO {$k} o.d.g.: {$v.oggetto}</strong></p>

                <p>
                    La documentazione di supporto alla discussione è pubblicata all'indirizzo
                    cal.tn.it dal giorno {$v.data_doc}.<br>
                    {if is_array($v.referente_politico)}
                        Il referente politico dell'argomento {if gt($v.referente_politico|count(), 1)}sono{else}è{/if} {$v.referente_politico|implode( ', ')}.
                        <br>
                    {/if}
                    {if is_array($v.referente_tecnico)}
                        Il referente tecnico dell'argomento {if gt($v.referente_tecnico|count(), 1)}sono{else}è{/if} {$v.referente_tecnico|implode( ', ')}.
                        <br>
                    {/if}
                    I Consiglieri interessati potranno rivolgersi ai referenti citati per ogni
                    informazione ritenuta opportuna.<br>
                    {if $v.consenti_osservazioni}
                        Considerazioni o osservazioni puntuali rispetto all'argomento dovranno essere inoltrate utilizzando il sistema Rice, accedendo all'indirizzo cal.tn.it, entro il giorno {$v.termine_osservazioni}.
                    {/if}
                </p>
            </div>
            {delimiter}<hr />{/delimiter}
        {/foreach}

    </div>
</div>


</body>
</html>