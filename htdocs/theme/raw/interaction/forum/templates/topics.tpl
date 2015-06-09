{foreach from=$topics item=topic}
{$objectionableclass = ''}
{if $topic->containsobjectionable}
{$objectionableclass = 'containobjectionable'}
{/if}

{if $sticky}
<tr class="stickytopic {$objectionableclass}">
{else}
<tr class="{$objectionableclass}">
    {/if}
    <td class="narrow">
        {if $membership && (!$forum->subscribed || $moderator)}
        <input type="checkbox" name="checked[{$topic->id}]" class="topic-checkbox mtl">
        {/if}
    </td>
    <td class="topic">
        <div class="text-inline">
            {if $topic->closed}
            <span class="icon icon-lock icon-lg prs"></span>
            <span class="sr-only">{str tag="Closed" section="interaction.forum"}</span>
            {/if}

            {if $topic->subscribed}
            <span class="icon icon-star icon-lg prs text-success"></span>
            <span class="sr-only">{str tag="Subscribed" section="interaction.forum"}</span>
            {/if}
        </div>
        <h3 class="title text-inline">
            <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">
                {$topic->subject}
            </a>
            <span class="metadata text-small">
                {str tag=by section=view}
                <a href="{profile_url($topic->poster)}" class="forumuser{if in_array($topic->poster, $groupadmins)} groupadmin{elseif $topic->moderator} moderator{/if}">
                    {$topic->poster|display_name:null:true}
                </a>
            </span>
        </h3>
        <div class="text-small threaddetails mtm">


            <p>{$topic->body|str_shorten_html:50:true:false|safe}</p>
        </div>
    </td>
    <td class="postscount text-center">
        {$topic->postcount}
    </td>
    <td class="lastposttd metadata">
            {if !$topic->lastpostdeleted}
            <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}&post{$topic->lastpost}">
                {$topic->lastposttime}
            </a>
            {if $publicgroup}
            <a href="{$topic->feedlink}" class="pls">
                <span class="icon-rss icon text-orange"></span>
            </a>
            {/if}
        <p>
            {str tag=by section=view}
            <a href="{profile_url($topic->lastposter)}" {if in_array($topic->lastposter, $groupadmins)} class="groupadmin"{elseif $topic->lastpostermoderator} class="moderator"{/if}>{$topic->lastposter|display_name:null:true}
            </a>
            {/if}
        </p>

    </td>
    <td class="control-buttons">
    {if $moderator}
        <div class="btn-group">
            <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}&amp;returnto=view" class="btn btn-default btn-xs" title="{str tag="edit"}">
                <span class="icon icon-pencil"></span>
                <span class="sr-only">
                    {str tag=edittopicspecific section=interaction.forum arg1=$topic->subject}
                </span>
            </a>
            <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}&amp;returnto=view" class="btn btn-default btn-xs" title="{str tag="delete"}">
                <span class="icon icon-trash text-danger"></span>
                <span class="sr-only">
                    {str tag=deletetopicspecific section=interaction.forum arg1=$topic->subject}
                </span>
            </a>
        </div>
    </td>
    {/if}
</tr>
{/foreach}
