{foreach from=$topics item=topic}
{cycle assign='objectionableclass' values='r0,r1'}
{if $topic->containsobjectionable}
{$objectionableclass .= ' containobjectionable'}
{/if}
{if !$topic->approved}
{$objectionableclass .= ' warning'}
{/if}

{if $sticky}
<tr class="stickytopic {$objectionableclass}">
{else}
<tr class=" {$objectionableclass}">
{/if}
    <td class="narrow">
        {if $membership && (!$forum->subscribed || $moderator)}
        <input type="checkbox" name="checked[{$topic->id}]" id="topic_{$topic->id}" class="topic-checkbox form-check">
        {/if}
    </td>
    <td class="topic">
        <h3 class="title text-inline">
            {if $membership && (!$forum->subscribed || $moderator)}
            <label for="topic_{$topic->id}">
            {/if}
            {if $topic->closed || $topic->subscribed || $sticky}
                {if $sticky}
                <span class="icon icon-asterisk icon-sm left text-midtone" role="presentation" aria-hidden="true"></span>
                {/if}

                {if $topic->closed}
                <span class="icon icon-lock icon-sm left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag="Closed" section="interaction.forum"}</span>
                {/if}

                {if $topic->subscribed}
                <span class="icon icon-star icon-sm left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag="Subscribed" section="interaction.forum"}</span>
                {/if}
            {/if}
            <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">
                {$topic->subject}
            </a>
            {if $membership && (!$forum->subscribed || $moderator)}
            </label>
            {/if}
            <span class="text-small text-midtone">
                {str tag=by section=view}
                {if $topic->deleteduser}
                    {str tag=deleteduser}
                {else}
                    <a href="{profile_url($topic->poster)}" class="forumuser{if in_array($topic->poster, $groupadmins)} groupadmin{elseif $topic->moderator} moderator{/if}">
                        {$topic->poster|display_name:null:true}
                    </a>
                {/if}
            </span>
        </h3>
        <div class="threaddetails">
            {$topic->body|str_shorten_html:50:true:false|safe}
        </div>
    </td>
    <td class="postscount text-center">
        {$topic->postcount}
    </td>
    <td class="lastposttd text-small">
            {if !$topic->lastpostdeleted}
            <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}&post{$topic->lastpost}">
                {$topic->lastposttime}
            </a>
            {if $publicgroup}
            <a href="{$topic->feedlink}">
                <span class="icon-rss icon mahara-rss-icon right" role="presentation" aria-hidden="true"></span>
            </a>
            {/if}
        <p>
            {str tag=by section=view}
            {if $topic->lastposterdeleteduser}
                {str tag=deleteduser}
            {else}
                <a href="{profile_url($topic->lastposter)}" {if in_array($topic->lastposter, $groupadmins)} class="groupadmin"{elseif $topic->lastpostermoderator} class="moderator"{/if}>{$topic->lastposter|display_name:null:true}
                </a>
            {/if}
        </p>

    </td>
    {if $moderator}
    <td class="control-buttons">
        <div class="btn-group">
            <a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id}&amp;returnto=view" class="btn btn-secondary btn-sm" title="{str tag="edit"}">
                <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">
                    {str tag=edittopicspecific section=interaction.forum arg1=$topic->subject}
                </span>
            </a>
            <a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id}&amp;returnto=view" class="btn btn-secondary btn-sm" title="{str tag="delete"}">
                <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">
                    {str tag=deletetopicspecific section=interaction.forum arg1=$topic->subject}
                </span>
            </a>
        </div>
    </td>
    {/if}
</tr>
{/foreach}
