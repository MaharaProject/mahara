{if $foruminfo}
    <div class="blocktype forumposts" id="latestforumposts">
        <ul class="list-unstyled list-group">
            {foreach from=$foruminfo item=postinfo name=item}
            <li class="list-group-item flush">
                <div class="usericon-heading">
                    <a href="{profile_url($postinfo->author)}" class="user-icon small-icon">
                        <img src="{profile_icon_url user=$postinfo->author maxheight=60 maxwidth=60}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}" />
                    </a>

                    <h4 class="title list-group-item-heading">
                         <a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}&post={$postinfo->id}">
                            {$postinfo->topicname}
                            <span class="metadata text-small">
                                - {$postinfo->author|display_name}
                            </span>
                        </a>
                    </h4>
                </div>
               <div class="detail">
                    {$postinfo->body|str_shorten_html:100:true:true:false|safe}
                </div>
            </li>
            {/foreach}
        </ul>
        <a href="{$WWWROOT}interaction/forum/index.php?group={$group->id}" class="panel-footer text-small">
        {str tag=gotoforums section=interaction.forum}
        <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
{else}
    <div class="panel-body">
        <span class="text-small no-results">{str tag=noforumpostsyet section=interaction.forum}</span>
    </div>
{/if}
