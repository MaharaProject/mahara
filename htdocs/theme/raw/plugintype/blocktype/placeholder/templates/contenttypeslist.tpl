{foreach from=$types item=type}
    <div class="card-option card-quarter">
        <div class="card placeholder btn-secondary" data-option="{$type.name}" title="{$type.description}">
            <div class="icon icon-lg icon-{$type.cssicon}"></div>
            <div>{$type.title}</div>
        </div>
    </div>
{/foreach}
