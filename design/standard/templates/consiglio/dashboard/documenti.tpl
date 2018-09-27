<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Documentazione</h3>
    </div>
    <table class="table table-striped" id="documenti">
        <tbody></tbody>
    </table>
</div>

{literal}
<script id="tpl-documenti-results" type="text/x-jsrender">
{{for searchHits}}
<tr>
	<td>
	    <h5>
	        <strong>{{:~i18n(metadata.name)}}</strong>
	    </h5>
	    {{if ~i18n(data, 'description')}}
	        {{:~i18n(data, 'description')}}
	    {{/if}}
	    {{if ~i18n(data, 'file')}}
	        <a class="label label-primary" href="{{:~i18n(data, 'file').url}}">Download</a>
	    {{/if}}
    </td>
</tr>
{{/for}}
{{if prevPageQuery || nextPageQuery}}
<tr>
	<td colspan="4">
		{{if prevPageQuery}}
			<div class="pull-left"><a href="#" id="documenti-prevPage" data-query="{{>prevPageQuery}}">Pagina precedente</a></div>
		{{/if}}
		{{if nextPageQuery }}
			<div class="pull-right"><a href="#" id="documenti-nextPage" data-query="{{>nextPageQuery}}">Pagina successiva</a></div>
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
    $("#documenti").openConsiglioWidget({
        "mainQuery": 'classes [file] sort [published=>desc] limit 10',
        "resultTplSelector": '#tpl-documenti-results',
        "nextPageSelector": '#documenti-nextPage',
        "prevPageSelector": '#documenti-prevPage',
    });
});
{/literal}
</script>