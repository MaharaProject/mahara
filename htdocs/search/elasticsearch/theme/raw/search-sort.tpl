{*
    Links must contain the following parameters:
    - query
    - mainfacetterm
    - secfacetterm
    - owner
    - sort
    - license
    - tagsonly
*}
<div id="universalsearch-results-sort">
    <input type="hidden" id="search-filter-sort-url" value="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$selected}&secfacetterm={$contentfilterselected}&license={$license}&owner={$owner}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}" />
    <label for="search-filter-sort">
        {str tag=sortby section=search.elasticsearch}:
    </label>
    <select id="search-filter-sort">
            <option value="ctime_asc" {if ($sort == 'ctime_asc')}selected{/if}>{str tag=dateoldestfirst section=search.elasticsearch}</option>
            <option value="ctime_desc" {if ($sort == 'ctime_desc')}selected{/if}>{str tag=daterecentfirst section=search.elasticsearch}</option>
            <option value="score" {if ($sort == 'score')}selected{/if}>{str tag=relevance section=search.elasticsearch}</option>
            <option value="sort_asc" {if ($sort == 'sort_asc')}selected{/if}>{str tag=atoz section=search.elasticsearch}</option>
            <option value="sort_desc" {if ($sort == 'sort_desc')}selected{/if}>{str tag=ztoa section=search.elasticsearch}</option>
    </select>
</div>