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
                <h5 class="title">
                    {if $post->id}
                    <a href="#post{$post->id}">
                        {$post->subject}
                    </a>
                    {else}
                        {$post->subject}
                    {/if}
                </h5>
            </div>
            {/if}

            <p class="posttime detail text-small text-midtone">
                {$post->ctime}
            </p>

            {$post->body|clean_html|safe}
            {if $post->attachments}
            <div class="postattachments">
                <h5 class="title">
                    <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>
                    {str tag="attachedfiles" section="artefact.blog"}
                </h5>
                <ul class="list-group list-group-unbordered">
                    {foreach from=$post->attachments item=file}
                    <li class="list-group-item list-group-item-link small">
                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->fileid}&post={$file->post}" {if $file->fileid} title="{$file->description}" data-toggle="tooltip"{/if}>
                        {if $file->icon}
                            <img src="{$file->icon}" alt="" class="file-icon">
                        {else}
                            <span class="icon icon-{$file->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                        {/if}
                        <span>{$file->title|truncate:40} - ({$file->size|display_size})</span>
                        </a>
                    </li>
                    {/foreach}
                </ul>
            </div>
            {/if}
            {if $post->edit}
            <div class="editstopost">
                <h5 class="title">
                    {str tag="editstothispost" section="interaction.forum"}
                </h5>
                <ul class="list-unstyled text-small">
                    {foreach from=$post->edit item=edit}
                    <li>
                        {if $edit.deleteduser}
                            <img src="{profile_icon_url user=null maxheight=20 maxwidth=20}" alt="{str tag=profileimagetextanonymous}"/>
                            {str tag=deleteduser}
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
