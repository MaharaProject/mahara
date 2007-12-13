{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$forum|escape} - {$heading|escape}</h2>

{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

<h4>{$topic->subject|escape}</h4>
<h5>{$topic->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$topic->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$topic->poster}" alt=""></div>
<p>{$topic->body}</p>

{$deleteform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
