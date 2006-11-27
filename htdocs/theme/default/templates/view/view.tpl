{include file="header.tpl"}

<h2>{$TITLE}</h2>

{if $VIEWCONTENT}
   {$VIEWCONTENT}
{else}
   {str tag=viewviewnotallowed}
{/if}

{include file="footer.tpl"}
