{ezscript_require( array( 'ezjsc::jquery', 'highcharts_presenze_utente.js', 'highcharts.js' ) )}
<div class="presenze_utente_pie_container"
     data-title="Presenze 2015"
     data-userid="{fetch(user,current_user).contentobject_id}"
     data-url="{concat('openpa/data/percentuale_presenze_seduta/?uid=',fetch(user,current_user).contentobject_id)|ezurl(no)}"
     style="min-width: 310px; height: 200px; max-width: 600px; margin: 0 auto"></div>

