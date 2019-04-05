{if $foruminfo}
    <div class="blocktype forumposts" id="latestforumposts">
        <ul class="list-unstyled list-group">
            {foreach from=$foruminfo item=postinfo name=item}
            <li class="list-group-item flush">
                <div class="usericon-heading">
                    <a href="{profile_url($postinfo->author)}" class="user-icon user-icon-20 small-icon">
                        <img src="{profile_icon_url user=$postinfo->author maxheight=20 maxwidth=20}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}" />
                    </a>

                    <h4 class="title list-group-item-heading">
                         <a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}&post={$postinfo->id}">
                            {$postinfo->topicname}
                            <span class="metadata text-small">
                                {if !$postinfo->author->deleted}
                                    - {$postinfo->author|display_name}
                                {else}
                                    - {$postinfo->author|full_name}
                                {/if}
                            </span>
                        </a>
                    </h4>
                </div>
                <div class="detail">
                    {$postinfo->body|str_shorten_html:100:true:true:false|safe}
                </div>
                {if $postinfo->filecount}
                <div class="has-attachment card collapsible collapsible-group" id="blockpostfiles-{$postinfo->id}">
                    <h5 class="card-header">
                        <a class="text-left collapsed" data-toggle="collapse" href="#post-attach-{$postinfo->id}" aria-expanded="false">
                            <span class="icon icon-paperclip left" role="presentation" aria-hidden="true"></span>
                            <span class="text-small"> {str tag=attachedfiles section=artefact.blog} </span>
                            <span class="metadata">
                                ({$postinfo->filecount})
                            </span>
                            <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                        </a>
                    </h5>
                    <div class="collapse" id="post-attach-{$postinfo->id}">
                        <ul class="list-group list-unstyled">
                        {foreach from=$postinfo->attachments item=file}
                            <li class="list-group-item">
                                <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}&amp;post={$postinfo->id}" class="outer-link icon-on-hover">
                                    <span class="sr-only">
                                        {str tag=Download section=artefact.file} {$file->title}
                                    </span>
                                </a>
                                {if $file->icon}
                                <img class="file-icon" src="{$file->icon}" alt="">
                                {else}
                                <span class="icon icon-{$file->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                                {/if}
                                <span class="title">
                                    <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}&post={$postinfo->id}" class="inner-link">
                                        {$file->title}
                                        <span class="metadata"> - [{$file->size|display_size}]</span>
                                    </a>
                                </span>
                                <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
                            </li>
                        {/foreach}
                        </ul>
                    </div>
                </div>
                {/if}
            </li>
            {/foreach}
        </ul>
        <a href="{$WWWROOT}interaction/forum/index.php?group={$group->id}" class="card-footer text-small">
        {str tag=gotoforums section=interaction.forum}
        <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
{else}
    <div class="card-body">
        <span class="text-small no-results">{str tag=noforumpostsyet section=interaction.forum}</span>
    </div>
{/if}
