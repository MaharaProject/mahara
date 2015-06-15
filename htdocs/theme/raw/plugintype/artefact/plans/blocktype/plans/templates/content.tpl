<div class="panel-body">
    <p class="detail">{$description}</p>
    {if $tags}
    <p class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</p>
    {/if}

    {if $tasks.data}
        <ul id="tasklist_{$blockid}" class="list-group list-unstyled mbl">
            {$tasks.tablerows|safe}
        </ul>
        {if $tasks.pagination}
        <div id="plans_page_container_{$blockid}" class="nojs-hidden-block ">
            {$tasks.pagination|safe}
        </div>
        {/if}
    {else}
        <p class="lead text-small pll">{str tag='notasks' section='artefact.plans'}</p>
    {/if}
</div>