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
    <td class="narrow center">
        {if $topic->closed}
        <span class="fa fa-lock"></span>
        <span class="sr-only">{str tag="Closed" section="interaction.forum"}</span>
        {/if}
        
        {if $topic->subscribed}
        <span class="fa fa-bookmark"></span>
        <span class="sr-only">{str tag="Subscribed" section="interaction.forum"}</span>
        {/if}
    </td>
    <td class="narrow">
        {if $membership && (!$forum->subscribed || $moderator)}
        <input type="checkbox" name="checked[{$topic->id}]" class="topic-checkbox">
        {/if}
    </td>
    <td class="topic">
        <h3 class="title">
            <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">
                {$topic->subject}
            </a>
            {if $publicgroup}
            <a href="{$topic->feedlink}">
                <span class="fa-rss fa"></span>
            </a>
            {/if}
        </h3>
        <div class="detail text-small mts">
            {$topic->body|str_shorten_html:50:true:false|safe}
        </div>
    </td>
    <td class="postertd">
        <a href="{profile_url($topic->poster)}" class="forumuser{if in_array($topic->poster, $groupadmins)} groupadmin{elseif $topic->moderator} moderator{/if}">{$topic->poster|display_name:null:true}
        </a>
    </td>
    <td class="postscount text-center">
        {$topic->postcount}
    </td>
    <td class="lastposttd">
        {if !$topic->lastpostdeleted}
        <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}&post{$topic->lastpost}">
            {$topic->lastposttime}
        </a> 
        {str tag=by section=view}
        <a href="{profile_url($topic->lastposter)}" {if in_array($topic->lastposter, $groupadmins)} class="groupadmin"{elseif $topic->lastpostermoderator} class="moderator"{/if}>{$topic->lastposter|display_name:null:true}
        </a>
        {/if}
    </td>
    {if $moderator}
    <td class="control-buttons">
        <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}&amp;returnto=view" class="btn btn-default btn-xs" title="{str tag="edit"}">
            <span class="fa fa-pencil"></span>
            <span class="sr-only">
                {str tag=edittopicspecific section=interaction.forum arg1=$topic->subject}
            </span>
        </a>
        <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}&amp;returnto=view" class="btn btn-danger btn-xs" title="{str tag="delete"}">
            <span class="fa fa-trash"></span>
            <span class="sr-only">
                {str tag=deletetopicspecific section=interaction.forum arg1=$topic->subject}
            </span>
        </a>
    </td>
    {/if}
</tr>
{/foreach}
