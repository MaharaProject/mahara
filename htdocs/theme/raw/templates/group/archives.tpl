{include file="header.tpl"}
<form class="form-inline pieform form with-heading" action="{$WWWROOT}group/archives.php" method="post">
    <input type="hidden" name="group" id="group" value="{$group}">
    {if $search->sortby}
    <input type="hidden" name="sortby" id="sortby" value="{$search->sortby}">
    {/if}
    {if $search->sortdir}
    <input type="hidden" name="sortdir" id="sortdir" value="{$search->sortdir}">
    {/if}
    {if $limit}
    <input type="hidden" name="limit" id="limit" value="{$limit}">
    {/if}
    <div class="admin-user-search">
        <div class="searchform text input-group">
            <label class="visually-hidden" for="query">{str tag='usersearch' section='admin'}</label>
            <input placeholder="{str tag='usersearch' section='admin'}" class="text form-control" type="text" name="query" id="query"{if $search->query} value="{$search->query}"{/if}>
            <div class="input-group-append button">
                <button id="query-button" class="btn-search btn btn-primary " type="submit">
                {str tag="search"}
                </button>
            </div>
        </div>
    </div>
</form>

{if $query}<h5>{str tag="searchresultsfor" section="mahara"} {$query}</h5>{/if}

<div id="results" class="card view-container">
    <h2 class="card-header" id="resultsheading">{str tag="Results"}</h2>
        {if $results}
        <table id="searchresults" class="tablerenderer fullwidth table">
            <thead>
                <tr>
                    {foreach from=$columns key=f item=c}
                    <th class="{if $c.sort}search-results-sort-column{if $f == $sortby} {$sortdir}{/if}{/if}{if $c.class} {$c.class}{/if}">
                        {if $c.sort}
                            <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">
                                <span>{$c.name}</span>
                                <span class="accessible-hidden visually-hidden">({str tag=sortby} {if $f == $sortby && $sortdir == 'asc'}{str tag=descending}{else}{str tag=ascending}{/if})</span>
                            </a>
                        {else}
                            {$c.name}
                            {if $c.accessible}
                                <span class="accessible-hidden visually-hidden">{$c.accessible}</span>
                            {/if}
                        {/if}
                        {if $c.help}
                            {$c.helplink|safe}
                        {/if}
                        {if $c.headhtml}<div class="headhtml">{$c.headhtml|safe}</div>{/if}
                    </th>
                    {/foreach}
                </tr>
            </thead>
            <tbody>
                {$results|safe}
            </tbody>
        </table>
        <div class="card-body">
            {$pagination|safe}
        </div>
        <a class="card-footer text-small" id="csvlink" href="{$WWWROOT}group/archivescsvdownload.php?group={$group}">
            <span class="icon icon-table left" role="presentation" aria-hidden="true"></span>
            {str tag=exportdataascsv section=admin}
        </a>
        {else}
            <div class="card-body">
                <p class="no-results"> {str tag="noresultsfound"}</p>
            </div>
        {/if}

</div>
{include file="footer.tpl"}
