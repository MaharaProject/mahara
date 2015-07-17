{foreach from=$posts item=post}
    <div class="post list-group-item clearfix">
        <div class="post-heading">
            <h4 class="title">
                <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{$post->title}</a>
            </h4>
            <div class="postdetails metadata">
                <span class="icon icon-calendar mrs"></span>
                {$post->postedby}
            </div>
            {if $post->tags}
            <div class="tags metadata">
                <span class="icon icon-tags"></span>
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$post->owner tags=$post->tags}
            </div>
            {/if}
        </div>

        {if $post->files}
        <ul class="attachment-incontent list-group list-group-unbordered pull-right mlm mtl">
            <li class="list-group-item">
                <span class="icon icon-paperclip icon-lg"></span>
                <span>{str tag=attachedfiles section=artefact.blog}</span>
            </li>
            {foreach from=$post->files item=file}
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/artefact.php?artefact={$file->attachment}&view={$options.viewid}" class="outer-link icon-on-hover">
                    <span class="sr-only">
                        {str tag=Download section=artefact.file} {$file->title}
                    </span>
                </a>
                {if $file->icon}
                <img src="{$file->icon}" alt="">
                {else}
                <span class="icon icon-{$file->artefacttype} icon-lg text-default"></span>
                {/if}
                <span class="title list-group-item-heading plm inline">
                    <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}&amp;view={$options.viewid}" class="inner-link">
                        {$file->title}
                        <span class="metadata"> -
                            [{$file->size|display_size}]
                        </span>
                    </a>
                </span>
                <span class="icon icon-download icon-lg pull-right pts text-watermark icon-action"></span>
            </li>
            {/foreach}
        </ul>
        {/if}

        <div class="post-content mtl">
            {$post->description|clean_html|safe}
        </div>

        {if $options.viewid && $post->allowcomments}
        <div class="comments ptm pbl">
            {if $post->commentcount > 0}
            <a id="blockpost_{$post->id}" class="commentlink" data-toggle="modal-docked" data-target="#feedbacktable_0{$post->id}{$options.blockid}" href="#">
                {str tag=Comments section=artefact.comment} ({$post->commentcount})
            </a>
            {else}
                {if $post->allowcomments}
                <a class="addcomment" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">
                    {str tag=addcomment section=artefact.comment}
                    <span class="icon icon-arrow-right text-success pls"></span>
                </a>
                {/if}
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
                        <h4 class="modal-title pull-left">
                            <span class="icon icon-lg icon-comments"></span>
                            {str tag=Comments section=artefact.comment} |
                            {$post->title}
                        </h4>
                        {if $post->allowcomments}
                        <a class="addcomment pull-right" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">
                            {str tag=addcomment section=artefact.comment}
                            <span class="icon icon-arrow-right pls"></span>
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
