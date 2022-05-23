{*
    Links must contain the following parameters:
    - query
    - mainfacetterm
    - owner
    - sort
    - tagsonly
*}
<div id="totalresultsdisplay" class="totalresults view-description lead">
    {if $query|strlen > 0}
        {str tag=xsearchresultsfory section=search.elasticsearch7 arg1=$totalresults arg2='<a href="`$WWWROOT``$thispath`?query=`$query`">`$query`</a>'}
    {else}
        {str tag=xsearchresults section=search.elasticsearch7 arg1=$totalresults}
    {/if}
</div>
<div class="elasticsearch-tabswrap">
    <ul class="in-page-tabs searchtab nav nav-tabs">
    {foreach from=$facets item=term}
        {if $term.count > 0}
            <li class="{if $term.term == $selected}current-tab active{/if}">
                <a href="{$WWWROOT}{$thispath}?query={$query}&mainfacetterm={$term.term}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}#onsearch"{if $term.term == $selected} class="current-tab {if $term.term == $selected}active{/if}"{/if}>{str tag=$term.display section=search.elasticsearch7} ({$term.count})
                <span class="accessible-hidden visually-hidden">({str tag=tab}{if $term.term == $selected} {str tag=selected}{/if})</span>
                </a>
            </li>
        {else}
            <li>
                <a class="inactive">
                    {str tag=$term.display section=search.elasticsearch7}
                    <span class="accessible-hidden visually-hidden">({str tag=tab} {str tag=disabled})</span>
                </a>
            </li>
        {/if}
    {/foreach}
    </ul>
</div>
