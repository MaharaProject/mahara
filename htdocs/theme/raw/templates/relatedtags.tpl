{include file="header.tpl"}
{if $noresultsmessage}
    <div class="no-results">{$noresultsmessage}</div>
{else}
    <div id="results_container" class="panel panel-default tag-results">
        <h2 id="results_heading" class="panel-heading">{str tag=searchresultsfor}
            <a class="tag secondary-link" href="{$results->baseurl}">{$tag|str_shorten_text:50}</a>
        </h2>
        <div class="tag-filters">
            <div id="results_sort" class="pull-right">
                <strong>{str tag=sortresultsby}</strong>
                {foreach from=$results->sortcols item=sortfield name=sortcols}
                    <a href="{$results->baseurl}{$results->queryprefix}type={$results->filter}&sort={$sortfield}"{if $results->sort == $sortfield} class="selected"{/if}>{str tag=$sortfield}</a>{if !$.foreach.sortcols.last} <span class="sep">|</span>{/if}
                {/foreach}
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-default select-title dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="icon icon-filter left" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=filterresultsby}</span>
                    {foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
                        <span id="currentfilter" {if $results->filter != $filtername} class="hidden"{/if}>{$filterdisplay}</span>
                    {/foreach}
                    <span class="icon icon-caret-down right" role="presentation" aria-hidden="true"></span>
                </button>
                <ul class="dropdown-menu" id="results_filter">
                {foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
                    <li>
                        <a href="{$results->baseurl}{$results->queryprefix}sort={$results->sort}&type={$filtername}"{if $results->filter == $filtername} class="selected"{/if}>{$filterdisplay}</a>
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
        <div id="results" class="list-group">
            {if $results->data}
                {$results->tablerows|safe}
            {/if}
        </div>
    </div>
    {$results->pagination|safe}
{/if}

{include file="footer.tpl"}
