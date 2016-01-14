{if $item->read && $item->type == 'usermessage'}
    <span class="icon icon-envelope type-icon" role="presentation" aria-hidden="true"></span>
    <span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
{else}
    {if $item->type == 'usermessage'}
        <span class="icon icon-envelope type-icon" role="presentation" aria-hidden="true"></span>
    {elseif $item->type == 'institutionmessage'}
        <span class="icon icon-university type-icon" role="presentation" aria-hidden="true"></span>
    {elseif $item->type == 'feedback'}
        <span class="icon icon-comments type-icon" role="presentation" aria-hidden="true"></span>
    {elseif $item->type == 'annotationfeedback'}
        <span class="icon icon-comments-o type-icon" role="presentation" aria-hidden="true"></span>
    {elseif $i->type == 'wallpost'}
        <span class="icon icon-wall type-icon text-default left" role="presentation" aria-hidden="true"></span>
    {else}
        <span class="icon icon-wrench type-icon" role="presentation" aria-hidden="true"></span>
    {/if}

    <span class="sr-only">{$item->strtype}</span>
{/if}
