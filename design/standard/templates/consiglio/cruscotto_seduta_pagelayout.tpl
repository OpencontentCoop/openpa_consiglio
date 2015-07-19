<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$site.http_equiv.Content-language|wash}">
<head>
    <title>{$title}</title>
    {ezcss_load( array( 'app.css','app_2.css','debug.css' ) )}
    {ezscript_load( array(
        'ezjsc::jquery',
        'ezjsc::jqueryUI',
        'ezjsc::jqueryio',
        'bootstrap/tab.js',
        'bootstrap/dropdown.js',
        'bootstrap/collapse.js',
        'bootstrap/affix.js',
        'bootstrap/alert.js',
        'bootstrap/button.js',
        'bootstrap/carousel.js',
        'bootstrap/modal.js',
        'bootstrap/tooltip.js',
        'bootstrap/popover.js',
        'bootstrap/scrollspy.js',
        'bootstrap/transition.js',
        'waypoints.min.js',
        'openpa_flat.js'
    ))}
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <script type="text/javascript" src={"javascript/respond.min.js"|ezdesign()}></script>
    <![endif]-->
</head>

<body>

<div id="page" style="min-height: 100%; padding: 20px;">
    <div class="container">
        {$module_result.content}
    </div>
</div>


<!--DEBUG_REPORT-->
</body>
</html>
