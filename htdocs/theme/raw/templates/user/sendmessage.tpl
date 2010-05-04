{auto_escape off}
{include file="header.tpl"}

{include file="user/simpleuser.tpl" user=$user}

{if $replyto}
<h4>{$replyto->subject|escape}:</h4>
<br>
{foreach from=$replyto->lines item=line}
{$line|escape}<br>
{/foreach}
{/if}

{$form}

{include file="footer.tpl"}
{/auto_escape}
