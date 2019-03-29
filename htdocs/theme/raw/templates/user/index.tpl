{include file="header.tpl"}

{$form|safe}
{if $message}
    <p class="no-results">
        {$message|safe}
    </p>
{/if}

{if $count > 0}
    <div class="card view-container">
        <h2 id="searchresultsheading" class="card-header">{str tag=Results}</h2>
        <div id="friendslist" class="list-group">
            {$results.tablerows|safe}
        </div>
    </div>
        {$results.pagination|safe}
        {if $results.pagination_js}
            <script>
            {$results.pagination_js|safe}
            </script>
        {/if}
{else}
    <p class="no-results">{str tag=nosearchresultsfound section=group}</p>
{/if}

{include file="footer.tpl"}
