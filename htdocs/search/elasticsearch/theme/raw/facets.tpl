{*
    Links must contain the following parameters:
    - query
    - mainfacetterm
    - owner
    - sort
    - tagsonly
*}
<div class="totalresults">{str tag=xsearchresultsfory section=search.elasticsearch arg1=$totalresults arg2='<a href="`$WWWROOT`search/elasticsearch/index.php?query=`$query`">`$query`</a>'}</div>
<div class="tabswrap"><ul class="in-page-tabs searchtab">
{foreach from=$facets item=term}
    {if $term.count > 0}
        <li{if $term.term == $selected} class="current-tab"{/if}>
        <a href="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$term.term}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}"{if $term.term == $selected} class="current-tab"{/if}>{str tag=$term.display section=search.elasticsearch} ({$term.count})</a></li>
    {else}
        <li><span class="inactive">{str tag=$term.display section=search.elasticsearch}</span></li>
    {/if}
{/foreach}
</ul></div>