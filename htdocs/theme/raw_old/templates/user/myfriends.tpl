{include file="header.tpl"}
{$form|safe}
{if $message}
<p class="no-results">
    {$message|safe}
</p>
{/if}
{if $results}
<div id="friendslist" class="panel-items">
    {$results.tablerows|safe}
</div>
<div class="panel-pagination">
{$results.pagination|safe}
</div>
{/if}
{include file="footer.tpl"}
