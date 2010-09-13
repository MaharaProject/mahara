{foreach from=$links item=link}        <link rel="{$link->type|escape}" href="{$link->id}"{if $link->display_order} leap2:display_order="{$link->display_order|escape}"{/if}/>
{/foreach}
