{foreach from=$posts item=post}
    <div class="post ptm mbxl">
        <h4 class="title">
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{$post->title}</a>
        </h4>
        <div class="postdetails metadata">
            <span class="fa fa-calendar mrs"></span>
            {$post->postedby}
        </div>
        {if $post->tags}
        <div class="tags metadata">
            <span class="fa fa-tags"></span>
            <strong>{str tag=tags}:</strong>
            {list_tags owner=$post->owner tags=$post->tags}
        </div>
        {/if}
        
        <div class="mtl mbl">{$post->description|clean_html|safe}
    </div>


        {if $post->files}
        <div class="has-attachment panel panel-default collapsible" id="postfiles_{$post->id}">
            <h5 class="panel-heading">
                <a href="#blogpost-attach-{$post->id}" class="text-left pts pbm collapsed" aria-expanded="false" data-toggle="collapse">
                    <span class="fa fa-paperclip prm"></span>

                    <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                    <span class="metadata">({$post->files|count})</span>
                    <span class="fa pts fa-chevron-down pull-right collapse-indicator"></span>
                </a>
            </h5>

            <div id="blogpost-attach-{$post->id}" class="collapse">
                <ul class="list-unstyled list-group">
                    {foreach from=$post->files item=file}
                    <li class="list-group-item-text list-group-item-link">
                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}&view={$options.viewid}">
                            <div class="file-icon mrs">
                                {if $file->icon}
                                <img src="{$file->icon}" alt="">
                                {else}
                                <span class="fa fa-{$file->artefacttype} fa-lg text-default"></span>
                                {/if}
                            </div>
                            {$file->title} [{$file->size|display_size}]
                        </a>
                    </li>
                    {/foreach}
                </ul>
            </div>
        </div>

        {/if}
        {if $options.viewid && ($post->commentcount || $post->commentcount === 0)}
        <div class="comments clearfix ptm mts">
            {if $post->commentcount > 0}
                <a id="block_0{$post->id}{$options.blockid}" class="commentlink text-thin pull-left" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{str tag=Comments section=artefact.comment} ({$post->commentcount})</a>
            {else}
                {if $allowcomments}
                <span class="nocomments text-thin pull-left">{str tag=Comments section=artefact.comment} ({$post->commentcount})</span>
                {/if}
                {if $post->allowcomments}
                <p class="text-right">
                    <a class="addcomment text-thin" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">
                        <span class="fa fa-lg fa-plus text-primary prs"></span>
                        {str tag=addcomment section=artefact.comment}
                    </a>
                </p>
                {/if}
            {/if}
        </div>
        {/if}
    </div>
{/foreach}
