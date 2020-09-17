{if $post->id}
<a name="post{$post->id}">
</a>
{/if}

    <div class="media forum-post {if !$post->approved} alert-warning {/if}">
        <div class="forumpostleft media-left text-small">
            {if $deleteduser}
                <span class="user-icon user-icon-40"><img src="{profile_icon_url user=null maxwidth=40 maxheight=40}" valign="middle" alt="{str tag=profileimagetextanonymous}" class="media-object"></span>

                <div class="poster">
                    <span>{$poster|full_name}</span>
                </div>
            {else}
                <span class="user-icon user-icon-40"><img src="{profile_icon_url user=$post->poster maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$post->poster|display_default_name}" class="media-object"></span>

                <div class="poster">
                    <a href="{profile_url($post->poster)}"{if in_array($post->poster, $groupadmins)} class="groupadmin"{elseif $post->moderator} class="moderator"{/if}>{$post->poster|display_name}
                    </a>
                </div>
            {/if}

            {if $post->postcount}
            <div class="postcount text-midtone">
                {$post->postcount}
            </div>
            {/if}
        </div>
        <div class="postedits media-body">
            {if $post->subject && !$nosubject}
            <div class="forumsubject media-heading">
                <h3 class="title h4">
                    {if $post->id}
                    <a href="#post{$post->id}">
                        {$post->subject}
                    </a>
                    {else}
                        {$post->subject}
                    {/if}
                </h3>
            </div>
            {/if}

            <p class="posttime detail text-small text-midtone">
                {$post->ctime}
            </p>

            {$post->body|clean_html|safe}
            {if $post->attachments}
            <div class="has-attachment card collapsible">
                <div class="card-header">
                    <a class="text-left collapsed" data-toggle="collapse" href="#post-attach-{$post->id}" aria-expanded="false">
                        <span class="icon icon-paperclip left" role="presentation" aria-hidden="true"></span>
                        <span class="text-small"> {str tag="attachedfiles" section="artefact.blog"} </span>
                        <span class="metadata">({$post->filecount})</span>
                        <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                    </a>
                </div>
                <div class="collapse" id="post-attach-{$post->id}">
                    <ul class="list-group list-unstyled">
                    {foreach from=$post->attachments item=file}
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
                            <a href="{$WWWROOT}artefact/file/download.php?file={$file->fileid}&post={$file->post}" class="download-link">
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
            {if $post->edit}
            <div class="editstopost">
                <h3 class="title h6">
                    {str tag="editstothispost" section="interaction.forum"}
                </h3>
                <ul class="list-unstyled text-small">
                    {foreach from=$post->edit item=edit}
                    <li>
                        {if $edit.deleteduser}
                            <img src="{profile_icon_url user=null maxheight=20 maxwidth=20}" alt="{str tag=profileimagetextanonymous}"/>
                            {str tag=deleteduser1}
                        {else}
                            <a href="{profile_url($edit.editor)}"
                            {if $edit.editor == $groupowner} class="groupowner"
                            {elseif $edit.moderator} class="moderator"
                            {/if}
                            >
                            <img src="{profile_icon_url user=$edit.editor maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$edit.editor|display_default_name}">
                            {$edit.editor|display_name}
                            </a> -
                        {/if}
                    <span class="posttime text-muted">
                        {$edit.edittime}
                    </span>
                    </li>
                    {/foreach}
                </ul>
            </div>
            {/if}
        </div>
    </div>
