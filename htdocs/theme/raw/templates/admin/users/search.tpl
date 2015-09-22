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
                        <option value="all"{if !$.request.institution} selected="selected"{/if}>{str tag=All}</option>
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
        <div class="input-group-btn button">
            <button id="query-button" class="btn-search btn btn-primary " type="submit">
            {str tag='Search' section='admin'}
            </button>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    <div class="col-md-12">
        <p class="lead mtl">{str tag="usersearchinstructions" section="admin"}</p>
    </div>
</div>
<div class="row">
    <div class="col-md-3 prs-md">

        <div class="panel panel-default">
            <h3 class="panel-heading" tabindex="0">{str tag="filterresultsby"}</h3>
            <div class="panel-body">
                  <div id="initials" class="initials">
                    <div id="firstnamelist">
                        <p class="pseudolabel" id="firstname">{str tag="firstname"}:</p>
                        <br/>

                        <a class="label first-initial{if !$search->f} label-primary active{else} label-default{/if} all" aria-describedby="firstname" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>

                       {foreach from=$alphabet item=a}
                            <a class="label first-initial{if $a == $search->f} label-primary active{else} label-default{/if}" aria-describedby="firstname" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;f={$a}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
                       {/foreach}
                    </div>
                    <div class="mtl" id="lastnamelist">
                      <p class="pseudolabel" id="lastname">{str tag="lastname"}:</p>
                        <br/>

                        <a class="label last-initial{if !$search->l} label-primary active{else} label-default{/if} all" aria-describedby="lastname" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>

                       {foreach from=$alphabet item=a}
                            <a class="label last-initial{if $a == $search->l} label-primary active{else} label-default{/if}" aria-describedby="lastname" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;l={$a}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
                       {/foreach}
                    </div>
                </div>

                <form class="mtm pieform" action="{$WWWROOT}admin/users/search.php" method="post">
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
                    <hr />
                    <div class="loggedin-filter mtl form-group">
                        <label for="loggedin">{str tag="lastlogin" section="admin"}</label>
                        <div class="picker">
                            <select class="form-control select" name="loggedin" id="loggedin">
                            {foreach from=$loggedintypes item=t}
                                <option value="{$t['name']}"{if $search->loggedin === $t['name']} selected="selected"{/if}>{$t['string']}</option>
                            {/foreach}
                            </select>
                        </div>
                        <span id="loggedindate_container"{if !($search->loggedin == 'since' || $search->loggedin == 'notsince')} class="js-hidden"{/if}>
                            {$loggedindate|safe}
                        </span>
                    </div>
                    <div class="duplicateemail-filter mtm checkbox form-group">
                       <input class="checkbox pull-left" type="checkbox" name="duplicateemail" id="duplicateemail" value="1"{if $search->duplicateemail} checked{/if}>
                        <label class="input-inline pls" for="duplicateemail">
                            {str tag="duplicateemailfilter1" section="admin"}
                        </label>

                    </div>
                </div>
            </div>


        </form>
    </div>
    <div class="col-md-9 pls-md">
        <div id="results" class="section panel panel-default">
            <h2 class="panel-heading" id="resultsheading">{str tag="Results"}</h2>
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
                </div>
                <div class="panel-body">
                    {$pagination|safe}
                </div>
            {else}
                <div class="panel-body"><p class="no-results">{str tag="noresultsfound"}</p></div>
            {/if}
            {if $USER->get('admin') || $USER->is_institutional_admin() || get_config('staffreports')}
                <div class="withselectedusers panel-body">
                    <div class="btn-group">
                        {if $USER->get('admin') || $USER->is_institutional_admin()}
                        <form class="nojs-hidden-inline form-as-button pull-left" id="bulkactions" action="{$WWWROOT}admin/users/bulk.php" method="post">
                            <button action="{$WWWROOT}admin/users/bulk.php" type="submit" class="btn btn-default disabled" name="edit" id="editbtn" value="{str tag=edit}">
                                <span class="icon icon-pencil prs"></span>
                                {str tag=withselectedusersedit section=admin}
                            </button>

                        </form>
                        {/if}
                        <form class="nojs-hidden-inline form-as-button pull-left" action="{$WWWROOT}admin/users/report.php" id="report" method="post">

                            <button action="{$WWWROOT}admin/users/report.php" type="submit" class="btn btn-default disabled" name="reports" id="reportsbtn" value="{str tag=getreports section=admin}">
                                <span class="icon icon-area-chart prs"></span>
                                {str tag=withselectedusersreports section=admin}
                            </button>

                        </form>
                    </div>
                    <div id="nousersselected" class="mtl hidden error alert alert-danger">{str tag=nousersselected section=admin}</div>
                </div>
            {/if}
        </div>
    </div>

</div>


{include file="footer.tpl"}
