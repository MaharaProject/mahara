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
                    <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
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

{if $blockheading || $configerror || $badnotag || $badtag || $full}
  <div class="details-before-list-group">
{/if}

{if $blockheading}
    <p class="text-midtone">{$blockheading|clean_html|safe}</p>
{/if}
{if $configerror}
    <p class="text-midtone">{str tag='configerror' section='blocktype.blog/taggedposts'}</p>
</div>
{elseif $badnotag && $badtag}
    <p class="text-midtone">{str tag='notagsboth' section='blocktype.blog/taggedposts' arg1=$badtag arg2=$badnotag}</p>
</div>
{elseif $badnotag}
    <p class="text-midtone">{str tag='notagsomit' section='blocktype.blog/taggedposts' arg1=$badnotag}</p>
</div>
{elseif $badtag}
    <p class="text-midtone">{str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}</p>
</div>
{elseif $full}

{if $blockheading || $configerror || $badnotag || $badtag || $full}
  </div>
{/if}

    <div id="postlist_{$blockid}" class="list-group">
        {foreach from=$results item=post}
        {if !$editing}
            {if !$post->allowcomments}
                {assign var="justdetails" value=true}
            {/if}
            {include
                file='header/block-comments-details-header.tpl'
                artefactid=$post->id
                blockid=$blockid
                commentcount=$post->commentcount
                allowcomments=$post->allowcomments
                justdetails=$justdetails
                displayiconsonly=true}
        {/if}
        <div class="post list-group-item flush">
            <h3 class="list-group-item-heading title">
                {if !($editing)}
                     <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$post->id}">
                         {$post->title}
                     </a>
                {else}
                    <span class="list-group-item-heading no-link">{$post->title}</span>
                {/if}
            </h3>
            <div class="postdetails text-small text-midtone">
                <span class="icon icon-regular icon-calendar-alt left" role="presentation" aria-hidden="true"></span>
                {$post->postedbyon}
            </div>
            <div class="tags text-small text-midtone">
                <span class="icon icon-tags" role="presentation" aria-hidden="true"></span>
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$post->owner tags=$post->taglist view=$view}
            </div>

            <div class="detail list-group-item-detail content-text">
                {$post->description|clean_html|safe}
            </div>
            {if $post->attachments}
                {$post->attachments|safe}
            {/if}
        </div>
        {/foreach}
    </div>
{else}
</div>
<div class="taggedposts list-group">
    {foreach from=$results item=post}
    {if !$editing}
        {if !$post->allowcomments}
            {assign var="justdetails" value=true}
        {/if}
        {include
            file='header/block-comments-details-header.tpl'
            artefactid=$post->id
            blockid=$blockid
            commentcount=$post->commentcount
            allowcomments=$post->allowcomments
            justdetails=$justdetails
            displayiconsonly=true}
    {/if}
    <div class="list-group-item flush-collapsible">
        <h3 class="list-group-item-heading title">
            {if !($editing)}
            <a class="modal_link list-group-item-heading" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$post->id}">
                {$post->title}
            </a>
            {else}
            <span class="list-group-item-heading no-link">{$post->title}</span>
            {/if}
        </h3>

        <a class="collapsed" data-toggle="collapse" href="#tagged_post_{$post->id}" aria-expanded="false">
            <span class="sr-only">{$post->title}</span>
            <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
        </a>

        <div>
            <span class="metadata">
                {str tag='postedon' section='blocktype.blog/taggedposts'}
                {$post->displaydate}
                {if $post->updateddate}
                <br>
                    {str tag='updatedon' section='blocktype.blog/taggedposts'}
                    {$post->updateddate}
                {/if}
            </span>
        </div>
        <div  id="tagged_post_{$post->id}" class="collapse content-text">
            <span>{$post->description|safe}</span>
        </div>
    </div>
    {/foreach}
</div>
{/if}
