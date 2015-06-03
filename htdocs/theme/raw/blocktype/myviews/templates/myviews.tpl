{if $myviews}
    <div class="myviewsection">
        <div id="myviewlist" class="fullwidth listing">
            {$myviews.tablerows|safe}
        </div>
    {if $myviews.pagination}
        <div id="myviews_page_container" class="hidden center">{$myviews.pagination|safe}</div>
    {/if}
    {if $myviews.pagination_js}
    <script>
        addLoadEvent(function() {literal}{{/literal}
            {$myviews.pagination_js|safe}
            removeElementClass('myviews_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
    </div>
{else}
    {str tag='noviewstosee' section='group'}
{/if}
