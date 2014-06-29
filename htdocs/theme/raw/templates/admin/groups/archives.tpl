{include file="header.tpl"}

    <form action="{$WWWROOT}admin/groups/archive.php" method="post">
        {if $search->sortby}
        <input type="hidden" name="sortby" id="sortby" value="{$search->sortby}">
        {/if}
        {if $search->sortdir}
        <input type="hidden" name="sortdir" id="sortdir" value="{$search->sortdir}">
        {/if}
        {if $limit}
        <input type="hidden" name="limit" id="limit" value="{$limit}">
        {/if}
        <div class="fr">
            <a id="csvlink" href="{$WWWROOT}admin/groups/archivescsvdownload.php{if $.request.institution}?institution={$.request.institution}{/if}" target="_blank">{str tag=exportdataascsv section=admin}</a>
        </div>
        <div class="usersearchform">
            <label for="query">{str tag='usersearch' section='admin'}:</label>
            <input type="text" name="query" id="query"{if $search->query} value="{$search->query}"{/if}>
            {if count($institutions) > 1}
            <span class="institutions">
                <label for="institution">{str tag='Institution' section='admin'}:</label>
                <select name="institution" id="institution">
                    <option value="all"{if !$.request.institution} selected="selected"{/if}>{str tag=All}</option>
                    {foreach from=$institutions item=i}
                    <option value="{$i->name}"{if $i->name == $.request.institution}" selected="selected"{/if}>{$i->displayname}</option>
                    {/foreach}
                </select>
            </span>
            {/if}
            <button id="query-button" class="btn-search" type="submit">{str tag="go"}</button>
        </div>
        <script type="text/javascript">
        $j(function() {
            var csvlink = '{$WWWROOT}admin/groups/archivescsvdownload.php';
            $j('#institution').on('change', function() {
                if ($j(this).val() != 'all') {
                    $j('#csvlink').attr('href', csvlink + '?institution=' + $j(this).val());
                }
                else {
                    $j('#csvlink').attr('href', csvlink);
                }
            });
        });
        </script>
    </form>
    <div id="results" class="section">
        <h2 id="resultsheading">{str tag="Results"}</h2>
        {if $results}
        <table id="searchresults" class="tablerenderer fullwidth listing">
            <thead>
                <tr>
                    {foreach from=$columns key=f item=c}
                    <th class="{if $c.sort}search-results-sort-column{if $f == $sortby} {$sortdir}{/if}{/if}{if $c.class} {$c.class}{/if}">
                        {if $c.sort}
                            <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">
                                {$c.name}
                                <span class="accessible-hidden">({str tag=sortby} {if $f == $sortby && $sortdir == 'asc'}{str tag=descending}{else}{str tag=ascending}{/if})</span>
                            </a>
                        {else}
                            {$c.name}
                            {if $c.accessible}
                                <span class="accessible-hidden">{$c.accessible}</span>
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
        {$pagination|safe}
        {else}
            <div>{str tag="noresultsfound"}</div>
        {/if}
    </div>

{include file="footer.tpl"}
