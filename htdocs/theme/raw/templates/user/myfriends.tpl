{include file="header.tpl"}
{$form|safe}
{if $message}
<p class="no-results">
    {$message|safe}
</p>
{/if}
{if $results}
<div id="friendslist" class="card-items js-masonry" data-masonry-options='{literal}{ "itemSelector": ".card" }{/literal}'>
    {$results.tablerows|safe}
</div>
<div class="card-pagination">
{$results.pagination|safe}
</div>
{/if}

{include file="footer.tpl"}
