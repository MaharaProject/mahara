{include file="header.tpl"}

<div id="logsearchformcontainer" class="card card-body">
{$form|safe}
</div>
<div id="results" class="section card">
    <h2 class="card-header" id="resultsheading">{str tag="Results"}</h2>
    {if $results}
    <div class="table-responsive">
    <table id="searchresults" class="table table-striped fullwidth listing">
        <thead>
            <tr>
                {foreach from=$columns key=f item=c}
                <th class="{if $c.sort}search-results-sort-column{if $f == $sortby} {$sortdir}{/if}{/if}{if $c.class} {$c.class}{/if}">
                    {if $c.sort}
                        <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">
                            {$c.name}
                            <span class="accessible-hidden sr-only">({str tag=sortby} {if $f == $sortby && $sortdir == 'asc'}{str tag=descending}{else}{str tag=ascending}{/if})</span>
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
    {if $pagination_js}
        <script>
        {$pagination_js|safe}
        </script>
    {/if}
    </div>
    {else}
        <div class="card-body">
            <p class="no-results">{str tag="noresultsfound"}</p>
        </div>
    {/if}
</div>

<script>
// to clear any offset when submitting form again
jQuery(function() {
    jQuery('#logsearchform').on('submit', function(e) {
        jQuery('.currentoffset').attr('value', 0);
    });
});
</script>
{include file="footer.tpl"}
