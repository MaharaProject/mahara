{foreach from=$links item=link}        <link rel="{$link->type}"{if $link->mimetype != ''} type="{$link->mimetype}"{/if} href="{$link->id}"{if $link->display_order} leap2:display_order="{$link->display_order}"{/if}/>
{/foreach}
