{foreach from=$topics item=topic}
{if $sticky}
<tr class="stickytopic">
{else}
<tr class="{cycle values='r0,r1'}">
{/if}
    <td>
    {if $topic->closed}<img src="{$closedicon|escape}" alt="{str tag="Closed" section="interaction.forum"}">{/if}
    {if $topic->subscribed}<img src="{$subscribedicon|escape}" alt="{str tag="Subscribed" section="interaction.forum"}">{/if}
    </td>
    <td class="narrow">
    {if $membership && (!$forum->subscribed || $moderator)}
        <input type="checkbox" name="checked[{$topic->id|escape}]" class="topic-checkbox">
    {/if}
    </td>
    <td>
        {if $moderator}
        <div class="s btn-spacer fr">
            <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}&amp;returnto=view" class="btn-edit">{str tag="edit"}</a>
            <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}&amp;returnto=view" class="btn-del">{str tag="delete"}</a>
        </div>
        {/if}
        <div><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a></div>
        <div class="s">{$topic->body}</div>
    </td>
    <td class="s">
        <a href="{$WWWROOT}user/view.php?id={$topic->poster}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$topic->poster}" alt=""></a>
        <a href="{$WWWROOT}user/view.php?id={$topic->poster}" class="forumuser{if in_array($topic->poster, $groupadmins)} groupadmin{elseif $topic->moderator} moderator{/if}">{$topic->poster|display_name:null:true|escape}</a>
    </td>
    <td class="center">{$topic->postcount|escape}</td>
    <td class="s">
    {if !$topic->lastpostdeleted}
    <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}#post{$topic->lastpost}">{$topic->lastposttime}</a> {str tag=by section=view}
    <a href="{$WWWROOT}user/view.php?id={$topic->lastposter}" {if in_array($topic->lastposter, $groupadmins)} class="groupadmin"{elseif $topic->lastpostermoderator} class="moderator"{/if}>{$topic->lastposter|display_name:null:true|escape}</a>
    {/if}
    </td>
</tr>
{/foreach}
