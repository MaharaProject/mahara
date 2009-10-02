{include file="header.tpl"}

<h2><a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">{$topic->forumtitle|escape}</a></h2>
<h3>{$topic->subject|escape}</h3>
{if $membership}
	<div id="forumbtns" class="rbuttons">
	{if $topic->canedit}
	<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}" class="btn btn-edittopic s">{str tag=edittopic section=interaction.forum}</a>
		{if $moderator}
		<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}" class="btn btn-deletetopic s">{str tag=deletetopic section=interaction.forum}</a>
		{/if}
	{/if}
	{if !$topic->forumsubscribed}
		{$topic->subscribe}
	{/if}
	</div>
{/if}

{if $topic->closed}
	<div class="message closed">{str tag=topicisclosed section=interaction.forum}</div>
{/if}
{$posts}

{include file="footer.tpl"}
