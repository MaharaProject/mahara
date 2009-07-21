{include file="header.tpl"}

<h2><a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">{$topic->forumtitle|escape}</a> - {$topic->subject|escape}</h2>
{if $membership}
	<div class="rbuttons pagetabs">
	{if $topic->canedit}
	<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}" id="btn-edittopic" class="btn-edit s">{str tag=edittopic section=interaction.forum}</a>
		{if $moderator}
		<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}" id="btn-deletetopic" class="delete btn-del s">{str tag=deletetopic section=interaction.forum}</a>
		{/if}
	{/if}
	{if !$topic->forumsubscribed}
		{$topic->subscribe}
	{/if}
	</div>
{/if}

{if $topic->closed}
	<div class="message">{str tag=topicisclosed section=interaction.forum}</div>
{/if}
{$posts}

{include file="footer.tpl"}
