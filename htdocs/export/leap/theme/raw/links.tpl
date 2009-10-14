{foreach from=$links item=link}        <link rel="{$link->type|escape}" href="{$link->id|escape}"{if $link->display_order} leap:display_order="{$link->display_order|escape}"{/if}/>
{/foreach}
