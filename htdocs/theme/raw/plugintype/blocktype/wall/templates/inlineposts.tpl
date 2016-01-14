<div id="wall" class="wall">
    {if $wallmessage}
        <div class="lead text-small text-center">{$wallmessage}</div>
    {/if}
    {if $wallposts}
        {foreach from=$wallposts item=wallpost}
            <div class="panel {if $wallpost->private}panel-warning{else}panel-default{/if} wallpost">
                <h4 class="panel-heading has-link">
                    <a href="{$wallpost->profileurl}" class="userinfo has-user-icon">
                        <span class="user-icon left">
                            <img src="{profile_icon_url user=$wallpost maxheight=60 maxwidth=60}" alt="{str tag=profileimagetext arg1=$wallpost|display_default_name}" />
                        </span>
                            {$wallpost->displayname}<span class="postedon text-small text-midtone"> - {$wallpost->postdate|format_date}</span>
                    </a>
                {if $wallpost->deletable}
                    <a href="{$WWWROOT}blocktype/wall/deletepost.php?postid={$wallpost->postid}&return={if $wholewall}wall{else}profile{/if}" class="panel-control panel-header-action">
                        <span class="icon icon-trash left text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag='delete' section='blocktype.wall'}</span>
                    </a>
                {/if}
                </h4>
                <div class="panel-body">
                    {$wallpost->text|safe}
                    <div class="metadata">
                    {if $wallpost->private}<em class="privatemessage">{str tag='wallpostprivate' section='blocktype.wall'}</em>{/if}
                    </div>
                </div>
                {* {if $ownwall}
                <div class="panel-footer">
                    <a href="{$WWWROOT}blocktype/wall/wall.php?instance={$instanceid}&amp;replyto={$wallpost->id}">
                        <span class="icon icon-reply left" role="presentation" aria-hidden="true"></span>
                        <span class="pull">{str tag='reply' section='blocktype.wall'}</span>
                    </a>
                </div>
                {/if}*}

            </div>
        {/foreach}

    {/if}
</div>
{if !$wholewall}
    <a href="{$WWWROOT}blocktype/wall/wall.php?id={$instanceid}" class="detail-link link-blocktype"><span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span> {str tag='wholewall' section='blocktype.wall'}</a>
{/if}
