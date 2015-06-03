{if $blocktypes}
    {if $javascript}
    <div class='btn-group-vertical'>
    {/if}
        {foreach from=$blocktypes item=blocktype}
        {* TODO at this point we have now $blocktype.singleonly *}
            <a class="blocktype-drag blocktypelink btn btn-default hide-title-collapsed text-left" href="#">
                <span class="fa fa-arrows prs move-indicator"></span>
                <input type="radio" id="blocktype-list-radio-{$blocktype.name}" class="blocktype-radio" name="blocktype" value="{$blocktype.name}">
                <span class="fa fa-{$blocktype.name} icon" title="{$blocktype.title}"></span>
                <span class="sr-only">{$blocktype.description}</span>
                <label for="blocktype-list-radio-{$blocktype.name}" class="blocktypetitle title">{$blocktype.title}</label>
            </a>
        {/foreach}
    {if $javascript}
    </div>
    {/if}
{/if}
