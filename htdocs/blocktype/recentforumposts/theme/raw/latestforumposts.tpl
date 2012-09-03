    {if $foruminfo}
        <table class="fullwidth" id="latestforumposts">
        {foreach from=$foruminfo item=postinfo}
            <tr class="{cycle values='r0,r1'}">
                <td><h5><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}&post={$postinfo->id}">{$postinfo->topicname}</a></h5><div class="s">{$postinfo->body|str_shorten_html:100:true|safe}</div></td>
                <td class="valign s right"><a href="{profile_url($postinfo->author)}"><img src="{profile_icon_url user=$postinfo->author maxheight=16 maxwidth=16}" alt=""> {$postinfo->author|display_name}</a>
                </td>
            </tr>
        {/foreach}
        </table>
    {else}
        <table class="fullwidth"><tr class="{cycle values='r0,r1'}">
                <td align="center">{str tag=noforumpostsyet section=interaction.forum}</td>
            </tr>
        </table>
    {/if}
    <div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}interaction/forum/?group={$group->id}">{str tag=gotoforums section=interaction.forum} &raquo;</a></div>
