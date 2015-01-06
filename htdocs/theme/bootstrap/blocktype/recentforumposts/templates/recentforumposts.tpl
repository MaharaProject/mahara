                <div id="recentforumpostsblock">
                {if $foruminfo}
                <table class="fullwidth">
                {foreach from=$foruminfo item=postinfo}
                <tr class="{cycle values='r0,r1'}">
                    <td><h3 class="title"><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic}&post={$postinfo->id}">{$postinfo->topicname}</a></h3>
                    <div class="detail">{$postinfo->body|str_shorten_html:100:true|safe}</div></td>
                    <td class="poster valign right"><a href="{profile_url($postinfo->author)}"><img src="{profile_icon_url user=$postinfo->author maxheight=20 maxwidth=20}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}"><br />{$postinfo->author|display_name}</a></td>
            	</tr>
                {/foreach}
        		</table>
                {else}
                <table class="fullwidth"><tr class="{cycle values='r0,r1'}">
                	<td align="center">{str tag=noforumpostsyet section=interaction.forum}</td>
                    </tr>
                </table>
                {/if}
                <div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}interaction/forum/index.php?group={$group->id}" target="_blank">{str tag=gotoforums section=interaction.forum} &raquo;</a></div>
                <div class="cb"></div>
                </div>
