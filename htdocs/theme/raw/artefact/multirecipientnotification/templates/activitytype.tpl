{if $item->read && $item->type == 'usermessage'}
    <span class="fa fa-envelope type-icon prxl plxl"></span>
    <span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
{else}
    {if $item->type == 'usermessage'}
        <span class="fa fa-envelope type-icon prxl plxl"></span>
    {elseif $item->type == 'institutionmessage'}
        <span class="fa fa-university type-icon prxl plxl"></span>
    {elseif $item->type == 'feedback'}
        <span class="fa fa-comments type-icon prxl plxl"></span>
    {elseif $item->type == 'annotationfeedback'}
        <span class="fa fa-comments-o type-icon prxl plxl"></span>
    {else}
        <span class="fa fa-wrench type-icon prxl plxl"></span>
    {/if}

    <span class="sr-only">{$item->strtype}</span>
{/if}
