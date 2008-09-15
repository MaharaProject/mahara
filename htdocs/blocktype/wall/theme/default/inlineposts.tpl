<div id="wall">
    {if $wallmessage}
        <p>{$wallmessage}</p>
    {/if}
    {if $wallposts}
        {foreach from=$wallposts item=wallpost}
            <div id="wallpost">
                {* TODO THIS BADLY NEEDS FORMATTING *}
                <div id="icon"><img src="{$WWWROOT}thumb.php?type=profileicon&maxwidth=50&maxheight=50&id={$wallpost->from}" /></div>
                <div id="userinfo">{$wallpost->displayname|escape}</div>
                <div id="text">{$wallpost->text|escape}</div>
                <div id="postedon">{$wallpost->postdate|format_date}</div>
                <div id="controls">
        {if $ownwall}
                    [ <a href="{$WWWROOT}blocktype/wall/wall.php?instance={$instanceid}&replyto={$wallpost->id}">{str tag='reply' section='blocktype.wall'}</a> ]
        {/if}
        {if $ownwall || $wallpost->from == $userid}
                    [ <a href="{$WWWROOT}blocktype/wall/deletepost.php?instance={$instanceid}&return={if $wholewall}wall{else}profile{/if}">
                        {str tag='delete' section='blocktype.wall'}
                    </a> ]
        {/if}
                </div>
            </div>
        {/foreach}
        {if !$wholewall}
            <a href="{$WWWROOT}blocktype/wall/wall.php?id={$instanceid}">{str tag='wholewall' section='blocktype.wall'}</a>
        {/if}
    {/if}
</div>
