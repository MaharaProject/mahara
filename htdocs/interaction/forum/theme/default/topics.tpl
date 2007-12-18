{foreach from=$topics item=topic}
{if $sticky}
<tr class="r1">
{else}
<tr class="r0">
{/if}
    <td>
    {if $topic->closed}C{else}O{/if}
    {if $topic->subscribed}S{else}U{/if}
    </td>
    {if !$forum->subscribed || $moderator}
    <td>
    <input type="checkbox" name="checkbox[{$topic->id|escape}]">
    <input type="hidden" name="topics[{$topic->id|escape}]">
    </td>
    {/if}
    <td>
    <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a>
    <div>{$topic->body}</div>
    </td>
    <td>
    <a href="{$WWWROOT}user/view.php?id={$topic->poster}">
    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=20x20&amp;id={$topic->poster}" alt="">
    {$topic->poster|display_name|escape}
    </a>
    </td>
    <td>{$topic->count|escape}</td>
    <td>
    {if !$topic->lastpostdeleted}
    <a href="{$WWWROOT}user/view.php?id={$topic->lastposter}">{$topic->lastposter|display_name|escape}</a>
    <br>{$topic->lastposttime}
    {/if}
    </td>
    {if $moderator}
    <td>
    <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit"}</a>
    <br><a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete"}</a></td>
    {/if}
</tr>
{/foreach}
