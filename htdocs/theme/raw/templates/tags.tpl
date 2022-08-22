{include file="header.tpl"}
{if $tags}
    <div class="btn-top-right btn-group btn-group-top">
        <button class="btn btn-secondary" data-url="{$WWWROOT}edittags.php" type="button">
            <span class="icon icon-pencil-alt left" role="presentation" aria-hidden="true"></span>{str tag=edittags}
        </button>
    </div>
    <ul class="nav nav-tabs">
    {foreach from=$tagsortoptions key=tagsortfield item=selectedsort name=tagsortoptions}
        <li><a href="{$WWWROOT}tags.php?ts={$tagsortfield}"{if $selectedsort} class="current-tab {if $selectedsort} active{/if}"{/if}>{str tag=sort$tagsortfield}<span class="accessible-hidden visually-hidden">({str tag=tab}{if $selectedsort} {str tag=selected}{/if})</span></a></li>
    {/foreach}
    </ul>
    <div class="mytags">
        <ul class="list-unstyled">
        {foreach from=$tags item=t}
            <li class="text-inline"><a id="tag:{$t->tag|urlencode|safe}" class="tag {if $t->tag == $tag}selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode|safe}">{$t->tag|str_shorten_text:30}&nbsp;{if $t->owner}({$t->owner}){/if}<span class="tagfreq badge">{$t->count}</span></a></li>
        {/foreach}
        </ul>
    </div>
    <div id="results_container" class="card tag-results">
        <h2 id="results_heading" class="card-header">{str tag=searchresultsfor}
            <a class="tag secondary-link" href="{$results->baseurl}{if $tag}{$results->queryprefix}tag={$tag|urlencode|safe}{/if}">{if $tag}{$tag|str_shorten_text:50}{else}{str tag=alltags}{/if}</a>
        </h2>
        {if $not_institution_tag}
        <div class="btn-top-right btn-group btn-group-top d-block{if !$tag} d-none{/if}">
            <button class="btn btn-secondary edit-tag float-end{if !$tag} d-none{/if}" data-url="{$WWWROOT}edittags.php?tag={$tag|urlencode|safe}"><span class="icon icon-pencil-alt left" role="presentation" aria-hidden="true"></span>{str tag=editthistag}</button>
        </div>
        {/if}
        <div class="tag-filters">
            <div id="results_sort" class="float-end">
                <strong>{str tag=sortresultsby}</strong>
                {foreach from=$results->sortcols item=sortfield name=sortcols}
                    <a href="{$results->baseurl}{$results->queryprefix}sort={$sortfield}"{if $results->sort == $sortfield} class="selected"{/if}>{str tag=$sortfield}</a>{if !$.foreach.sortcols.last} <span class="sep">|</span>{/if}
                {/foreach}
            </div>
            <div class="btn-group dropright">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-hidden="true" aria-haspopup="true" aria-expanded="false">
                    <span class="icon icon-filter left" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">{str tag=filterresultsby}</span>
                    {foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
                        <span id="currentfilter" {if $results->filter != $filtername} class="d-none"{/if}>{$filterdisplay}</span>
                    {/foreach}
                    <span class="icon icon-caret-down right" role="presentation" aria-hidden="true"></span>
                </button>
                <div class="dropdown-menu">
                    {foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
                        <li class="dropdown-item"><a href="{$results->baseurl}{$results->queryprefix}type={$filtername}"{if $results->filter == $filtername} class="selected"{/if}>{$filterdisplay}</a></li>
                    {/foreach}
                </div>
            </div>
        </div>
        <div id="results" class="list-group">
            {if $results->data}
                {$results->tablerows|safe}
            {/if}
        </div>
    </div>
    {$results->pagination|safe}
{else}
    <div class="no-results">{str tag=youhavenottaggedanythingyet}</div>
{/if}

{include file="footer.tpl"}
