{foreach from=$posts item=post}
    <div id="posttitle_{$post->id}" class="{if $post->published} published{else} draft{/if} list-group-item">
        <div class="post-heading">
            <h2 class="list-group-item-heading">
                {$post->title}
            </h2>

            <div class="list-group-item-controls">
                <span id="poststatus{$post->id}" class="poststatus text-inline">
                    {if $post->published}
                        {str tag=published section=artefact.blog}
                    {else}
                        {str tag=draft section=artefact.blog}
                    {/if}
                </span>

                {if !$post->locked}
                <span id="changepoststatus{$post->id}" class="changepoststatus text-inline">
                    {$post->changepoststatus|safe}
                </span>
                {/if}

                {if $post->locked}
                <span class="locked-post text-muted">
                    <span class="icon icon-lock left" role="presentation" aria-hidden="true"></span>
                    {str tag=submittedforassessment section=view}
                </span>
                {else}
                <div class="btn-group postcontrols">
                    <form name="edit_{$post->id}" action="{$WWWROOT}artefact/blog/post.php" class="form-as-button pull-left">
                        <input type="hidden" name="id" value="{$post->id}">
                        <button type="submit" class="submit btn btn-default btn-sm" title="{str(tag=edit)|escape:html|safe}">
                            <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">{str(tag=edit)|escape:html|safe}</span>
                        </button>
                    </form>
                    {$post->delete|safe}
                </div>
                {/if}
            </div>
        </div>
        <div id="postdetails_{$post->id}" class="postdetails postdate">
            <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
            <strong>
                {str tag=postedon section=artefact.blog}:
            </strong>
            {$post->ctime}

            {if $post->tags}
            <p id="posttags_{$post->id}" class="tags">
                <span class="icon icon-tags left" role="presentation" aria-hidden="true"></span>
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$post->author tags=$post->tags}
            </p>
            {/if}
        </div>
        <p id="postdescription_{$post->id}" class="postdescription">
            {$post->description|clean_html|safe}
        </p>

        {if $post->files}
        <div class="has-attachment panel panel-default collapsible" id="postfiles_{$post->id}">
            <h5 class="panel-heading">
                <a class="text-left collapsed" data-toggle="collapse" href="#attach_{$post->id}" aria-expanded="false">
                    <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>
                    <span class="text-small"> {str tag=attachedfiles section=artefact.blog} </span>
                     <span class="metadata">
                        ({$post->files|count})
                    </span>
                    <span class="icon icon-chevron-down collapse-indicator pull-right" role="presentation" aria-hidden="true"></span>
                </a>
            </h5>
            <div class="collapse" id="attach_{$post->id}">
                <ul class="list-group list-unstyled">
                {foreach from=$post->files item=file}
                    <li class="list-group-item-link">
                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" {if $file->description} title="{$file->description}" data-toggle="tooltip"{/if}>
                            {if $file->icon}
                            <img src="{$file->icon}" alt="" class="file-icon">
                            {else}
                            <span class="icon icon-{$file->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                            {/if}
                            <span class="file-title">{$file->title|truncate:40}</span>
                            <span class="file-size">
                            ({$file->size|display_size})
                            </span>
                        </a>
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
        {/if}
    </div>
{/foreach}
