{if $USERGROUPS.count > 0}
    <div id="usergroupstable" class="fullwidth listing">
        {$USERGROUPS.tablerows|safe}
    </div>
    {if $USERGROUPS.pagination}
        <div id="usergroups_page_container" class="hidden center">{$USERGROUPS.pagination|safe}</div>
    {/if}
    {if $USERGROUPS.pagination_js}
    <script>
        var cleanurlid = {$userid};
        addLoadEvent(function() {literal}{{/literal}
            {$USERGROUPS.pagination_js|safe}
            removeElementClass('usergroups_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
{else}
    <div class="panel-body">
        <p class="no-results-small">{str tag='notinanygroups' section='group'}</p>
    </div>
{/if}
