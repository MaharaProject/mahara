{if $USERGROUPS}
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
    {str tag='notinanygroups' section='group'}
{/if}
