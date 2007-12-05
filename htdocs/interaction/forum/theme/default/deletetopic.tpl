{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<p>
<a href="{$breadcrumbs[0][0]|escape}">{$breadcrumbs[0][1]|escape}</a>
{foreach from=$breadcrumbs[1] item=item}
&raquo <a href="{$item[0]|escape}">{$item[1]|escape}</a>
{/foreach}
</p>

<h2>{$forum|escape}</h2>
<h3>{$heading|escape}</h3>

<h4>{$topic->subject|escape}</h4>
<h5>{$topic->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$topic->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&maxsize=100&id={$topic->poster}" alt=""></div>
<p>{$topic->body}</p>

{$deleteform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
