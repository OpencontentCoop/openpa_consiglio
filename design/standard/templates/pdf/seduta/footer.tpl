<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <script>{literal}
        function pagination() {
            var vars = {};
            var x = document.location.search.substring(1).split('&');
            for (var i in x)      {
                var z = x[i].split('=', 2);
                vars[z[0]] = unescape(z[1]);      }
            var x = ['frompage','topage','page','webpage','section','subsection','subsubsection'];
            for (var i in x)      {          var y = document.getElementsByClassName(x[i]);
                for (var j = 0; j < y.length; ++j){
                    y[j].textContent = vars[x[i]];
                }
            }
        }
        {/literal}</script>
</head>
<body id="pdf-footer" onload="pagination()">
<!--
<span class="copyright">© Copyright MySite. All rights reserved.</span>
<span class="page"></span>-->

<div  style="background-repeat: no-repeat; height:101px; background-image: url('http://{ezini('SiteSettings','SiteURL')}/extension/openpa_consiglio/design/standard/images/pdf/corner-footer.jpg'); background-position: bottom right">
    <div style="padding-top: 30px">
        <hr style="margin: 0 40px; border: 0; height: 1px; background: #000;">
        <p style="text-align: center">
            <strong>
                * SI PRECISA CHE LA CONVOCAZIONE VIENE INVIATA PER CONOSCENZA A TUTTI I SINDACI<br>
                DEI COMUNI TRENTINI, AI SENSI DELL'ART.7, COMMA 3, DELLA L.P.7/2005
            </strong>
        </p>
    </div>
</div>

</body>
</html>
