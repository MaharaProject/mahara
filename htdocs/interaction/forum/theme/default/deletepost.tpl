{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<p>
<a href="{$breadcrumbs[0][0]|escape}">{$breadcrumbs[0][1]|escape}</a>
{foreach from=$breadcrumbs[1] item=item}
&raquo <a href="{$item[0]|escape}">{$item[1]|escape}</a>
{/foreach}
</p>

<h2>{$topicsubject|escape}</h2>
<h3>{$heading|escape}</h3>

<h4>{$post->subject|escape}</h4>
<h5>{$post->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$post->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&maxsize=100&id={$post->poster}" alt=""></div>
<p>{$post->body}</p>

{$deleteform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
