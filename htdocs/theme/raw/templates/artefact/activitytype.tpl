{if $item->read && $item->type == 'usermessage'}
    <span class="fa fa-envelope type-icon"></span>
    <span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
{else}
    {if $item->type == 'usermessage'}
        <span class="fa fa-envelope type-icon"></span>
    {elseif $item->type == 'institutionmessage'}
        <span class="fa fa-university type-icon"></span>
    {elseif $item->type == 'feedback'}
        <span class="fa fa-comments type-icon"></span>
    {elseif $item->type == 'annotationfeedback'}
        <span class="fa fa-comments-o type-icon"></span>
    {else}
        <span class="fa fa-wrench type-icon"></span>
    {/if}

    <span class="sr-only">{$item->strtype}</span>
{/if}
