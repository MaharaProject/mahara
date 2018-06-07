{if $watchlistempty}
    <div class="card-body">
        <p class="lead text-small">{str tag=nopages section=blocktype.watchlist}</p>
    </div>
{else}
    <ul id="watchlistblock" class="viewlist list-group">
        {$watchlist.tablerows|safe}
    </ul>
    {if $watchlist.pagination}
        <div id="watchlist_page_container" class="d-none">{$watchlist.pagination|safe}</div>
    {/if}
    {if $watchlist.pagination_js}
    <script>
        jQuery(function($) {literal}{{/literal}
            {$watchlist.pagination_js|safe}
            $('#watchlist_page_container').removeClass('d-none');
        {literal}}{/literal});
    </script>
    {/if}
{/if}
