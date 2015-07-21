<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body id="pdf-content" style="line-height: {$line_height}pt; background-repeat: no-repeat; height:643px; background-image: url('http://{ezini('SiteSettings','SiteURL')}/extension/openpa_consiglio/design/standard/images/pdf/corner-content.jpg'); background-attachment: fixed; background-position: right bottom">

    <div style="margin: 0 100px">

        <p><i>Trento, {$data|datetime( 'custom', '%j %F %Y' )}</i></p>

        <div id="destinatari">
            <p style="text-align: right">Ai Signori Componenti il<br>Consiglio delle autonomie locali<br>- LL.SS.-</p>

            <p style="text-align: right">e p.c.<br>Egregio Signor<br>dott. Ugo Rossi<br>Presidente<br>della Provincia Autonoma di Trento<br>Piazza Dante, 15<br>38122 TRENTO</p>

            <p style="text-align: right">Egregio Signor<br>dott. Bruno Dorigatti<br>Presidente<br>del Consiglio Provinciale<br>Via Manci, 27<br>38122 TRENTO</p>

            <p style="text-align: right">A tutti i Comuni *</p>

            <p id="oggetto"><strong>OGGETTO: convocazione seduta di {$organo}</strong></p>
        </div>

        <p>Con la presente si informa che la seduta di {$organo} è fissata per il giorno</p>

        <p style="text-align: center"><strong>{$data_seduta|datetime( 'custom', '%l %j %F %Y, alle ore %H:%i' )}</strong><br>presso la sede<br>{$luogo}</p>

        <p>Per discutere il seguente ordine del giorno:</p>

        <ol>
            {foreach $odg as $p}
                <li>{$p.oggetto}</li>
            {/foreach}
        </ol>

        <div style="page-break-before:always;">
            <p><strong>ALLEGATO</strong></p>

            <ol>
                {foreach $odg as $k => $v}
                    <li>
                        <p><strong>PUNTO {$k} o.d.g.: {$v.oggetto}</strong></p>
                        <p>
                            La documentazione di supporto alla discussione è pubblicata all'indirizzo cal.tn.it dal giorno {$v.data_doc}.<br>
                            {if is_array($v.referente_politico)}
                            Il referente politico dell'argomento {if gt($v.referente_politico|count(), 1)}sono{else}è{/if} {$v.referente_politico|implode( ', ')}.<br>
                            {/if}
                            {if is_array($v.referente_tecnico)}
                            Il referente tecnico dell'argomento {if gt($v.referente_tecnico|count(), 1)}sono{else}è{/if} {$v.referente_tecnico|implode( ', ')}.<br>
                            {/if}
                            I Consiglieri interessati potranno rivolgersi ai referenti citati per ogni informazione ritenuta opportuna.<br>
                            {if $v.consenti_osservazioni}
                                Considerazioni o osservazioni puntuali rispetto all'argomento dovranno essere inoltrate utilizzando il sistema Rice, accedendo all'indirizzo cal.tn.it, entro il giorno {$v.termine_osservazioni}.
                            {/if}
                        </p>
                    </li>
                {/foreach}
            </ol>
        </div>

        {if $firmatario}
            <p id="firma" style="text-align: right">{$firmatario}<br>
                {if $firma}
                    <img src="{$firma}" width="100" />
                {/if}
            </p>
        {/if}
    </div>
</body>
</html>