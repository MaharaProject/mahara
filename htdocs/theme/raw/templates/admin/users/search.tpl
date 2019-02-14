{include file="header.tpl"}

<div class="form form-inline with-heading dropdown admin-user-search">
    {if count($institutions) > 1}
    <div class="dropdown-group js-dropdown-group form-group">
        <fieldset class="pieform-fieldset dropdown-group js-dropdown-group">
            <div class="usersearchform with-dropdown js-with-dropdown text form-group">
                <label for="query">{str tag='Search' section='admin'}: </label>
                <input placeholder="{str tag='Search' section='admin'}" class="form-control with-dropdown js-with-dropdown text" type="text" name="query" id="query"{if $search->query} value="{$search->query}"{/if}>
            </div>
            <div class="dropdown-connect js-dropdown-connect select form-group">
                <label for="institution">{str tag='Institution' section='admin'}:</label>
                 <span class="picker">
                    <select class="form-control dropdown-connect js-dropdown-connect select" name="institution" id="institution">
                        <option value="all"{if !$.request.institution} selected="selected"{/if}>{str tag=Allinstitutions}</option>
                        {foreach from=$institutions item=i}
                        <option value="{$i->name}"{if $i->name == $.request.institution}" selected="selected"{/if}>{$i->displayname}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </fieldset>
    </div>
    <div class="no-label text-inline form-group">
        <button id="query-button" class="btn-search btn btn-primary " type="submit">
        {str tag='Search' section='admin'}
        </button>
    </div>
    {else}
    <div class="usersearchform text input-group">
        <label class="sr-only" for="query">{str tag='Search' section='admin'}: </label>
        <input placeholder="{str tag='Search' section='admin'}" class="text form-control" type="text" name="query" id="query"{if $search->query} value="{$search->query}"{/if}>
        <div class="input-group-append button">
            <button id="query-button" class="btn-search btn btn-primary " type="submit">
            {str tag='Search' section='admin'}
            </button>
        </div>
    </div>
    {/if}
    <div class="advanced as-link link-expand-right form-group collapsible-group">
        <fieldset class="pieform-fieldset advanced last as-link link-expand-right collapsible">
            <legend>
                <h4>
                    <a href="#initials" data-toggle="collapse" aria-expanded="{if $search->f || $search->l}true{else}false{/if}" aria-controls="initials" class="{if !$search->f && !$search->l}collapsed{/if}">
                        {str tag='moreoptions' section='view'}
                        <span class="icon icon-chevron-down collapse-indicator right float-right" role="presentation" aria-hidden="true"></span>
                    </a>
                </h4>
            </legend>
            <div id="initials" class="initials collapse{if $search->f || $search->l} show{/if}" aria-expanded="{if $search->f || $search->l}true{else}false{/if}">
                <h3 class="filter-result-heading" tabindex="0">{str tag="filterresultsby"}</h3>
                <div class="row">
                    <div id="firstnamelist" class="col-md-4 userserach-filter">
                        <span class="pseudolabel" id="firstname">{str tag="firstname"}:</span>
                        <br/>
                        <a class="badge first-initial{if !$search->f} badge-primary active{else} badge-default{/if} all" aria-describedby="firstname" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>

                       {foreach from=$alphabet item=a}
                        <a class="badge first-initial{if $a == $search->f} badge-primary active{else} badge-default{/if}" aria-describedby="firstname" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;f={$a}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
                       {/foreach}
                    </div>

                    <div id="lastnamelist" class="col-md-4 userserach-filter">
                        <span class="pseudolabel" id="lastname">{str tag="lastname"}:</span>
                        <br/>
                        <a class="badge last-initial{if !$search->l} badge-primary active{else} badge-default{/if} all" aria-describedby="lastname" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>

                       {foreach from=$alphabet item=a}
                        <a class="badge last-initial{if $a == $search->l} badge-primary active{else} badge-default{/if}" aria-describedby="lastname" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;l={$a}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
                       {/foreach}
                    </div>

                    <div id="lastlogin-filter" class="col-md-4 userserach-filter">
                        <form class="pieform" action="{$WWWROOT}admin/users/search.php" method="post">
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
                                <label for="loggedin">{str tag="lastlogin" section="admin"}</label>
                                <div class="picker">
                                    <select class="form-control select" name="loggedin" id="loggedin">
                                    {foreach from=$loggedintypes item=t}
                                        <option value="{$t['name']}"{if $search->loggedin === $t['name']} selected="selected"{/if}>{$t['string']}</option>
                                    {/foreach}
                                    </select>
                                </div>
                                <div id="loggedindate_container" class="loggedindate-container {if !($search->loggedin == 'since' || $search->loggedin == 'notsince')}js-hidden{/if}">
                                    {$loggedindate|safe}
                                </div>
                            </div>
                            <div class="duplicateemail-filter checkbox">
                                <label class="input-inline" for="duplicateemail">
                                    <input class="checkbox" type="checkbox" name="duplicateemail" id="duplicateemail" value="1"{if $search->duplicateemail} checked{/if}>
                                    {str tag="duplicateemailfilter1" section="admin"}
                                </label>
                            </div>
                            <div class="objectionable-filter checkbox">
                                <label class="input-inline" for="objectionable">
                                    <input class="checkbox" type="checkbox" name="objectionable" id="objectionable" value="1"{if $search->objectionable} checked{/if}>
                                    {str tag="objectionablefilter" section="admin"}
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>
<p class="lead view-description">{str tag="usersearchinstructions" section="admin"}</p>
<div id="results" class="section card view-container">
    <h2 class="card-header" id="resultsheading">{str tag="Results"}</h2>
    {if $results}
        <div class="table-responsive">
            <table id="searchresults" class="table table-striped fullwidth listing">
                <thead>
                    <tr>
                        {foreach from=$columns key=f item=c}
                            {if !$c.mergelast}
                            <th class="{if $c.sort}search-results-sort-column{if $f == $sortby} {$sortdir}{/if}{/if}{if $c.class} {$c.class}{/if}">
                            {/if}
                                {if $c.sort}
                                    <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">
                                        {$c.name}
                                        <span class="accessible-hidden sr-only">({str tag=sortby} {if $f == $sortby && $sortdir == 'asc'}{str tag=descending}{else}{str tag=ascending}{/if})</span>
                                    </a>
                                {else}
                                    {$c.name}
                                    {if $c.accessible}
                                        <span class="accessible-hidden sr-only">{$c.accessible}</span>
                                    {/if}
                                {/if}
                                {if $c.help}
                                    {$c.helplink|safe}
                                {/if}
                                {if $c.headhtml}<div class="headhtml allnone-toggles">{$c.headhtml|safe}</div>{/if}
                            {if !$c.mergefirst}
                            </th>
                            {/if}
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {$results|safe}
                </tbody>
            </table>
            {$pagination|safe}
        </div>
    {else}
        <p class="no-results">{str tag="noresultsfound"}</p>
    {/if}
    {if $USER->get('admin') || $USER->is_institutional_admin() || get_config('staffreports')}
        <div class="withselectedusers card-body">
            <div class="btn-group">
                {if $USER->get('admin') || $USER->is_institutional_admin()}
                <form class="nojs-hidden-inline form-as-button float-left" id="bulkactions" action="{$WWWROOT}admin/users/bulk.php" method="post">
                    <button action="{$WWWROOT}admin/users/bulk.php" type="submit" class="btn btn-secondary disabled" name="edit" id="editbtn" value="{str tag=edit}">
                        <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
                        {str tag=withselectedusersedit section=admin}
                    </button>

                </form>
                {/if}
                <form class="nojs-hidden-inline form-as-button float-left" action="{$WWWROOT}admin/users/statistics.php" id="report" method="post">

                    <button action="{$WWWROOT}admin/users/statistics.php" type="submit" class="btn btn-secondary disabled" name="reports" id="reportsbtn" value="{str tag=getreports section=admin}">
                        <span class="icon icon-area-chart left" role="presentation" aria-hidden="true"></span>
                        {str tag=withselectedusersreports section=admin}
                    </button>
                    <input type="hidden" name="type" value="users">
                    <input type="hidden" name="subtype" value="userdetails">
                </form>
            </div>
            <div id="nousersselected" class="d-none error alert alert-danger">
                {str tag=nousersselected section=admin}
            </div>
        </div>
    {/if}
</div>

{include file="footer.tpl"}
