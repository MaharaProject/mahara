{if $blocktypes}
    {if $javascript}
    <div class='btn-group-vertical'>
    {/if}
        {foreach from=$blocktypes item=blocktype}
        {* TODO at this point we have now $blocktype.singleonly *}
            <a class="blocktype-drag blocktypelink btn btn-default hide-title-collapsed text-left" href="#" title="{$blocktype.description}">
                <span class="icon icon-arrows icon-sm left move-indicator"></span>
                <input type="radio" id="blocktype-list-radio-{$blocktype.name}" class="blocktype-radio" name="blocktype" value="{$blocktype.name}">
                <span class="icon icon-{$blocktype.name} block-icon" title="{$blocktype.title}"></span>
                <label for="blocktype-list-radio-{$blocktype.name}" class="blocktypetitle title">{$blocktype.title}</label>
                <span class="sr-only">({$blocktype.description})</span>
            </a>
        {/foreach}
    {if $javascript}
    </div>
    {/if}
{/if}
