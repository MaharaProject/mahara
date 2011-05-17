{foreach from=$topics item=topic}
{if $sticky}
<tr class="stickytopic">
{else}
<tr class="{cycle values='r0,r1'}">
{/if}
    <td>
    {if $topic->closed}<img src="{$closedicon}" alt="{str tag="Closed" section="interaction.forum"}">{/if}
    {if $topic->subscribed}<img src="{$subscribedicon}" alt="{str tag="Subscribed" section="interaction.forum"}">{/if}
    </td>
    <td class="narrow">
    {if $membership && (!$forum->subscribed || $moderator)}
        <input type="checkbox" name="checked[{$topic->id}]" class="topic-checkbox">
    {/if}
    </td>
    <td>
        <h4><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">{$topic->subject}</a>{if $publicgroup}<a href="{$topic->feedlink}"><img class="feedicon" src="{theme_url filename='images/rss_small.gif'}"></a>{/if}</h4>
        <div class="s">{$topic->body|str_shorten_html:50:true:false|safe}</div>
    </td>
    <td class="s">
        <a href="{$WWWROOT}user/view.php?id={$topic->poster}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$topic->poster}" alt=""></a>
        <a href="{$WWWROOT}user/view.php?id={$topic->poster}" class="forumuser{if in_array($topic->poster, $groupadmins)} groupadmin{elseif $topic->moderator} moderator{/if}">{$topic->poster|display_name:null:true}</a>
    </td>
    <td class="center s">{$topic->postcount}</td>
    <td class="s">
    {if !$topic->lastpostdeleted}
    <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}#post{$topic->lastpost}">{$topic->lastposttime}</a> {str tag=by section=view}
    <a href="{$WWWROOT}user/view.php?id={$topic->lastposter}" {if in_array($topic->lastposter, $groupadmins)} class="groupadmin"{elseif $topic->lastpostermoderator} class="moderator"{/if}>{$topic->lastposter|display_name:null:true}</a>
    {/if}
    </td>
    {if $moderator}
	<td>
    	<div class="s btn-spacer fr">
            <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}&amp;returnto=view" class="btn-big-edit" title="{str tag="edit"}"></a>
            <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}&amp;returnto=view" class="btn-big-del" title="{str tag="delete"}"></a>
        </div>
    </td>
    {/if}
</tr>
{/foreach}
