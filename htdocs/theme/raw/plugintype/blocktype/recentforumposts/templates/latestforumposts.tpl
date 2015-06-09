{if $foruminfo}
    <div class="blocktype forumposts" id="latestforumposts">
        <ul class="list-unstyled list-group mb0">
            {foreach from=$foruminfo item=postinfo name=item}
            <li class="list-group-item pl0">
                    <a href="{profile_url($postinfo->author)}" class="mts user-icon small-icon left">
                        <img src="{profile_icon_url user=$postinfo->author maxheight=60 maxwidth=60}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}" />
                    </a>

                    <h4 class="title list-group-item-heading mts mlxl">
                         <a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}&post={$postinfo->id}" class="plm">
                            {$postinfo->topicname}
                            <span class="metadata text-small">
                                {$postinfo->author|display_name}
                            </span>
                        </a>
                    </h4>
                   <div class="ptl detail">
                         <p class="text-small">
                            {$postinfo->body|str_shorten_html:100:true|safe}
                        </p>
                    </div>
                </li>
            {/foreach}
        </ul>
        <a href="{$WWWROOT}interaction/forum/index.php?group={$group->id}" class="panel-footer">
        {str tag=gotoforums section=interaction.forum}
        <span class="icon icon-arrow-circle-right mls"></span>
        </a>
    </div>
{else}
    <div class="panel-body">
        {str tag=noforumpostsyet section=interaction.forum}
    </div>
{/if}

