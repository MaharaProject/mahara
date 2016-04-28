{if $watchlistempty}
    <div class="panel-body">
        <p class="lead text-small">{str tag=nopages section=blocktype.watchlist}</p>
    </div>
{else}
    <ul id="watchlistblock" class="viewlist list-group">
        {$watchlist.tablerows|safe}
    </ul>
    {if $watchlist.pagination}
        <div id="watchlist_page_container" class="hidden">{$watchlist.pagination|safe}</div>
    {/if}
    {if $watchlist.pagination_js}
    <script type="application/javascript">
        jQuery(function() {literal}{{/literal}
            {$watchlist.pagination_js|safe}
            removeElementClass('watchlist_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
{/if}
