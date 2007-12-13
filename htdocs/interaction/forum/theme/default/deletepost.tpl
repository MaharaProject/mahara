{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$topicsubject|escape} - {$heading|escape}</h2>

{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

<h4>{$post->subject|escape}</h4>
<h5>{$post->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$post->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$post->poster}" alt=""></div>
<p>{$post->body}</p>

{$deleteform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
