<div class="panel-body">
    <p class="detail">{$description}</p>
    {if $tags}
    <p class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</p>
    {/if}

    {if $tasks.data}
        <div id="tasklist_{$blockid}" class="list-group list-unstyled mbl">
            {$tasks.tablerows|safe}
        </div>
        {if $tasks.pagination}
        <div id="plans_page_container_{$blockid}" class="hidden">
            {$tasks.pagination|safe}
        </div>
        <script>
        addLoadEvent(function() {literal}{{/literal}
            {$tasks.pagination_js|safe}
            removeElementClass('plans_page_container_{$blockid}', 'hidden');
        {literal}}{/literal});
        </script>
        {/if}
    {else}
        <p class="lead text-small pll">{str tag='notasks' section='artefact.plans'}</p>
    {/if}
</div>