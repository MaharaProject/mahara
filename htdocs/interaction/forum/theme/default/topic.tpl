{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}


{if $membership}
	<div class="forumaddicon fr">
	{if $topic->canedit}
	<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}" id="btn-edittopic">{str tag=edittopic section=interaction.forum}</a>
		{if $moderator}
		<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}" id="btn-deletetopic" class="delete">{str tag=deletetopic section=interaction.forum}</a>
		{/if}
	{/if}
	{if !$topic->forumsubscribed}
		{$topic->subscribe}
	{/if}
	</div>
{/if}
<h2>{$subheading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

{if $topic->closed}
	<div class="message">{str tag=topicisclosed section=interaction.forum}</div>
{/if}
{$posts}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
