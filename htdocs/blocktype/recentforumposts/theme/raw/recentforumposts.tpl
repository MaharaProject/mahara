                <div>
                {if $foruminfo}
                {foreach from=$foruminfo item=postinfo}
                <h4><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic}#post{$postinfo->id}">{$postinfo->topicname}</a></h4>
                <div>
                  <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$postinfo->poster}" alt="">
                  <a href="{$WWWROOT}user/view.php?id={$postinfo->poster}">{$postinfo->poster|display_name|escape}</a>
                </div>
                <div>{$postinfo->body|str_shorten_html:100:true|safe}</div>
                {/foreach}
                {else}
                <p>{str tag=noforumpostsyet section=interaction.forum}</p>
                {/if}
                <p><a href="{$WWWROOT}interaction/forum/?group={$group->id}" target="_blank">{str tag=gotoforums section=interaction.forum} &raquo;</a></p>
                </div>
