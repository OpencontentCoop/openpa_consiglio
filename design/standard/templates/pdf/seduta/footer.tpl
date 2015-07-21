<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    {literal}<script>
        function pagination() {
            var vars = {};
            var x = document.location.search.substring(1).split('&');
            for (var i in x) {
                var z = x[i].split('=', 2);
                vars[z[0]] = unescape(z[1]);
            }
            var x = ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection'];
            for (var i in x) {
                var y = document.getElementsByClassName(x[i]);
                for (var j = 0; j < y.length; ++j) {
                    y[j].textContent = vars[x[i]];
                }
            }
        }
        </script>{/literal}
</head>
<body id="pdf-footer" onload="pagination()" style="padding: 10px;margin: 0;min-height: 100px">

<div>
    <hr style="margin: 0 100px; border: 0; height: 4px; background: #000;">
    <hr style="margin: 0 100px; border: 0; height: 4px; background: #fff;">
    <hr style="margin: 0 100px; border: 0; height: 1px; background: #000;">
    <p style="text-align: center">
        <strong>
            * SI PRECISA CHE LA CONVOCAZIONE VIENE INVIATA PER CONOSCENZA A TUTTI I SINDACI<br>
            DEI COMUNI TRENTINI, AI SENSI DELL'ART.7, COMMA 3, DELLA L.P.7/2005
        </strong>
    </p>
</div>

<div style="z-index:-1;background-repeat: no-repeat; position:absolute; bottom:20px; right:0; width:313px; height:100px; background-image: url('http://{ezini('SiteSettings','SiteURL')}/{"images/pdf/corner-footer.jpg"|ezdesign(no)}'); background-position: bottom center"></div>

</body>
</html>
