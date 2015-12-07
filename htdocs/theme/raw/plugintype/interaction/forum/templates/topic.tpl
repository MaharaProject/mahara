{include file="header.tpl"}
{if $membership}
<div id="forumbtns" class="btn-top-right btn-group btn-group-top">
    {if !$topic->forumsubscribed}
    {$topic->subscribe|safe}
    {/if}
    {if $topic->canedit}
    <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}" class="btn btn-default editforum">
        <span class="icon icon-pencil icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag=edittopic section=interaction.forum}
    </a>
    {if $moderator}
    <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}" class="btn btn-default deletetopic">
        <span class="icon icon-trash icon-lg left text-danger" role="presentation" aria-hidden="true"></span>
        {str tag=deletetopic section=interaction.forum}
    </a>
    {/if}
    {/if}

</div>
{/if}

<h2 id="forum-topic" class="view-container">
    <span class="lead text-small text-inline link">
        <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">
            {$topic->forumtitle}
        </a> /
    </span>
    <br />
    {$topic->subject}
</h2>
<hr>

{if $topic->closed}
<div class="closed">
    {str tag=topicisclosed section=interaction.forum}
</div>

{else}
    {if $lastpostid}
    <div class="postbtns">
        <a href="{$WWWROOT}interaction/forum/editpost.php?parent={$lastpostid}" class="btn">
            <span class="icon icon-reply" role="presentation" aria-hidden="true"></span>
            {str tag="Reply" section=interaction.forum}
        </a>
    </div>
    {/if}
{/if}

{$posts|safe}

{if !$topic->closed}
{if $lastpostid}
<div class="postbtns">
    <a href="{$WWWROOT}interaction/forum/editpost.php?parent={$lastpostid}" class="btn">
        <span class="icon icon-reply" role="presentation" aria-hidden="true"></span>
        {str tag="Reply" section=interaction.forum}
    </a>
</div>
{/if}
{/if}

<div>
    {$pagination|safe}
</div>
{include file="footer.tpl"}
