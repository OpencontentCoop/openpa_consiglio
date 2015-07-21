<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <script>{literal}
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
        {/literal}</script>
</head>
<body id="pdf-footer" onload="pagination()" style="padding: 10px;margin: 0;min-height: 100px">
    <div style="z-index:-1;background-repeat: no-repeat; position:absolute; bottom:20px; right:0; width:313px; height:100px; background-image: url('http://{ezini('SiteSettings','SiteURL')}/{"images/pdf/corner-footer.jpg"|ezdesign(no)}'); background-position: bottom center"></div>
</body>
</html>