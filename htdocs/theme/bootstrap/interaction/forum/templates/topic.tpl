{include file="header.tpl"}

{if $membership}
	<div id="forumbtns">
	{if $topic->canedit}
	<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}" class="btn editforum">{str tag=edittopic section=interaction.forum}</a>
		{if $moderator}
		<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}" class="btn deletetopic">{str tag=deletetopic section=interaction.forum}</a>
		{/if}
	{/if}
	{if !$topic->forumsubscribed}
		{$topic->subscribe|safe}
	{/if}
	</div>
{/if}
<h2>{str tag=nameplural section=interaction.forum} &gt; <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">{$topic->forumtitle}</a></h2>
<h3>{$topic->subject}</h3>

{if $topic->closed}
	<div class="message closed">{str tag=topicisclosed section=interaction.forum}</div>
{else}
    {if $lastpostid}<div class="postbtns"><a href="{$WWWROOT}interaction/forum/editpost.php?parent={$lastpostid}" class="btn"><span class="btn-reply">{str tag="Reply" section=interaction.forum}</span></a></div>{/if}
{/if}

{$posts|safe}

{if !$topic->closed}
    {if $lastpostid}<div class="postbtns"><a href="{$WWWROOT}interaction/forum/editpost.php?parent={$lastpostid}" class="btn"><span class="btn-reply">{str tag="Reply" section=interaction.forum}</span></a></div>{/if}
{/if}

<div class="center">{$pagination|safe}</div>
{include file="footer.tpl"}

