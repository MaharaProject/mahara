{foreach from=$types item=type}
    <div class="card-option card-quarter" id="row_{$type.name}">
        <button class="card placeholder btn-secondary" data-option="{$type.name}" title="{$type.description}" data-blockid="{$blockid}">
            <div class="icon icon-lg icon-{$type.cssicon} {$type.cssicontype}"></div>
            <div>{$type.title}{if $type.count} ({$type.count}){/if}</div>
        </button>
    </div>
{/foreach}
