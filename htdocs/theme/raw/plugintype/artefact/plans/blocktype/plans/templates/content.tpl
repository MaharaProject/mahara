<div class="panel-body flush">
    <p>{$description}</p>
    
    {if $tags}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}
    </div>
    {/if}

    {if $tasks.data}
        <div id="tasklist_{$blockid}" class="list-group list-unstyled">
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
        <div class="lead text-center content-text">{str tag='notasks' section='artefact.plans'}</div>
    {/if}
</div>