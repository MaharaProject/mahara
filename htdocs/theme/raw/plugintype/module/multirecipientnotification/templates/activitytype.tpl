{if $item->read && $item->type == 'usermessage'}
    <span class="icon icon-envelope type-icon"></span>
    <span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
{else}
    {if $item->type == 'usermessage'}
        <span class="icon icon-envelope type-icon"></span>
    {elseif $item->type == 'institutionmessage'}
        <span class="icon icon-university type-icon"></span>
    {elseif $item->type == 'feedback'}
        <span class="icon icon-comments type-icon"></span>
    {elseif $item->type == 'annotationfeedback'}
        <span class="icon icon-comments-o type-icon"></span>
    {else}
        <span class="icon icon-wrench type-icon"></span>
    {/if}

    <span class="sr-only">{$item->strtype}</span>
{/if}
