<div class="list-group">
    {foreach from=$items item=item}
    <div class="list-group-item flush">
        <h5 class="text-inline list-group-item-heading"><a href="{$item->homeurl}">{$item->name}</a></h5>
        <span class="text-small text-midtone"> ({$item->roledisplay})</span>
    </div>
    {/foreach}
</div>
