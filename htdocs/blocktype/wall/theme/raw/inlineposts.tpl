<div id="wall" class="wall">
    {if $wallmessage}
        <p class="message">{$wallmessage}</p>
    {/if}
    {if $wallposts}
        {foreach from=$wallposts item=wallpost}
            <div class="wallpost{if $wallpost->private} private{/if}">
            {if $wallpost->deletable}
                    <a href="{$WWWROOT}blocktype/wall/deletepost.php?postid={$wallpost->postid}&return={if $wholewall}wall{else}profile{/if}" class="wallpostdelete">{str tag='delete' section='blocktype.wall'}</a>
        {/if}
                <div class="userinfo"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxwidth=25&amp;maxheight=25&amp;id={$wallpost->from}" alt="Profile Picture"><a href="{$WWWROOT}user/view.php?id={$wallpost->userid}">{$wallpost->displayname}</a><span class="postedon"> - {$wallpost->postdate|format_date}</span></div>
                <div class="text">{$wallpost->text|parse_bbcode|safe}</div>
                <div class="controls">
       {* {if $ownwall}
                    <a href="{$WWWROOT}blocktype/wall/wall.php?instance={$instanceid}&amp;replyto={$wallpost->id}" class="btn-reply">{str tag='reply' section='blocktype.wall'}</a>
        {/if}*}
        
                </div>
            </div>
        {/foreach}
    {/if}
</div>
{if !$wholewall}
    <div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}blocktype/wall/wall.php?id={$instanceid}">{str tag='wholewall' section='blocktype.wall'} &raquo;</a></div>
{/if}
