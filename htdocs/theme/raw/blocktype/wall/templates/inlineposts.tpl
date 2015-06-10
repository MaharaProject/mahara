<div id="wall" class="wall">
    {if $wallmessage}
        <div class="lead text-small text-center ptl">{$wallmessage}</div>
    {/if}
    {if $wallposts}
        {foreach from=$wallposts item=wallpost}
            <div class="panel panel-default wallpost{if $wallpost->private} private{/if} {cycle name=rows values='r0,r1'}">
                <h4 class="panel-heading has-link">
                    <a href="{$wallpost->profileurl}" class="userinfo has-user-icon">
                        <span class="user-icon small-icon left">
                            <img src="{profile_icon_url user=$wallpost maxheight=60 maxwidth=60}" alt="{str tag=profileimagetext arg1=$wallpost|display_default_name}" />
                        </span>
                            {$wallpost->displayname} - <span class="postedon metadata">{$wallpost->postdate|format_date}</span>
                    </a>
                {if $wallpost->deletable}
                    <a href="{$WWWROOT}blocktype/wall/deletepost.php?postid={$wallpost->postid}&return={if $wholewall}wall{else}profile{/if}" class="panel-control panel-header-action">
                        <span class="icon icon-trash prs text-danger icon-lg pbs"></span>
                        <span class="sr-only">{str tag='delete' section='blocktype.wall'}</span>
                    </a>
                {/if}
                </h4>
                <div class="detail panel-body">{$wallpost->text|safe}</div>
                {* {if $ownwall}
                <div class="panel-footer">
                    <a href="{$WWWROOT}blocktype/wall/wall.php?instance={$instanceid}&amp;replyto={$wallpost->id}">
                        <span class="icon icon-reply mrs "></span>
                        <span class="pull">{str tag='reply' section='blocktype.wall'}</span>
                    </a>
                </div>
                {/if}*}
               
            </div>
        {/foreach}

    {/if}
</div>
{if !$wholewall}
    <a class="panel-footer" href="{$WWWROOT}blocktype/wall/wall.php?id={$instanceid}">
        {str tag='wholewall' section='blocktype.wall'} 
        <span class="icon icon-arrow-circle-right mls  pull-right"></span>
    </a>
{/if}