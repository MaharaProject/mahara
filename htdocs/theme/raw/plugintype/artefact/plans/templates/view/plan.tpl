{if $tags}<p class="tags">{str tag=tags}: {list_tags owner=$owner tags=$tags}</p>{/if}
<div class="">
    <ul id="tasklist" class="list-group list-unstyled">
        {$tasks.tablerows|safe}
    </ul>
</div>

<div id="plans_page_container">
    {$tasks.pagination|safe}
</div>
<script type="application/javascript">
    jQuery(function($) {literal}{{/literal}
        {$tasks.pagination_js|safe}
        $('#plans_page_container_{$blockid}_plan{$tasks.planid}').removeClass('d-none');
        {literal}}{/literal});
</script>

{if $license}
    <div class="license">
        {$license|safe}
    </div>
{/if}
