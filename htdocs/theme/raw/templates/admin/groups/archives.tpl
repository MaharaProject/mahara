{include file="header.tpl"}

<ul class="nav nav-tabs">
    <li class="nav-item" aria-hidden="true">
        <a href="{$WWWROOT}admin/groups/archives.php" class="nav-link {$tabs->archivedclass}">
            {str tag=archivedsubmissions section=admin}
        </a>
    </li>
    <li class="nav-item" aria-hidden="true">
        <a href="{$WWWROOT}admin/groups/archives.php?current=1" class="nav-link {$tabs->currentclass}">
            {str tag=currentsubmissions section=admin}
        </a>
    </li>
</ul>
<form class="form-inline pieform form with-heading" action="{$WWWROOT}admin/groups/archives.php" method="post">
    {if $search->sortby}
    <input type="hidden" name="sortby" id="sortby" value="{$search->sortby}">
    {/if}
    {if $search->sortdir}
    <input type="hidden" name="sortdir" id="sortdir" value="{$search->sortdir}">
    {/if}
    {if $limit}
    <input type="hidden" name="limit" id="limit" value="{$limit}">
    {/if}
    <input type="hidden" name="current" id="current" value="{$search->type}">

    <div class="admin-user-search">
        {if count($institutions) > 1}
        <div class="dropdown-group js-dropdown-group form-group">
            <fieldset class="pieform-fieldset dropdown-group js-dropdown-group">
                <div class="usersearchform with-dropdown js-with-dropdown text form-group">
                    <label for="query">{str tag='usersearch' section='admin'}: </label>
                    <input  class="form-control with-dropdown js-with-dropdown text" type="text" name="query" id="query"{if $search->query} value="{$search->query}"{/if}>
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
            <button id="query-button" class="btn-search btn btn-secondary" type="submit">{str tag="search"}</button>
        </div>
        {else}
        <div class="searchform text input-group">
            <label class="visually-hidden" for="query">{str tag='usersearch' section='admin'}</label>
            <input placeholder="{str tag='usersearch' section='admin'}" class="text form-control" type="text" name="query" id="query"{if $search->query} value="{$search->query}"{/if}>
            <div class="input-group-append button">
                <button id="query-button" class="btn-search btn btn-secondary " type="submit">
                {str tag="search"}
                </button>
            </div>
        </div>
        {/if}
    </div>
    <script>
    // Append a querystring to the URL.
    var buildUrl = function(base, key, value) {
        var glue = (base.indexOf('?') > -1) ? '&' : '?';
        return base + glue + key + '=' + value;
    }
    // Handle institution onclick.
    jQuery(function($) {
        var csvlink = '{$WWWROOT}admin/groups/archivescsvdownload.php';
        {if $tabs->currentclass == 'active'}
            csvlink = buildUrl(csvlink, 'current', '1');
        {/if}
        $('#institution').on('change', function() {
            if ($(this).val() != 'all') {
                thisUrl = buildUrl(csvlink, 'institution', $j(this).val());
                $('#csvlink').attr('href', thisUrl);
            }
            else {
                $('#csvlink').attr('href', csvlink);
            }
        });
        // Update the link on page load as well.
        var selectedInstitution = $('#institution').find(":selected").val();
        if (selectedInstitution == undefined) {
            selectedInstitution = 'all';
        }
        thisUrl = buildUrl(csvlink, 'institution', selectedInstitution);
        $('#csvlink').attr('href', thisUrl);
    });
    </script>
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
                        {if $c.headhtml}<div class="headhtml">{$c.headhtml|safe}</div>{/if}
                        {if $c.helplink}
                            {$c.helplink|safe}
                        {/if}
                    </th>
                    {/foreach}
                </tr>
            </thead>
            <tbody>
                {$results|safe}
            </tbody>
            {if $searchtypecurrent}
            <tfoot>
                <tr id="buttonsrow">
                    <td colspan="{math equation="x-1" x=count($columns)}">
                        <div id="nocontentselected" class="d-none error">{str tag=nocontentselected section=admin}</div>
                    </td>
                    <td class="text-center">
                        <form class="nojs-hidden-inline" id="releaseform" action="{$WWWROOT}admin/groups/archives.php?current=1" method="post">
                            <label class="accessible-hidden visually-hidden" for="releasebtn">{str tag=withselectedcontentrelease section=admin}</label>
                            <input type="button" class="button btn btn-secondary btn-sm" name="releasesubmissions" id="releasebtn" value="{str tag=release section=statistics}">
                            <label class="accessible-hidden visually-hidden" for="releaseandreturnbtn">{str tag=withselectedcontentreleaseandreturn section=admin}</label>
                            <input type="button" class="button btn btn-secondary btn-sm" name="releaseandreturnsubmissions" id="releaseandreturnbtn" value="{str tag='releaseandreturn' section='module.submissions'}">
                        </form>
                    </td>
                </tr>
            </tfoot>
            {/if}
        </table>
        <div class="card-body">
            {$pagination|safe}
        </div>

        <a class="card-footer text-small" id="csvlink" href="{$WWWROOT}admin/groups/archivescsvdownload.php{if $.request.institution}?institution={$.request.institution}{/if}">
        <span class="icon icon-table left" role="presentation" aria-hidden="true"></span>
        {str tag=exportdataascsv section=admin}
        </a>

        {else}
            <div class="card-body">
                <p class="no-results"> {str tag="noresultsfound"}</p>
            </div>
        {/if}

    </div>
</div>
{include file="footer.tpl"}
