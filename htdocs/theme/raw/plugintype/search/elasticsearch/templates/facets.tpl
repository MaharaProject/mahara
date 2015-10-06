{*
    Links must contain the following parameters:
    - query
    - mainfacetterm
    - owner
    - sort
    - tagsonly
*}
<div id="totalresultsdisplay" class="totalresults view-description lead">
    {str tag=xsearchresultsfory section=search.elasticsearch arg1=$totalresults arg2='
    <a href="`$WWWROOT`search/elasticsearch/index.php?query=`$query`">`$query`</a>'}
</div>
<div class="elasticsearch-tabswrap">
    <ul class="in-page-tabs searchtab nav nav-tabs">
    {foreach from=$facets item=term}
        {if $term.count > 0}
            <li class="{if $term.term == $selected}current-tab active{/if}">
                <a href="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$term.term}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}"{if $term.term == $selected} class="current-tab"{/if}>{str tag=$term.display section=search.elasticsearch} ({$term.count})
                <span class="accessible-hidden sr-only">({str tag=tab}{if $term.term == $selected} {str tag=selected}{/if})</span>
                </a>
            </li>
        {else}
            <li>
                <a class="inactive">
                    {str tag=$term.display section=search.elasticsearch}
                    <span class="accessible-hidden sr-only">({str tag=tab} {str tag=disabled})</span>
                </a>
            </li>
        {/if}
    {/foreach}
    </ul>
</div>
