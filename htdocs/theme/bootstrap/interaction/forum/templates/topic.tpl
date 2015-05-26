{include file="header.tpl"}
<h2 class="pbl mbl">
    {str tag=nameplural section=interaction.forum} &gt; 
    <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">
        {$topic->forumtitle}
    </a>
</h2>

{if $membership}
<div id="forumbtns" class="text-right btn-top-right">
    {if $topic->canedit}
    <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}" class="btn btn-default editforum">
        <span class="fa fa-pencil"></span>
        {str tag=edittopic section=interaction.forum}
    </a>
    {if $moderator}
    <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}" class="btn btn-danger deletetopic">
        <span class="fa fa-trash"></span>
        {str tag=deletetopic section=interaction.forum}
    </a>
    {/if}
    {/if}
    
    {if !$topic->forumsubscribed}
    {$topic->subscribe|safe}
    {/if}
</div>
{/if}

<h3>{$topic->subject}</h3>
{if $topic->closed}
<div class="message closed">
    {str tag=topicisclosed section=interaction.forum}
</div>

{else}
    {if $lastpostid}
    <div class="postbtns">
        <a href="{$WWWROOT}interaction/forum/editpost.php?parent={$lastpostid}" class="btn">
            <span class="fa fa-reply"></span>
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
        <span class="fa fa-reply"></span>
        {str tag="Reply" section=interaction.forum}
    </a>
</div>
{/if}
{/if}

<div>
    {$pagination|safe}
</div>
{include file="footer.tpl"}

