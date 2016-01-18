{include file="header.tpl"}
{if $tags}
    <div class="btn-top-right btn-group btn-group-top">
        <a class="btn btn-default" href="{$WWWROOT}edittags.php"><span class="icon icon-lg icon-pencil left" role="presentation" aria-hidden="true"></span>{str tag=edittags}</a>
    </div>
    <ul class="nav nav-tabs">
    {foreach from=$tagsortoptions key=tagsortfield item=selectedsort name=tagsortoptions}
        <li{if $selectedsort} class="active"{/if}><a href="{$WWWROOT}tags.php?ts={$tagsortfield}"{if $selectedsort} class="current-tab"{/if}>{str tag=sort$tagsortfield}<span class="accessible-hidden sr-only">({str tag=tab}{if $selectedsort} {str tag=selected}{/if})</span></a></li>
    {/foreach}
    </ul>
    <div class="mytags">
        <ul class="list-unstyled">
        {foreach from=$tags item=t}
            <li class="text-inline"><a id="tag:{$t->tag|urlencode|safe}" class="tag {if $t->tag == $tag}selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode|safe}">{$t->tag|str_shorten_text:30}&nbsp;<span class="tagfreq badge">{$t->count}</span></a></li>
        {/foreach}
        </ul>
    </div>
    <div id="results_container" class="panel panel-default tag-results">
        <h2 id="results_heading" class="panel-heading">{str tag=searchresultsfor}
            <a class="tag secondary-link" href="{$WWWROOT}tags.php{if $tag}{$results->queryprefix}tag={$tag|urlencode|safe}{/if}">{if $tag}{$tag|str_shorten_text:50}{else}{str tag=alltags}{/if}</a>
        </h2>
        <div class="btn-top-right btn-group btn-group-top">
            <a class="btn btn-default edit-tag{if !$tag} hidden{/if}" href="{$WWWROOT}edittags.php?tag={$tag|urlencode|safe}"><span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>{str tag=editthistag}</a>
        </div>
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
                        <span {if $results->filter != $filtername} class="hidden"{/if}>{$filterdisplay}</span>
                    {/foreach}
                    <span class="icon icon-caret-down right" role="presentation" aria-hidden="true"></span>
                </button>
                <ul class="dropdown-menu">
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
{else}
    <div class="no-results">{str tag=youhavenottaggedanythingyet}</div>
{/if}

{include file="footer.tpl"}
