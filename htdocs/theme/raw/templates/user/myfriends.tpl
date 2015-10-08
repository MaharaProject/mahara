{include file="header.tpl"}
{$form|safe}
{if $message}
<p class="no-results">
    {$message|safe}
</p>
{/if}
{if $results}
<div id="friendslist" class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
    {$results.tablerows|safe}
</div>
{$results.pagination|safe}
{/if}
{include file="footer.tpl"}
