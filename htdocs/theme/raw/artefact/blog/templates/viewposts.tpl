{foreach from=$posts item=post}
<div class="post">
    <h3 class="title"><a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{$post->title}</a></h3>
    <div class="postdetails">{$post->postedby}</div>
    <div>{$post->description|clean_html|safe}
    {if $post->tags}
    <div class="tags">{str tag=tags}: {list_tags owner=$post->owner tags=$post->tags}</div>
    {/if}</div>
    {if $post->files}
    <table class="cb attachments fullwidth" id="blockinstance-attachments-{$post->id}-{$options.blockid}">
        <thead class="expandable-head">
            <tr>
                <td colspan="2">
                    <a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
                    <span class="fr">
                        <img class="fl" src="{theme_image_url filename='attachment'}" alt="{str tag=attachments section=artefact.blog}">
                        {$post->files|count}
                    </span>
                </td>
            </tr>
        </thead>
        <tbody class="expandable-body">
            {foreach from=$post->files item=file}
            <tr class="{cycle values='r0,r1'}">
                <td class="icon-container"><img src="{$file->icon}" alt=""></td>
                <td>
                    <h3 class="title">
                        <a href="{$WWWROOT}artefact/artefact.php?artefact={$file->attachment}&view={$options.viewid}">{$file->title}</a>
                        <span class="description">({$file->size|display_size}) - <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}&view={$options.viewid}">{str tag=Download section=artefact.file}</a></span>
                    </h3>
                    <div class="detail">{$file->description}</div>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {/if}
    {if $options.viewid && ($post->commentcount || $post->commentcount === '0')}
    <div class="comments">
        {if $post->commentcount > 0}
            {if !$options.editing}<a id="block_0{$post->id}{$options.blockid}" class="commentlink" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{/if}
            {str tag=Comments section=artefact.comment} ({$post->commentcount})
            {if !$options.editing}</a>{/if}
        {else}
            {if $post->allowcomments}
                <span class="nocomments">{str tag=Comments section=artefact.comment} ({$post->commentcount})</span>
            {/if}
        {/if}
        {if $post->allowcomments}
            <a class="addcomment bar-before" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$options.viewid}">{str tag=addcomment section=artefact.comment}</a>
        {/if}
    </div>
    <div class="feedbacktablewrapper">
        <div id="feedbacktable_0{$post->id}{$options.blockid}" class="feedbacktable">
          {$post->comments->tablerows|safe}
        </div>
    </div>
    {/if}
</div>
<script type="application/javascript">
setupExpanders($j('#blockinstance-attachments-{$post->id}-{$options.blockid}'));
</script>
{/foreach}
