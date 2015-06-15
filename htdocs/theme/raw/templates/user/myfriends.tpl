{include file="header.tpl"}
{$form|safe}
{if $message}
<div class="no-result mtxl ptxl text-center">
	<p class="ptxl lead">{$message|safe}</p>
</div>
{/if}
{if $results}
<div id="friendslist" class="mtl panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
    {$results.tablerows|safe}
</div>
{$results.pagination|safe}
{/if}
{include file="footer.tpl"}
