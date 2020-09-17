<div id="wall" class="wall list-group list-group-lite list-group-top-border">
    {if $wallmessage}
        <div class="card-body lead text-small text-center">{$wallmessage}</div>
    {/if}
    {if $wallposts}
        {foreach from=$wallposts item=wallpost}
            <div class="list-group-item {if $wallpost->private} list-group-item-private{/if} wallpost">
                <div class="usericon-heading">
                    <span class="user-icon user-icon-30 float-left" role="presentation" aria-hidden="true">
                        <a href="{$wallpost->profileurl}"><img src="{profile_icon_url user=$wallpost maxheight=30 maxwidth=30}" alt="{str tag=profileimagetext arg1=$wallpost|display_default_name}" /></a>
                    </span>
                    <h3 class="list-group-item-heading text-inline"><a href="{$wallpost->profileurl}">{$wallpost->displayname}</a><br /><span class="postedon text-small text-midtone">{$wallpost->postdate|format_date}</span></h3>

                    {if $wallpost->deletable}
                    <div class="btn-group btn-group-top comment-item-buttons">
                        {* {if $ownwall}
                            <a href="{$WWWROOT}blocktype/wall/wall.php?instance={$instanceid}&amp;replyto={$wallpost->id}" class="btn btn-secondary btn-group-item form-as-button float-left">
                                <span class="icon icon-reply" role="presentation" aria-hidden="true"></span>
                                <span class="sr-only">{str tag='reply' section='blocktype.wall'}</span>
                            </a>
                        {/if}*}
                        <a href="{$WWWROOT}blocktype/wall/deletepost.php?postid={$wallpost->postid}&return={if $wholewall}wall{else}profile{/if}" class="btn btn-secondary btn-group-item form-as-button float-left">
                            <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">{str tag='delete' section='blocktype.wall'}</span>
                        </a>
                    </div>
                    {/if}
                </div>

                <div class="wallpost-text">
                    {$wallpost->text|safe}
                    <div class="metadata">
                    {if $wallpost->private}<em class="privatemessage">{str tag='wallpostprivate' section='blocktype.wall'}</em>{/if}
                    </div>
                </div>

            </div>
        {/foreach}
    {/if}
</div>
{if !$wholewall}
    <a href="{$WWWROOT}blocktype/wall/wall.php?id={$instanceid}" class="detail-link link-blocktype"><span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span> {str tag='wholewall' section='blocktype.wall'}</a>
{/if}
