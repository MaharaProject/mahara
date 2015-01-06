{include file="header.tpl"}

    <p>{str tag="usersearchinstructions" section="admin"}</p>
    <span class="accessible-hidden" tabindex="0">{str tag="filterresultsby"}</span>
    <div id="initials">
        <div id="firstnamelist">
          <strong id="firstname">{str tag="firstname"}:</strong>
           <span class="first-initial{if !$search->f} selected{/if} all">
            <a aria-labelledby="firstname" aria-label="{str tag="firstnameall"}" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>
           </span>
           {foreach from=$alphabet item=a}
           <span class="first-initial{if $a == $search->f} selected{/if}">
            <a aria-labelledby="firstname" aria-label="{str tag="firstname"}" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;f={$a}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
           </span>
           {/foreach}
        </div>
        <div id="lastnamelist">
          <strong id="lastname">{str tag="lastname"}:</strong>
           <span class="last-initial{if !$search->l} selected{/if} all">
            <a aria-labelledby="lastname" aria-label="{str tag="lastnameall"}" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>
           </span>
           {foreach from=$alphabet item=a}
           <span class="last-initial{if $a == $search->l} selected{/if}">
            <a aria-labelledby="lastname" aria-label="{str tag="lastname"}" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;l={$a}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
           </span>
           {/foreach}
        </div>
    </div>
    <form action="{$WWWROOT}admin/users/search.php" method="post">
        {if $search->f}
        <input type="hidden" name="f" id="f" value="{$search->f}">
        {/if}
        {if $search->l}
        <input type="hidden" name="l" id="l" value="{$search->l}">
        {/if}
        {if $search->sortby}
        <input type="hidden" name="sortby" id="sortby" value="{$search->sortby}">
        {/if}
        {if $search->sortdir}
        <input type="hidden" name="sortdir" id="sortdir" value="{$search->sortdir}">
        {/if}
        {if $limit}
        <input type="hidden" name="limit" id="limit" value="{$limit}">
        {/if}
        <div class="loggedin-filter">
            <label for="loggedin">{str tag="loggedinfilter" section="admin"}</label>
            <select name="loggedin" id="loggedin">
            {foreach from=$loggedintypes item=t}
                <option value="{$t['name']}"{if $search->loggedin === $t['name']} selected="selected"{/if}>{$t['string']}</option>
            {/foreach}
            </select>
            <span id="loggedindate_container"{if !($search->loggedin == 'since' || $search->loggedin == 'notsince')} class="js-hidden"{/if}>
                {$loggedindate|safe}
            </span>
        </div>
        <div class="duplicateemail-filter">
            <label for="duplicateemail">{str tag="duplicateemailfilter" section="admin"}</label>
            <input type="checkbox" name="duplicateemail" id="duplicateemail" value="1"{if $search->duplicateemail} checked{/if}>
        </div>
        <div class="usersearchform">
            <label for="query">{str tag='Search' section='admin'}:</label>
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
    </form>
    {if $USER->get('admin') || $USER->is_institutional_admin() || get_config('staffreports')}
    <div class="withselectedusers">
        <strong>{str tag=withselectedusers section=admin}: </strong>
        {if $USER->get('admin') || $USER->is_institutional_admin()}
        <form class="nojs-hidden-inline" id="bulkactions" action="{$WWWROOT}admin/users/bulk.php" method="post">
            <label class="accessible-hidden" for="editbtn">{str tag=withselectedusersedit section=admin}</label>
            <input type="button" class="button" name="edit" id="editbtn" value="{str tag=edit}">
        </form>
        {/if}
        <form class="nojs-hidden-inline" id="report" action="{$WWWROOT}admin/users/report.php" method="post">
            <label class="accessible-hidden" for="reportsbtn">{str tag=withselectedusersreports section=admin}</label>
            <input type="button" class="button" name="reports" id="reportsbtn" value="{str tag=getreports section=admin}">
        </form>
        <div id="nousersselected" class="hidden error">{str tag=nousersselected section=admin}</div>
    </div>
    {/if}
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
