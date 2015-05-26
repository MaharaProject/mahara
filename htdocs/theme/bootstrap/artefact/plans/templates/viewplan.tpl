{if $tags}<p class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</p>{/if}
<div class="">
    <ul id="tasklist" class="list-group list-unstyled mbl">
        {$tasks.tablerows|safe}
    </ul>
</div>

<div id="plans_page_container">
    {$tasks.pagination|safe}
</div>

{if $license}
<div class="resumelicense">
{$license|safe}
</div>
{/if}
