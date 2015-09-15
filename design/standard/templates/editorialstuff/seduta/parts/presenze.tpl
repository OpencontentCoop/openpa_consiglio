{ezscript_require( array( 'dhtmlxgantt.js' ) )}
{ezcss_require( array( 'dhtmlxgantt.css' ) )}


{if $post.current_state.identifier|eq( 'closed' )}
<h3>Attestati di presenza</h3>
<table class="table">
    {foreach $post.partecipanti as $partecipante}
        <tr>
            <td>{content_view_gui content_object=$partecipante.object view="politico_line"}</td>
            <td>
                <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post" class="form-horizontal">
                        <input type="hidden" name="ActionIdentifier" value="GetAttestatoPresenza" />
                        <input type="hidden" name="ActionParameters[presente]" value="{$partecipante.object_id}" />
                        <button class="btn btn-success btn-md" type="submit" name="GetAttestatoPresenza"><i class=\"fa fa-download\"></i> Stampa attestato</button>
                </form>
            </td>
        </tr>
    {/foreach}
</table>
{/if}
<div id="logs-partecipanti">
{foreach $post.partecipanti as $partecipante}
  <div id="logs-{$partecipante.object_id}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="logdLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						  aria-hidden="true">&times;</span></button>
			  <h4 class="modal-title">Rilevazioni {$partecipante.object.name|wash()} ({$partecipante.object_id})</h4>
		  </div>              
			<div class="modal-body">
			<table class="table table-striped">
			  <thead>
				<tr>
				  <th>Id</th>
				  <th>InOut</th>
				  <th>IsIn</th>
				  <th>Label</th>
				  <th>Time</th>
				</tr>
			  </thead>
			  <tbody>                      
			  </tbody>
			</table>
		  </div>
		</div>
	  </div>
  </div>
{/foreach}
</div>

{if $post.current_state.identifier|eq( 'in_progress' )}
<input id="start_stop" type="button" class="btn btn-xs" value="Live Data" style="dispaly:none"/>
{/if}
<div id="gantt_here" style='width:100%; height:1100px;'></div>
<script type="text/javascript">
    
    var ganttLoaded = false;
    
    function loadGantt(){ldelim}        
        if (!ganttLoaded) {ldelim}
          gantt.config.columns = [{ldelim}name:"text", label:"Nome", tree:true, width:'*' {rdelim}];
          gantt.config.initial_scroll = false;
          gantt.config.details_on_dblclick = false;
          gantt.config.drag_progress = false;
          gantt.config.readonly = true;
          gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
          gantt.config.scale_unit = "hour";
          gantt.config.step = 1;
          gantt.config.date_scale = "%H";
          gantt.config.min_column_width = 30;
          gantt.config.duration_unit = "minute";
          gantt.config.duration_step = 60;
          gantt.config.scale_height = 75;
  
          gantt.config.subscales = [
              {ldelim}unit:"minute", step:15, date : "%i"{rdelim}
          ];
  
          gantt.init("gantt_here");        
          gantt.templates.task_class = function(start, end, task){ldelim}
              if (task.values)
                  return "complex_gantt_bar";
          {rdelim};
          gantt.templates.task_text = function(start, end, task){ldelim}
              var returnData = '';
              var background = '';
              if (!task.values) return task.text;
              if (task.values.length == 0) {ldelim}
                returnData += "<div class='gantt_task_line' style='border:none;position:relative;float:left;width:100%;background:#fff'><span style='visibility:hidden'>0</span></div>";;
              {rdelim}else{ldelim}
                for (index = 0; index < task.values.length; ++index) {ldelim}
                    if ( task.values[index][0] == 0) {ldelim}
                        background = 'background:#eee';
                    {rdelim}else{ldelim}
                        background = 'background:#5cb85c';
                    {rdelim}
                    var width = task.values[index][1];
                    returnData += "<div class='gantt_task_line' style='border:none;position:relative;float:left;width:"+(30*width)+"px;"+background+"'><span style='visibility:hidden'>0</span></div>";
                {rdelim}
                for (index = 0; index < task.detections.length; ++index) {ldelim}                  
                  $('*[data-logid="'+task.detections[index].id+'"]').remove();
                  var logRow = $('<tr data-logid="'+task.detections[index].id+'"><td>'+task.detections[index].id+'</td><td>'+task.detections[index].in_out+'</td><td>'+task.detections[index].is_in+'</td><td>'+task.detections[index].label+'</td><td>'+task.detections[index].time+'</td>');
                  $('#logs-'+task.id+' tbody').append(logRow);
                {rdelim}
              {rdelim}
              return returnData;
          {rdelim};
          gantt.attachEvent("onTaskRowClick", function(id,row){ldelim}
            $('#logs-'+id).modal();
          {rdelim});
          var twentyMinutesLater = new Date();
          twentyMinutesLater.setMinutes(twentyMinutesLater.getMinutes() + 20);
          gantt.showDate(twentyMinutesLater);
          ganttLoaded = true;
        {rdelim}
        $('#logs-partecipanti tbody').empty();
        gantt.load({concat('/openpa/data/timeline_presenze_seduta?seduta=',$post.object_id)|ezurl()});
    {rdelim}    
    
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {ldelim}loadGantt(){rdelim});
    {if $post.current_state.identifier|eq( 'in_progress' )}        
        $(function() {ldelim}
          var timer = null, 
              interval = 5000;
      
          $("#start_stop").show().click(function() {ldelim}                        
            if (timer == null) {ldelim}
              loadGantt();
              $("#start_stop").addClass('btn-success');
              timer = setInterval(function () {ldelim}
                  loadGantt();
              {rdelim}, interval);
            {rdelim}else{ldelim}
              $("#start_stop").removeClass('btn-success');
              clearInterval(timer);
              timer = null
            {rdelim}  
          {rdelim});
        });
    {/if}    
</script>
<style>{literal}
    .complex_gantt_bar{
        background: transparent;
        border:none;
    }
    .complex_gantt_bar .gantt_task_progress{
        display:none;
    }
{/literal}</style>

{*
{ezscript_require( array( 'ezjsc::jquery', 'jQuery.Gantt/jquery.fn.gantt.js' ) )}
{ezcss_require( array( 'jQuery.Gantt.css' ) )}
<div class="gantt" data-gant_source={$node.node_id}></div>
<script>
    {literal}
    $(function() {
        "use strict";
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $(".gantt").gantt({
                source: {/literal}{concat('/openpa/data/timeline_presenze_seduta?seduta=',$post.object_id)|ezurl()}{literal},
                navigate: "scroll",
                scale: "hours",
                minScale: "hours",
                maxScale: "hours",
                itemsPerPage: 200,
                months: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
                dow: ["Do", "Lu", "Ma", "Me", "Gi", "Ve", "Sa"],
                waitText: "Attendere per favore..."
            });
        });
    });
    {/literal}
</script>
*}