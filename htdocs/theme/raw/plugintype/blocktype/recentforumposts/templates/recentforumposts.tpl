<div id="recentforumpostsblock" class="forumposts recentforumpostsblock">
    {if $foruminfo}
        <ul class="list-unstyled list-group">
            {foreach from=$foruminfo item=postinfo}
            <li class="list-group-item flush">
                <div class="usericon-heading clearfix">
                    <a href="{profile_url($postinfo->author)}" class="user-icon small-icon">
                        <img src="{profile_icon_url user=$postinfo->author maxheight=40 maxwidth=40}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}" class="float-left">
                    </a>
                    <h4 class="title list-group-item-heading">
                        <a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic}&post={$postinfo->id}">
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
                <p class="content-text">{$postinfo->body|str_shorten_html:100:true:true:false|safe}</p>
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
    {else}
    <p class="no-results text-small">
        {str tag=noforumpostsyet section=interaction.forum}
    </p>
    {/if}
</div>
<a class="morelink card-footer text-small" href="{$WWWROOT}interaction/forum/index.php?group={$group->id}">
    {str tag=gotoforums section=interaction.forum}
    <span class="icon icon-arrow-circle-right right float-right" role="presentation" aria-hidden="true"></span>
</a>
