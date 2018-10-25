{if $tags}<div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags view=$view}</div>{/if}
<div>
    <ul id="tasklist" class="list-group list-unstyled">
        {$tasks.tablerows|safe}
    </ul>
</div>

<div id="plans_page_container">
    {$tasks.pagination|safe}
</div>
<script>
    jQuery(function($) {literal}{{/literal}
        {$tasks.pagination_js|safe}
        $('#plans_page_container_{$blockid}_plan{$tasks.planid}').removeClass('hidden');
    {literal}}{/literal});
</script>
{if $license}
<div class="license">
{$license|safe}
</div>
{/if}
