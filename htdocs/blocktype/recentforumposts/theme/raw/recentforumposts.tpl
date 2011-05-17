                <div id="recentforumpostsblock">
                {if $foruminfo}
                <table class="fullwidth">
                {foreach from=$foruminfo item=postinfo}
                <tr class="{cycle values='r0,r1'}">
                	<td><strong><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic}#post{$postinfo->id}">{$postinfo->topicname}</a></strong><br />
                    <div class="s">{$postinfo->body|str_shorten_html:100:true|safe}</div></td>
                    <td class="valign s center"><a href="{$WWWROOT}user/view.php?id={$postinfo->poster}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$postinfo->poster}" alt=""><br />{$postinfo->poster|display_name}</a></td>
            	</tr>
                {/foreach}
        		</table>
                {else}
                <table class="fullwidth"><tr class="{cycle values='r0,r1'}">
                	<td align="center">{str tag=noforumpostsyet section=interaction.forum}</td>
                    </tr>
                </table>
                {/if}
                <div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}interaction/forum/?group={$group->id}" target="_blank">{str tag=gotoforums section=interaction.forum} &raquo;</a></div>
                <div class="cb"></div>
                </div>
