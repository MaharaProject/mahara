{foreach from=$posts item=post}
{if !$options.editing}
    {if !$post->allowcomments}
        {assign var="justdetails" value=true}
    {/if}
    {include
        file='header/block-comments-details-header.tpl'
        artefactid=$post->id
        blockid=$options.blockid
        commentcount=$post->commentcount
        allowcomments=$post->allowcomments
        justdetails=$justdetails
        displayiconsonly = true}
{/if}
    <div class="post list-group-item clearfix flush">
        <div class="post-heading">
            <h4 class="title">
                {if !$options.editing}
                    <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$options.blockid}" data-artefactid="{$post->id}">
                        {$post->title}
                    </a>
                {else}
                    {$post->title}
                {/if}
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
            <div class="card-header">
                <a class="text-left collapsed" data-toggle="collapse" href="#post-attach-{$post->id}" aria-expanded="false">
                    <span class="icon icon-paperclip left icon-sm" role="presentation" aria-hidden="true"></span>
                    <span class="text-small"> {str tag=attachedfiles section=artefact.blog} </span>
                     <span class="metadata">
                        ({$post->files|count})
                    </span>
                    <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                </a>
            </div>
            <div class="collapse" id="post-attach-{$post->id}">
                <ul class="list-group list-unstyled">
                {foreach from=$post->files item=file}
                    <li class="list-group-item">
                    {if $file->icon}
                        <img class="file-icon" src="{$file->icon}" alt="">
                    {else}
                        <span class="icon icon-{$file->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                    {/if}
                    {if !$options.editing}
                    <span class="title">
                        <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$options.blockid}" data-artefactid="{$file->attachment}">
                            <span class="text-small">{$file->title}</span>
                        </a>
                    </span>
                    {else}
                        <span class="title">
                            <span class="text-small">{$file->title}</span>
                        </span>
                    {/if}
                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}&amp;view={$options.viewid}">
                            <span class="sr-only">{str tag=Download section=artefact.file} {$file->title}</span>
                            <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
                        </a>
                        {if $file->description}
                        <div class="file-description text-small">
                            {$file->description|clean_html|safe}
                        </div>
                        {/if}
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
        {/if}
    </div>
{/foreach}
