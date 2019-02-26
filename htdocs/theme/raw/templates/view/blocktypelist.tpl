{if $blocktypes}
    {if $javascript}
    <div class='btn-group-vertical'>
    {/if}
        {foreach from=$blocktypes item=blocktype}
        {* TODO at this point we have now $blocktype.singleonly *}
            <a class="blocktype-drag blocktypelink btn btn-secondary hide-title-collapsed text-left" href="#" title="{$blocktype.description}">
                <span class="icon icon-arrows icon-sm left move-indicator" role="presentation" aria-hidden="true"></span>
                <input type="radio" id="blocktype-list-radio-{$blocktype.name}" class="blocktype-radio" name="blocktype" value="{$blocktype.name}">
                {if $blocktype.cssicon}{*
                    *}<span class="icon icon-{$blocktype.cssicon} block-icon" title="{$blocktype.title}" role="presentation" aria-hidden="true"></span>{*
                *}{else}{*
                    *}<img class="icon block-icon" src="{$blocktype.thumbnail_path}" title="{$blocktype.description}" alt="{$blocktype.description}" width="14" height="14">{*
                *}{/if}
                <label for="blocktype-list-radio-{$blocktype.name}" class="blocktypetitle title">{$blocktype.title}</label>
                <span class="sr-only">({$blocktype.description})</span>
            </a>
        {/foreach}
    {if $javascript}
    </div>
    {/if}
{/if}
