{if $USERGROUPS.count > 0}
    <div id="usergroupstable" class="fullwidth listing">
        {$USERGROUPS.tablerows|safe}
    </div>
    {if $USERGROUPS.pagination}
        <div id="usergroups_page_container" class="d-none center">{$USERGROUPS.pagination|safe}</div>
    {/if}
    {if $USERGROUPS.pagination_js}
    <script>
        var cleanurlid = {$userid};
        jQuery(function($) {literal}{{/literal}
            {$USERGROUPS.pagination_js|safe}
            $('#usergroups_page_container').removeClass('d-none');
        {literal}}{/literal});
    </script>
    {/if}
{else}
    <div class="card-body">
        <p class="no-results-small">{str tag='notinanygroups' section='group'}</p>
    </div>
{/if}
