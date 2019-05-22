{foreach from=$types item=type}
    <div class="card-option card-quarter">
        <button class="card placeholder btn-secondary" data-option="{$type.name}" title="{$type.description}" data-blockid="{$blockid}">
            <div class="icon icon-lg icon-{$type.cssicon}"></div>
            <div>{$type.title}</div>
        </button>
    </div>
{/foreach}
