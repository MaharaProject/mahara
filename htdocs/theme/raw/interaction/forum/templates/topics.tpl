{foreach from=$topics item=topic}
{$objectionableclass = ''}
{if $topic->containsobjectionable}
    {$objectionableclass = 'containobjectionable'}
{/if}
{if $sticky}
<tr class="stickytopic {$objectionableclass}">
{else}
<tr class="{cycle values='r0,r1'} {$objectionableclass}">
{/if}
    <td class="narrow center">
    {if $topic->closed}<img src="{$closedicon}" alt="{str tag="Closed" section="interaction.forum"}">{/if}
    {if $topic->subscribed}<img src="{$subscribedicon}" alt="{str tag="Subscribed" section="interaction.forum"}">{/if}
    </td>
    <td class="narrow">
    {if $membership && (!$forum->subscribed || $moderator)}
        <input type="checkbox" name="checked[{$topic->id}]" class="topic-checkbox">
    {/if}
    </td>
    <td class="topic">
        <h3 class="title"><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">{$topic->subject}</a>{if $publicgroup}<a href="{$topic->feedlink}"><img class="feedicon" src="{theme_image_url filename='feed'}"></a>{/if}</h3>
        <div class="detail">{$topic->body|str_shorten_html:50:true:false|safe}</div>
    </td>
    <td class="postertd">
        <a href="{profile_url($topic->poster)}" class="forumuser{if in_array($topic->poster, $groupadmins)} groupadmin{elseif $topic->moderator} moderator{/if}">{$topic->poster|display_name:null:true}</a>
    </td>
    <td class="postscount center">{$topic->postcount}</td>
    <td class="lastposttd">
    {if !$topic->lastpostdeleted}
    <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}&post{$topic->lastpost}">{$topic->lastposttime}</a> {str tag=by section=view}
    <a href="{profile_url($topic->lastposter)}" {if in_array($topic->lastposter, $groupadmins)} class="groupadmin"{elseif $topic->lastpostermoderator} class="moderator"{/if}>{$topic->lastposter|display_name:null:true}</a>
    {/if}
    </td>
    {if $moderator}
    <td class="right btns2">
        <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}&amp;returnto=view" class="btn-big-edit" title="{str tag="edit"}">
            {str tag=edittopicspecific section=interaction.forum arg1=$topic->subject}
        </a>
        <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}&amp;returnto=view" class="btn-big-del" title="{str tag="delete"}">
            {str tag=deletetopicspecific section=interaction.forum arg1=$topic->subject}
        </a>
    </td>
    {/if}
</tr>
{/foreach}
