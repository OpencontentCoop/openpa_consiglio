<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Calendario appuntamenti</h3>
    </div>
    <table class="table table-striped" id="eventi">
        <tbody></tbody>
    </table>
</div>

{literal}
<script id="tpl-eventi-results" type="text/x-jsrender">
{{for searchHits}}
<tr>
	<td>
	    <small class="label label-primary">
            {{:~formatDate(~i18n(data,'from_time'), 'D/MM/YYYY')}}
            {{if ~i18n(data, 'to_time')}}
                - {{:~formatDate(~i18n(data,'to_time'), 'D/MM/YYYY')}}
            {{/if}}
        </small>
	    <h5>
	        <strong>{{:~i18n(metadata.name)}}</strong>
	    </h5>
	    {{if ~i18n(data, 'text')}}
	        {{:~i18n(data, 'text')}}
	    {{/if}}
    </td>
</tr>
{{/for}}
{{if prevPageQuery || nextPageQuery}}
<tr>
	<td colspan="4">
		{{if prevPageQuery}}
			<div class="pull-left"><a href="#" id="eventi-prevPage" data-query="{{>prevPageQuery}}">Pagina precedente</a></div>
		{{/if}}
		{{if nextPageQuery }}
			<div class="pull-right"><a href="#" id="eventi-nextPage" data-query="{{>nextPageQuery}}">Pagina successiva</a></div>
		{{/if}}
	</td>
</tr>
{{/if}}
</script>
{/literal}

{ezscript_require(array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'jsrender.js',
    'dashboard_widget.js'
))}

<script type="text/javascript" language="javascript">
{literal}

$(document).ready(function () {
    $("#eventi").openConsiglioWidget({
        "mainQuery": 'classes event and calendar[] = [now,*] sort [from_time=>asc] limit 10',
        "resultTplSelector": '#tpl-eventi-results',
        "nextPageSelector": '#eventi-nextPage',
        "prevPageSelector": '#eventi-prevPage',
    });
});
{/literal}
</script>