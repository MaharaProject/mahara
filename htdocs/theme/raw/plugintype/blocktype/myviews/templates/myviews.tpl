{if $myviews}
    <div class="list-group">
        <div id="myviewlist" class="list-group">
            {$myviews.tablerows|safe}
        </div>
    {if $myviews.pagination}
        <div id="myviews_page_container" class="hidden">{$myviews.pagination|safe}</div>
    {/if}
    {if $myviews.pagination_js}
    <script type="application/javascript">
        addLoadEvent(function() {literal}{{/literal}
            {$myviews.pagination_js|safe}
            removeElementClass('myviews_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
    </div>
{else}
<p class="content-text lead text-center">{str tag='noviewstosee' section='group'}</p>
{/if}

