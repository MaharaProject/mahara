{if $blocktypes}
    {if $javascript}
    <div class='btn-group-vertical'>
    {/if}
        {foreach from=$blocktypes item=blocktype}{strip}
            <a class="{if !$accessible} not-accessible{/if} blocktype-drag blocktypelink btn btn-primary hide-title-collapsed" href="#" title="{$blocktype.title}">
                <input type="radio" id="blocktype-list-radio-{$blocktype.name}" class="blocktype-radio" name="blocktype" value="{$blocktype.name}">
                <span class="icon icon-{$blocktype.cssicon} {$blocktype.cssicontype}" title="{$blocktype.title}" role="presentation" aria-hidden="true"></span>
                <label for="blocktype-list-radio-{$blocktype.name}" class="blocktypetitle title">
                    <span class="hidden">{$blocktype.title}</span>
                </label>
                <span class="sr-only">({$blocktype.description})</span>
            </a>{/strip}
        {/foreach}
    {if $javascript}
    </div>
    {/if}
{/if}
