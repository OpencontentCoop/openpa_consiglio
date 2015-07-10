{ezscript_require( array( 'dhtmlxgantt.js' ) )}
{ezcss_require( array( 'dhtmlxgantt.css' ) )}

<div id="gantt_here" style='width:100%; height:800px;'></div>
<script type="text/javascript">
    function loadGantt(){ldelim}
        gantt.config.columns = [{ldelim}name:"text", label:"Nome", tree:true, width:'*' {rdelim}];
        gantt.config.details_on_dblclick = false;
        gantt.config.drag_progress = false;
        gantt.config.drag_move = false;
        gantt.config.drag_resize = false;
        gantt.config.date_grid = "%H:%i";
        gantt.config.scale_unit = "hour";
        gantt.config.duration_unit = "minute";
        gantt.config.date_scale = "%H:%i";
        gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
        gantt.init("gantt_here");
        gantt.templates.task_class = function(start, end, task){ldelim}
            if (task.values)
                return "complex_gantt_bar";
        {rdelim};
        gantt.templates.task_text = function(start, end, task){ldelim}
            if (!task.values) return task.text;
            var lw = Math.round( task.splitStart * 100 / task.duration );
            var rw = Math.round( 100 - task.splitEnd * 100 / task.duration );
            var left = "<div class='gantt_task_line' style='left:0px; width:"+lw+"%;'>" + task.text + "</div>";
            var right = "<div class='gantt_task_line' style='right:0px; width:"+rw+"%'>" + task.text + "</div>";
            return right + left;
        {rdelim};
        {*gantt.load({concat('/openpa/data/timeline_presenze_seduta?seduta=',$post.object_id)|ezurl()});*}
        gantt.load({concat('javascript/data.json')|ezdesign()});
    {rdelim}
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {ldelim}loadGantt(){rdelim});
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