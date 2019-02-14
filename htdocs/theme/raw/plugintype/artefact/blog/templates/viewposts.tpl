{foreach from=$posts item=post}
    <div class="post list-group-item clearfix flush">
        <div class="post-heading">
            <h4 class="title">
                <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{$post->title}</a>
            </h4>
            <div class="postdetails metadata">
                <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
                {$post->postedby}
                {if $post->lastupdated}
                    <br>
                    <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
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
                {if $post->commentcount > 0}
                <a id="blockpost_{$post->id}" class="commentlink link-blocktype" data-toggle="modal-docked" data-target="#feedbacktable_0{$post->id}{$options.blockid}" href="#">
                    <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
                    {str tag=Comments section=artefact.comment} ({$post->commentcount})
                </a>
                {/if}
                {if $post->allowcomments}
                <a class="addcomment link-blocktype" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">
                    <span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span>
                    {str tag=addcomment section=artefact.comment}
                </a>
                {/if}
            </div>
            <div class="feedback modal modal-docked" id="feedbacktable_0{$post->id}{$options.blockid}">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header clearfix">
                            <button class="close" data-dismiss="modal-docked">
                                <span class="times">&times;</span>
                                <span class="sr-only">{str tag=Close}</span>
                            </button>
                            <h4 class="modal-title float-left">
                                <span class="icon icon-lg icon-comments" role="presentation" aria-hidden="true"></span>
                                {str tag=Comments section=artefact.comment} |
                                {$post->title}
                            </h4>
                            {if $post->allowcomments}
                            <a class="addcomment float-right" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">
                                {str tag=addcomment section=artefact.comment}
                                <span class="icon icon-arrow-right right" role="presentation" aria-hidden="true"></span>
                            </a>
                            {/if}
                        </div>
                        <div class="modal-body flush">
                        {$post->comments->tablerows|safe}
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
{/foreach}
