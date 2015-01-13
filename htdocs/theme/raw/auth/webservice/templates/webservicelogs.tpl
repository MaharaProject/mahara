{include file="header.tpl"}

    <form id="logsearchform" action="{$WWWROOT}webservice/admin/webservicelogs.php" method="post">
        <div class="searchform">
            <label>{str tag='userauth' section='auth.webservice'}:</label>
            <input type="text" name="userquery" id="query"{if $search->userquery} value="{$search->userquery}"{/if}>
            {if count($institutions) > 1}
            <span class="institutions">
                <label>{str tag='Institution' section='admin'}:</label>
                    {if $USER->get('admin')}
                    <select name="institution" id="institution">
                    {else}
                    <select name="institution_requested" id="institution_requested">
                    {/if}
                        <option value="all"{if !$.request.institution} selected="selected"{/if}>{str tag=All}</option>
                        {foreach from=$institutions item=i}
                        <option value="{$i->name}"{if $i->name == $.request.institution}" selected="selected"{/if}>{$i->displayname}</option>
                        {/foreach}
                    </select>
            </span>
            {/if}
            <span class="institutions">
                <label>{str tag='protocol' section='auth.webservice'}:</label>
                    <select name="protocol" id="protocol">
                        <option value="all"{if !$.request.protocol} selected="selected"{/if}>{str tag=All}</option>
                        {foreach from=$protocols item=i}
                        <option value="{$i}"{if $i == $.request.protocol}" selected="selected"{/if}>{$i}</option>
                        {/foreach}
                    </select>
            </span>
            <span class="institutions">
                <label>{str tag='sauthtype' section='auth.webservice'}:</label>
                    <select name="authtype" id="authtype">
                        <option value="all"{if !$.request.authtype} selected="selected"{/if}>{str tag=All}</option>
                        {foreach from=$authtypes item=i}
                        <option value="{$i}"{if $i == $.request.authtype}" selected="selected"{/if}>{$i}</option>
                        {/foreach}
                    </select>
            </span>
            <label>{str tag='function' section='auth.webservice'}:</label>
            <input type="text" name="functionquery" id="query"{if $search->functionquery} value="{$search->functionquery}"{/if}>
            <button id="query-button" class="btn-search" type="submit">{str tag="go"}</button>
            <br/>
            <label>{str tag='errors' section='auth.webservice'}:</label>
            <input type="checkbox" name="onlyerrors" id="query"{if $search->onlyerrors} CHECKED{/if}>
        </div>
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
                        {/if}
                        {if $c.help}
                            {$c.helplink|safe}
                        {/if}
                        {if $c.headhtml}<div style="font-weight: normal;">{$c.headhtml|safe}</div>{/if}
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
        </div>
    </form>
<script type="application/javascript">
// to clear any offset when submitting form again
jQuery(function() {
    jQuery('#logsearchform').submit(function(e) {
        jQuery('.currentoffset').attr('value', 0);
    });
});
</script>
{include file="footer.tpl"}
