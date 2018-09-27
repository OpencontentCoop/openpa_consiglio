{ezpagedata_set( show_path, false() )}
{ezpagedata_set( hide_header_searchbox, false() )}

{def $search=false()
	 $use_url_translation=false()}
{if $use_template_search}
    {set $page_limit=20}

    {def $activeFacetParameters = array()}
    {if ezhttp_hasvariable( 'activeFacets', 'get' )}
        {set $activeFacetParameters = ezhttp( 'activeFacets', 'get' )}
    {/if}

    {def $dateFilter=0}
    {if ezhttp_hasvariable( 'dateFilter', 'get' )}
        {set $dateFilter = ezhttp( 'dateFilter', 'get' )}
        {switch match=$dateFilter}
            {case match=1}
                {def $dateFilterLabel="Last day"|i18n("design/standard/content/search")}
            {/case}
            {case match=2}
                {def $dateFilterLabel="Last week"|i18n("design/standard/content/search")}
            {/case}
            {case match=3}
                {def $dateFilterLabel="Last month"|i18n("design/standard/content/search")}
            {/case}
            {case match=4}
                {def $dateFilterLabel="Last three months"|i18n("design/standard/content/search")}
            {/case}
            {case match=5}
                {def $dateFilterLabel="Last year"|i18n("design/standard/content/search")}
            {/case}
        {/switch}
    {/if}

    {def $filterParameters = fetch( 'ezfind', 'filterParameters' )
         $defaultSearchFacets = array(
                                       hash( 'field', 'class', 'name', 'Content type'|i18n("extension/ezfind/facets"), 'limit', 20 )
                                     )}
        
    {set $search=fetch( ezfind,search,
                        hash( 'query', $search_text,
                              'offset', $view_parameters.offset,
                              'limit', $page_limit,
                              'sort_by', hash( 'score', 'desc' ),
                              'facet', $defaultSearchFacets,
                              'filter', $filterParameters,
                              'publish_date', $dateFilter,
                              'subtree_array', $search_subtree_array,
                              'spell_check', array( false() ),
                              'search_result_clustering', hash( 'clustering', false() ) )
                             )}
    {set $search_result=$search['SearchResult']}
    {set $search_count=$search['SearchCount']}
    {def $search_extras=$search['SearchExtras']}
    {set $stop_word_array=$search['StopWordArray']}
    {set $search_data=$search}
{/if}
{def $baseURI=concat( '/content/search?SearchText=', $search_text )}

{* Build the URI suffix, used throughout all URL generations in this page *}
{def $uriSuffix = ''}
{foreach $activeFacetParameters as $facetField => $facetValue}
    {set $uriSuffix = concat( $uriSuffix, '&activeFacets[', $facetField, ']=', $facetValue )}
{/foreach}

{foreach $filterParameters as $name => $value}
    {set $uriSuffix = concat( $uriSuffix, '&filter[]=', $name, ':', $value )}
{/foreach}

{if gt( $dateFilter, 0 )}
    {set $uriSuffix = concat( $uriSuffix, '&dateFilter=', $dateFilter )}
{/if}

<form action="{"/content/search/"|ezurl(no)}" method="get">
  
  <div class="row">
    
	<div class="col-lg-8">
	  
	  {if $search_text|ne('')}
	  <h2>        
		  <span>{"Search"|i18n("design/ezwebin/content/search")}</span>
		  <small>{$search_text|wash}</small>
	  </h2>
	  {/if}
	
	  <div class="input-group">
		<input type="text" name="SearchText" class="form-control input-lg" value="{$search_text|wash}" />
		<span class="input-group-btn">
		  <button type="submit" name="SearchButton" class="btn btn-primary btn-lg" title="{'Search'|i18n('design/ezwebin/content/search')}">
			<span class="fa fa-search"></span>
		  </button>
		</span>
	  </div>

	  {if $search_extras.spellcheck_collation}
		   {def $spell_url=concat('/content/search/',$search_text|count_chars()
					|gt(0)
					|choose('',concat('?SearchText=',$search_extras.spellcheck_collation|urlencode)))
					|ezurl}
		<p class="help-block">
		  {'Spell check suggestion: did you mean'|i18n('design/ezfind/search')}
		  <strong>{concat("<a href=",$spell_url,">")}{$search_extras.spellcheck_collation}</a></strong>?
		</p>
	  {/if}
  
	  {if $stop_word_array}
		  <p class="help-block">
		  {"The following words were excluded from the search"|i18n("design/base")}:
		  {foreach $stop_word_array as $stopWord}
			  {$stopWord.word|wash}
			  {delimiter}, {/delimiter}
		  {/foreach}
		  </p>
	  {/if}
	  
	  {switch name=Sw match=$search_count}
		{case match=0}
		<h4 class="text-danger">
		  {'No results were found when searching for "%1".'|i18n("design/ezwebin/content/search",,array($search_text|wash))}
		  {if $search_extras.hasError}
			  {$search_extras.error|wash}
		  {/if}		  
		</h4>
		{/case}
		{case}
		<p class="text-success">
		  {if $search_text|ne('')}
			{'Search for "%1" returned %2 matches'|i18n("design/ezwebin/content/search",,array($search_text|wash,$search_count))}
		  {else}
			{$search_count} {"Results"|i18n("design/base")|downcase()}
		  {/if}
		</p>
	    {/case}
	  {/switch}	
    </div>
  </div>
  
  {if $search_count|gt(0)}
  <div class="row">
	<div class="col-sm-3 hidden-xs">
          {def $activeFacetsCount=0}
          <ul class="list-unstyled" id="active-facets-list">
          {foreach $defaultSearchFacets as $key => $defaultFacet}
              {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )}
                  {def $facetData=$search_extras.facet_fields.$key}

                  {foreach $facetData.nameList as $key2 => $facetName}
                      {if eq( $activeFacetParameters[concat( $defaultFacet['field'], ':', $defaultFacet['name'] )], $facetName )}
                          {set $activeFacetsCount=sum( $key, 1 )}
                          {def $suffix=$uriSuffix
										  |explode( concat( '&filter[]=', $facetData.queryLimit[$key2] ) )
										  |implode( '' )
										  |explode( concat( '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName ) )
										  |implode( '' )}
                          <li>
	                          <a class="btn btn-success btn-xs" href={concat( $baseURI, $suffix )|ezurl} title="{'Remove filter on '|i18n( 'design/ezwebin/content/search' )}'{$facetName|trim('"')|wash}'">
	                            <span class="remover">&times</span> 
	                            <span><strong>{$defaultFacet['name']}</strong>&nbsp;{$facetName|trim('"')|shorten(20)|wash}</span>
	                          </a>
                          </li>						  
                      {/if}
                  {/foreach}                  
              {/if}
          {/foreach}

          {* handle date filter here, manually for now. Should be a facet later on *}
          {if gt( $dateFilter, 0 )}
              <li>
                 {def $activeFacetsCount=$activeFacetsCount|inc}
                 {def $suffix=$uriSuffix|explode( concat( '&dateFilter=', $dateFilter ) )|implode( '' )}
                  <a class="btn btn-success btn-xs" href={concat( $baseURI, $suffix )|ezurl} title="{'Remove filter on '|i18n( 'design/ezwebin/content/search' )}'{$dateFilterLabel}'">
                  <span class="remover">&times</span>
                  <span><strong>{'Creation time'|i18n( 'extension/ezfind/facets' )}</strong>&nbsp;{$dateFilterLabel}</span>
                  </a> 
              </li>
          {/if}

          {*if ge( $activeFacetsCount, 2 )}
              <li>
                  <a class="btn btn-mini btn-info clear-all" href={$baseURI|ezurl} title="{'Clear all'|i18n( 'extension/ezfind/facets' )}">
                  <span class="remover">&times</span>
                  <span><strong>{'Clear all filters'|i18n( 'extension/ezfind/facets' )}</strong></span>                  
                  </a>
              </li>
          {/if*}
          </ul>

          <ul class="list-unstyled" id="facet-list">
          {foreach $defaultSearchFacets as $key => $defaultFacet}
              {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )|not}
                {def $facetData=$search_extras.facet_fields.$key}
                <li>                  
                    <h5>{$defaultFacet['name']}</h5>
                    <ul class="list-unstyled">
                      {foreach $facetData.nameList as $key2 => $facetName}
                          {if ne( $key2, '' )}
                          <li>
                              <span class="label label-primary facet-count">{$facetData.countList[$key2]}</span>							  
                              <a href={concat(
                                  $baseURI, '&filter[]=', $facetData.queryLimit[$key2],
                                  '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=',
                                  $facetName|rawurlencode,
                                  $uriSuffix )|ezurl}>
                                  <span class="label label-primary facet-name">{$facetName|shorten(20)|wash}</span>
                              </a> 
                          </li>
                          {/if}
                      {/foreach}
                    </ul>                    
                </li>
                {/if}
                {undef $facetData }              
          {/foreach}
          {* date filtering here. Using a simple filter for now. Should use the date facets later on *}
          {if eq( $dateFilter, 0 )}
              <li>
                  <h5>{'Creation time'|i18n( 'extension/ezfind/facets' )}</h5>
                  <ul class="list-unstyled">
                    <li>
                        <a href={concat( $baseURI, '&dateFilter=1', $uriSuffix )|ezurl}><span class="label label-primary facet-name">{"Last day"|i18n("design/standard/content/search")}</span></a>
                    </li>
                    <li>
                        <a href={concat( $baseURI, '&dateFilter=2', $uriSuffix )|ezurl}><span class="label label-primary facet-name">{"Last week"|i18n("design/standard/content/search")}</span></a>
                    </li>
                    <li>
                        <a href={concat( $baseURI, '&dateFilter=3', $uriSuffix )|ezurl}><span class="label label-primary facet-name">{"Last month"|i18n("design/standard/content/search")}</span></a>
                    </li>
                    <li>
                        <a href={concat( $baseURI, '&dateFilter=4', $uriSuffix )|ezurl}><span class="label label-primary facet-name">{"Last three months"|i18n("design/standard/content/search")}</span></a>
                    </li>
                    <li>
                        <a href={concat( $baseURI, '&dateFilter=5', $uriSuffix )|ezurl}><span class="label label-primary facet-name">{"Last year"|i18n("design/standard/content/search")}</span></a>
                    </li>
                  </ul>
              </li>
           {/if}
          </ul>

	</div>


	<div class="col-sm-9">
    {foreach $search_result as $result
             sequence array(bglight,bgdark) as $bgColor}
       {node_view_gui view='search_line' sequence=$bgColor use_url_translation=$use_url_translation content_node=$result}
    {/foreach}

    {include name=Navigator
             uri='design:navigator/google.tpl'
             page_uri='/content/search'
             page_uri_suffix=concat('?SearchText=',$search_text|urlencode,$search_timestamp|gt(0)|choose('',concat('&SearchTimestamp=',$search_timestamp)), $uriSuffix )
             item_count=$search_count
             view_parameters=$view_parameters
             item_limit=$page_limit}
	</div>
  </div>
  {/if}
</form>



{*
{ezscript_require( array('ezjsc::jquery', 'ezjsc::yui2', 'ezajax_autocomplete.js') )}
<script language="JavaScript" type="text/javascript">
jQuery('#mainarea-autocomplete-rs').css('width', jQuery('input#Search').width());
var autocomplete = new eZAJAXAutoComplete({ldelim}
    url: "{'ezjscore/call/ezfind::autocomplete'|ezurl('no')}",
    inputid: 'Search',
    containerid: 'mainarea-autocomplete-rs',
    minquerylength: {ezini( 'AutoCompleteSettings', 'MinQueryLength', 'ezfind.ini' )},
    resultlimit: {ezini( 'AutoCompleteSettings', 'Limit', 'ezfind.ini' )}
{rdelim});

{literal}
ezfSetBlock( 'ezfFacets', ezfGetCookie( 'ezfFacets' ) );
ezfSetBlock( 'ezfHelp', ezfGetCookie( 'ezfHelp' ) );
{/literal}
</script>
*}