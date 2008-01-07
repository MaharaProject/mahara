{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
{if $topic->canedit}
<div>
<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag=edittopic section=interaction.forum}</a>
{if $moderator}
 | <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag=deletetopic section=interaction.forum}</a>
{/if}
</div>
{/if}
{if !$topic->forumsubscribed}{$topic->subscribe}{/if}
{if $topic->closed}
<div class="message">{str tag=topicisclosed section=interaction.forum}</div>
{/if}
{$posts}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
