{foreach from=$posts item=post}
    <div id="posttitle_{$post->id}" class="{if $post->published}published{else}draft{/if} post">
        <div class="post-heading">
            <h2>{$post->title}</h2>
            <div class="post-menu">
                <span id="poststatus{$post->id}" class="poststatus">
                    {if $post->published}
                        {str tag=published section=artefact.blog}
                    {else}
                        {str tag=draft section=artefact.blog}
                    {/if}
                </span>
                <span id="changepoststatus{$post->id}" class="changepoststatus">
                    {if !$post->locked}
                        {$post->changepoststatus|safe}
                    {/if}
                </span>
                <span class="controls">
                    {if $post->locked}
                        {str tag=submittedforassessment section=view}
                    {else}
                        <form name="edit_{$post->id}" action="{$WWWROOT}artefact/blog/post.php">
                            <input type="hidden" name="id" value="{$post->id}">
                            <button type="submit" class="btn btn-default btn-xs">
                                <span class="fa fa-pencil"></span>
                                <span class="sr-only">{str(tag=editspecific arg1=$post->title)|escape:html|safe}</span>
                            </button>
                        </form>
                        {$post->delete|safe}
                    {/if}
                </span>
            </div>
            <div id="postdetails_{$post->id}" class="postdetails postdate">
                {str tag=postedon section=artefact.blog} {$post->ctime}
            </div>
        </div>
        
        <div id="postdescription_{$post->id}" class="postdescription ptl">
            {$post->description|clean_html|safe}
        </div>
        {if $post->tags}<div id="posttags_{$post->id}" class="tags">{str tag=tags}: {list_tags owner=$post->author tags=$post->tags}</div>{/if}
        {if $post->files}
            <div id="postfiles_{$post->id}">
                <div class="attachments">
                    <div class="attachment-heading">
                        <span class="badge">
                            {$post->files|count}
                        </span>
                        <a class="attach-files" data-toggle="collapse" href="#attach_{$post->id}" aria-expanded="false">
                            {str tag=attachedfiles section=artefact.blog}
                            <span class="fa fa-chevron-up"></span>
                        </a>
                    </div>
                    <div class="collapse files" id="attach_{$post->id}">

                        {foreach from=$post->files item=file}
                            <div class="attached-file {cycle values='r1,r0'}">
                                <h3 class="title">
                                    <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a>
                                </h3>
                                <div class="file-detail">
                                    <img src="{$file->icon}" alt="">
                                    <span class="file-size">
                                        ({$file->size|display_size})
                                    </span>
                                </div>
                                {if $file->description}
                                <div class="file-description">
                                    <p class="description">
                                        {$file->description}
                                    </p>
                                </div>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        {/if}
    </div>
{/foreach}
