{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
{if $moderator}
<div>
<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag=edittopic section=interaction.forum}</a>
 | <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag=deletetopic section=interaction.forum}</a>
</div>
{/if}
{if !$topic->forumsubscribed}{$topic->subscribe}{/if}
{if $topic->closed}
{str tag=topicisclosed section=interaction.forum}
{/if}
<ul>
    <li>
        {$posts}
    </li>
</ul>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
