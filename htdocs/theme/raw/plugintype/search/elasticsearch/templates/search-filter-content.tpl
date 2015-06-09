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
<div id="results_filter" class="search-filter-content">
    <strong class="filtertitle">{str tag=filterresultsby section=search.elasticsearch}:</strong>
    {foreach from=$contentfilter item=term}
        {if $term.count > 0}
            <div class="filtername"><a href="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$selected}&secfacetterm={$term.term}&owner={$owner}&sort={$sort}&license={$license}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}"{if $term.term == $contentfilterselected} class="selected"{/if}>{$term.display} ({$term.count})</a></div>
        {/if}
    {/foreach}
</div>