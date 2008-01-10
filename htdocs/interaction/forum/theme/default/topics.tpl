{foreach from=$topics item=topic}
{if $sticky}
<tr class="r1">
{else}
<tr class="r0">
{/if}
    <td>
    {if $topic->closed}<img src="{$closedicon|escape}" alt="{str tag="Closed" section="interaction.forum"}">{/if}
    {if $topic->subscribed}<img src="{$subscribedicon|escape}" alt="{str tag="Subscribed" section="interaction.forum"}">{/if}
    </td>
    {if !$forum->subscribed || $moderator}
    <td>
    <input type="checkbox" name="checked[{$topic->id|escape}]" class="topic-checkbox">
    </td>
    {/if}
    <td>
    <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a>
    <div>{$topic->body|escape}</div>
    </td>
    <td>
    <a href="{$WWWROOT}user/view.php?id={$topic->poster}"
    {if $topic->poster == $groupowner} class="groupowner"
    {elseif $topic->moderator} class="moderator"
    {/if}
    >
    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$topic->poster}" alt="">
    {$topic->poster|display_name|escape}
    </a>
    </td>
    <td>{$topic->count|escape}</td>
    <td>
    {if !$topic->lastpostdeleted}
    <a href="{$WWWROOT}user/view.php?id={$topic->lastposter}"
    {if $topic->lastposter == $groupowner} class="groupowner"
    {elseif $topic->lastpostermoderator} class="moderator"
    {/if}
    >
    {$topic->lastposter|display_name|escape}</a>
    <br>
    <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}#post{$topic->lastpost}">{$topic->lastposttime}</a>
    {/if}
    </td>
    {if $moderator}
    <td>
    <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}&amp;returnto=view">{str tag="edit"}</a>
    <br><a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}&amp;returnto=view">{str tag="delete"}</a></td>
    {/if}
</tr>
{/foreach}
