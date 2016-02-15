<div class="list-group">
    {foreach from=$items item=item}
    <div class="list-group-item list-group-item-link">
        <a href="{$item->homeurl}">
            <h5 class="text-inline list-group-item-heading">{$item->name}</h5>
            <span class="text-small text-midtone"> ({$item->roledisplay})</span>
        </a>
    </div>
    {/foreach}
</div>