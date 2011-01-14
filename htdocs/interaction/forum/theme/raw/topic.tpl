{include file="header.tpl"}

<h2>{str tag=nameplural section=interaction.forum} &gt; <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">{$topic->forumtitle}</a></h2>
<h4>{$topic->subject}</h4>
{if $membership}
	<div id="forumbtns" class="rbuttons">
	{if $topic->canedit}
	<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}" class="btn editforum">{str tag=edittopic section=interaction.forum}</a>
		{if $moderator}
		<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}" class="btn">{str tag=deletetopic section=interaction.forum}</a>
		{/if}
	{/if}
	{if !$topic->forumsubscribed}
		{$topic->subscribe|safe}
	{/if}
	</div>
{/if}

{if $topic->closed}
	<div class="message closed">{str tag=topicisclosed section=interaction.forum}</div>
{/if}
{$posts|safe}

{include file="footer.tpl"}

