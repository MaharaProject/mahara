{foreach from=$links item=link}        <link rel="{$link->type}" href="{$link->id}"{if $link->display_order} leap:display_order="{$link->display_order}"{/if}/>
{/foreach}
