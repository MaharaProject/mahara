<div id="recentforumpostsblock" class="forumposts recentforumpostsblock">
    {if $foruminfo}
        <ul class="list-unstyled list-group">
            {foreach from=$foruminfo item=postinfo}
            <li class="list-group-item flush">
                <div class="usericon-heading clearfix">
                    <a href="{profile_url($postinfo->author)}" class="user-icon user-icon-30 small-icon">
                        <img src="{profile_icon_url user=$postinfo->author maxheight=40 maxwidth=40}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}" class="float-left">
                    </a>
                    <h3 class="title list-group-item-heading">
                        <a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic}&post={$postinfo->id}">
                            {$postinfo->topicname}
                        </a>
                        <br />
                        <span class="text-small"><a href="{profile_url($postinfo->author)}">
                            {if !$postinfo->author->deleted}
                                {$postinfo->author|display_name}
                            {else}
                                {$postinfo->author|full_name}
                            {/if}
                        </a></span>
                    </h3>
                </div>
                <p class="content-text">{$postinfo->body|str_shorten_html:100:true:true:false|safe}</p>
                {if $postinfo->filecount}
                <div class="has-attachment card collapsible collapsible-group" id="blockpostfiles-{$postinfo->id}">
                    <div class="card-header">
                        <a class="text-left collapsed" data-toggle="collapse" href="#post-attach-{$postinfo->id}" aria-expanded="false">
                            <span class="icon icon-paperclip left icon-sm" role="presentation" aria-hidden="true"></span>
                            <span class="text-small"> {str tag=attachedfiles section=artefact.blog} </span>
                            <span class="metadata">({$postinfo->filecount})</span>
                            <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                        </a>
                    </div>
                    <div class="collapse" id="post-attach-{$postinfo->id}">
                        <ul class="list-group list-unstyled">
                        {foreach from=$postinfo->attachments item=file}
                            <li class="list-group-item">
                                <span class="file-icon-link">
                                {if $file->icon}
                                    <img class="file-icon" src="{$file->icon}" alt="">
                                {else}
                                    <span class="icon icon-{$file->artefacttype} icon-lg text-default left file-icon" role="presentation" aria-hidden="true"></span>
                                {/if}
                                </span>
                                <span class="title">
                                    <span class="text-small">{$file->title}</span>
                                </span>
                                <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}&amp;post={$postinfo->id}" class="download-link">
                                    <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$file->title arg2=$file->size|display_size}"></span>
                                </a>
                            {if $file->description}
                                <div class="file-description text-small text-midtone">
                                    {$file->description|clean_html|safe}
                                </div>
                            {/if}
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
