{if ($editing)}
<div class="shortcut nojs-hidden-block">
    <div class="card-footer {if (count($blogs) == 1)}d-none{/if}">
        <label class="text">{str tag='shortcutaddpost' section='artefact.blog'} </label>
        <div class="input-group">
            <span class="picker">
                <select id="blogselect_{$blockid}" class="select form-control">{foreach from=$blogs item=blog}<option value="{$blog->id}"> {$blog->title} </option>{/foreach}</select>
            </span>
            <input class="select" type="hidden" value="{$tagselect}">
            <span class="input-group-append">
                <a class="btn btnshortcut btn-secondary">
                    <span class="icon icon-plus text-success left" role="presentation" aria-hidden="true"></span>
                    {str tag='shortcutadd' section='artefact.blog'}
                </a>
            </span>
        </div>
    </div>
    <a class="btn btnshortcut feedback card-footer mtl {if (count($blogs) != 1)} d-none{/if}">
        <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag='shortcutnewentry' section='artefact.blog'}
    </a>
</div>
{/if}

<div class="taggedpost-title text-midtone card-body flush">
    {$blockheading|clean_html|safe}
</div>

{if $configerror}
    <span class="text-midtone">{str tag='configerror' section='blocktype.blog/taggedposts'}</span>
{elseif $badnotag && $badtag}
    <span class="text-midtone">{str tag='notagsboth' section='blocktype.blog/taggedposts' arg1=$badtag arg2=$badnotag}</span>
{elseif $badnotag}
    <span class="text-midtone">{str tag='notagsomit' section='blocktype.blog/taggedposts' arg1=$badnotag}</span>
{elseif $badtag}
    <span class="text-midtone">{str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}</span>
{elseif $full}
<div id="blogdescription">
    <div id="postlist_{$blockid}" class="list-group">
        {foreach from=$results item=post}
        <div class="post list-group-item">
            <h4 class="list-group-heading">
                <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
                    {$post->title}
                </a>
            </h4>
            <div class="postdetails metadata">
                <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
                {$post->postedbyon}
            </div>
            <div class="tags metadata">
                <span class="icon icon-tags" role="presentation" aria-hidden="true"></span>
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$post->owner tags=$post->taglist view=$view}
            </div>

            <div class="detail list-group-item-detail">
                {$post->description|clean_html|safe}
            </div>

            {if $post->attachments}
                {$post->attachments|safe}
            {/if}

            {if !$editing}
                {if $post->commentcount != null}
                <div class="comments clearfix">
                    {if $post->commentcount > 0}
                    <a id="block_0{$post->id}{$blockid}" class="commentlink link-blocktype" data-toggle="modal-docked" data-target="#feedbacktable_0{$post->id}{$blockid}" href="#">
                        <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
                        {str tag=Comments section=artefact.comment} ({$post->commentcount})
                    </a>
                    {/if}
                    {if $post->allowcomments}
                    <a class="addcomment link-blocktype" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
                        <span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span>
                        {str tag=addcomment section=artefact.comment}
                    </a>
                    {/if}
                </div>
                {/if}
            {/if}
        </div>
        {if !$editing}
        <div id="feedbacktable_0{$post->id}{$blockid}" class="feedbacktable modal modal-docked">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header clearfix">
                        <button class="close" data-dismiss="modal-docked">
                            <span class="times">&times;</span>
                            <span class="sr-only">{str tag=Close}</span>
                        </button>
                        <h4 class="modal-title float-left">
                            <span class="icon icon-lg icon-comments left" role="presentation" aria-hidden="true"></span>
                            {str tag=Comments section=artefact.comment} |
                            {$post->title}
                        </h4>
                        {if $post->allowcomments}
                        <a class="addcomment float-right" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
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
        {/foreach}
    </div>
</div>
{else}
<div class="taggedposts list-group">
    {foreach from=$results item=post}
    <div class="list-group-item">
        <h4 class="list-group-item-heading">
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&amp;view={$view}">
                {$post->title}
            </a>
            <span class="metadata">
                {str tag='postedon' section='blocktype.blog/taggedposts'}
                {$post->displaydate}
                {if $post->updateddate}
                <br>
                    {str tag='updatedon' section='blocktype.blog/taggedposts'}
                    {$post->updateddate}
                {/if}
            </span>
        </h4>
    </div>
    {/foreach}
</div>
{/if}
