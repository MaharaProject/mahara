{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$subheading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

<table id="forumtopicbtnwrap">
<tr>
{if $topic->canedit}
	<td align="right">
<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}" class="btn-editdk">{str tag=edittopic section=interaction.forum}</a>
{if $moderator}
<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}" class="btn-deletedk">{str tag=deletetopic section=interaction.forum}</a>
{/if}
{/if}
</td>
<td align="right">
{if !$topic->forumsubscribed}{$topic->subscribe}{/if}
</td>
</tr>
</table>
{if $topic->closed}
<div class="message">{str tag=topicisclosed section=interaction.forum}</div>
{/if}
{$posts}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
