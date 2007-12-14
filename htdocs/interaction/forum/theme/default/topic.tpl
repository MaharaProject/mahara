{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$topic->forumtitle|escape} - {$topic->subject|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
{if $moderator}
<div>
<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit"}</a>
 | <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete"}</a>
</div>
{/if}
{if !$topic->forumsubscribed}{$topic->subscribe}{/if}
<ul>
    <li>
        {$posts}
    </li>
</ul>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
