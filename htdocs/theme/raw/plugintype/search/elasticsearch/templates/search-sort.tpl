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
<div id="universalsearch-results-sort" class="search-results-sort pieform with-label-widthauto form-condensed">
    <input type="hidden" id="search-filter-sort-url" value="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$selected}&secfacetterm={$contentfilterselected}&license={$license}&owner={$owner}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}" />
    <div class="form-group select">
        <label for="search-filter-sort">
            {str tag=sortby section=search.elasticsearch}:
        </label>
        <span class="picker">
            <select id="search-filter-sort" class="form-control select autofocus">
                    <option value="ctime_asc" {if ($sort == 'ctime_asc')}selected{/if}>{str tag=dateoldestfirst section=search.elasticsearch}</option>
                    <option value="ctime_desc" {if ($sort == 'ctime_desc')}selected{/if}>{str tag=daterecentfirst section=search.elasticsearch}</option>
                    <option value="score" {if ($sort == 'score')}selected{/if}>{str tag=relevance section=search.elasticsearch}</option>
                    <option value="sort_asc" {if ($sort == 'sort_asc')}selected{/if}>{str tag=atoz section=search.elasticsearch}</option>
                    <option value="sort_desc" {if ($sort == 'sort_desc')}selected{/if}>{str tag=ztoa section=search.elasticsearch}</option>
            </select>
        </span>
    </div>
</div>