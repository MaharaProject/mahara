{include file="header.tpl"}
<div class="ptl pbl">
    {$form|safe}
</div>
{if $message}
<div class="no-result">{$message|safe}</div>
{/if}
{if $results}
<div class="">
    <div id="friendslist" class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
        {$results.tablerows|safe}
    </div>
</div>
{$results.pagination|safe}
{/if}
{include file="footer.tpl"}
