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
<div id="results_filter" class="search-filter-content btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="icon icon-filter left" role="presentation" aria-hidden="true"></span>
        <span class="sr-only">{str tag=filterresultsby section=search.elasticsearch}</span>
        {foreach from=$contentfilter item=term}
            <span {if $term.term != $contentfilterselected} class="hidden"{/if}>{$term.display} ({$term.count})</span>
        {/foreach}
        <span class="icon icon-caret-down right" role="presentation" aria-hidden="true"></span>
    </button>

    <ul class="dropdown-menu" role="menu">
    {foreach from=$contentfilter item=term}
        {if $term.count > 0}
            <li class="filtername">
                <a href="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$selected}&secfacetterm={$term.term}&owner={$owner}&sort={$sort}&license={$license}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}"{if $term.term == $contentfilterselected} class="selected"{/if}>
                {$term.display} ({$term.count})
                </a>
            </li>
        {/if}
    {/foreach}
    </ul>
</div>
