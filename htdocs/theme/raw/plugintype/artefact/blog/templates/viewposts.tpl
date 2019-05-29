{foreach from=$posts item=post}
    <div class="post list-group-item clearfix flush">
        <div class="post-heading">
            <h4 class="title">
                <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$options.blockid}" data-artefactid="{$post->id}">
                    {$post->title}
                </a>
            </h4>
            <div class="postdetails metadata">
                <span class="icon icon-regular icon-calendar-alt left" role="presentation" aria-hidden="true"></span>
                {$post->postedby}
                {if $post->lastupdated}
                    <br>
                    <span class="icon icon-regular icon-calendar-alt left" role="presentation" aria-hidden="true"></span>
                    {str tag=updatedon section=artefact.blog} {$post->lastupdated}
                {/if}
            </div>
            {if $post->tags}
            <div class="tags metadata">
                <span class="icon icon-tags left" role="presentation" aria-hidden="true"></span>
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$post->owner tags=$post->tags view=$options.viewid}
            </div>
            {/if}
        </div>

        <div class="content-text">
            {$post->description|clean_html|safe}
        </div>

        {if $post->files}
        <div class="has-attachment card collapsible" id="blockpostfiles-{$post->id}">
            <h5 class="card-header">
                <a class="text-left collapsed" data-toggle="collapse" href="#post-attach-{$post->id}" aria-expanded="false">
                    <span class="icon icon-paperclip left" role="presentation" aria-hidden="true"></span>
                    <span class="text-small"> {str tag=attachedfiles section=artefact.blog} </span>
                     <span class="metadata">
                        ({$post->files|count})
                    </span>
                    <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                </a>
            </h5>
            <div class="collapse" id="post-attach-{$post->id}">
                <ul class="list-group list-unstyled">
                {foreach from=$post->files item=file}
                    <li class="list-group-item">
                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}&amp;view={$options.viewid}" class="outer-link icon-on-hover">
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
                            <a href="{$WWWROOT}artefact/artefact.php?artefact={$file->attachment}&view={$options.viewid}" class="inner-link">
                                {$file->title}
                                <span class="metadata"> -
                                    [{$file->size|display_size}]
                                </span>
                            </a>
                        </span>
                        <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
        {/if}

        {if $options.viewid && $post->allowcomments}
            <div class="comments">
                {if $post->allowcomments}
                    <a id="comment_link_{$post->id}" class="commentlink link-blocktype" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$options.blockid}" data-artefactid="{$post->id}">
                        <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
                        <span class="comment_count" role="presentation" aria-hidden="true"></span>
                        {str tag=commentsanddetails section=artefact.comment arg1=$post->commentcount}
                    </a>
                {/if}
            </div>
        {/if}
    </div>
{/foreach}
