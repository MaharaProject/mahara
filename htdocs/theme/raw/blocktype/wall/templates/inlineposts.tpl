<div id="wall" class="wall">
    {if $wallmessage}
        <div class="message">{$wallmessage}</div>
    {/if}
    {if $wallposts}
        <ul>
        {foreach from=$wallposts item=wallpost}
            <li class="wallpost{if $wallpost->private} private{/if} {cycle name=rows values='r0,r1'}">
                <div class="userinfo"><img src="{profile_icon_url user=$wallpost maxheight=25 maxwidth=25}" alt="{str tag=profileimagetext arg1=$wallpost|display_default_name}"><a href="{$wallpost->profileurl}">{$wallpost->displayname}</a> - <span class="postedon">{$wallpost->postdate|format_date}</span></div>
                <div class="detail">{$wallpost->text|safe|clean_html}</div>
                <div class="controls">
       {* {if $ownwall}
                    <a href="{$WWWROOT}blocktype/wall/wall.php?instance={$instanceid}&amp;replyto={$wallpost->id}" class="btn-reply">{str tag='reply' section='blocktype.wall'}</a>
        {/if}*}
                {if $wallpost->deletable}
                    <div class="wallpostdeletebutton"><a href="{$WWWROOT}blocktype/wall/deletepost.php?postid={$wallpost->postid}&return={if $wholewall}wall{else}profile{/if}" class="btn-big-del">{str tag='delete' section='blocktype.wall'}</a></div>
                {/if}
                </div>
            </li>
        {/foreach}
        </ul>
    {/if}
</div>
{if !$wholewall}
    <div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}blocktype/wall/wall.php?id={$instanceid}">{str tag='wholewall' section='blocktype.wall'} &raquo;</a></div>
{/if}
