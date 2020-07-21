{if ($editing)}
    {if (count($blogs) == 1)}
        <a class="card-footer {if (count($blogs) != 1)} d-none{/if}">
            <span id="blog_{$blogs[0]->id}" class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            {str tag='shortcutnewentry' section='artefact.blog'}
        </a>
    {elseif (count($blogs) > 1)}
    <div class="card-footer">
        <label class="text" for="blogselect_{$blockid}">{str tag='shortcutaddpost' section='artefact.blog'}</label>
        <div class="input-group">

            <select id="blogselect_{$blockid}" class="select form-control">
            {foreach from=$blogs item=blog}
                <option value="{$blog->id}"> {$blog->title} </option>
            {/foreach}
            </select>
            <span class="input-group-append">
                <a class="btn btn-secondary btnshortcut">
                    <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span> {str tag='shortcutadd' section='artefact.blog'}
                </a>
            </span>
        </div>
    </div>
    {/if}
{/if}
<div class="recentblogpost list-group">
{foreach from=$mostrecent item=post}
    {if !($editing)}
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
                 <a class="modal_link text-left" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$post->id}">
                     {$post->title}
                 </a>
            {else}
                <span class="list-group-item-heading no-link">{$post->title}</span>
            {/if}
        </h3>
        <a class="collapsed" data-toggle="collapse" href="#recent_post_{$post->id}" aria-expanded="false">
            <span class="sr-only">{$post->title}</span>
            <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
        </a>

        <div>
            <span class="text-small">
                {str tag='postedin' section='blocktype.blog/recentposts'}
                {if $canviewblog}
                    <a href="{$WWWROOT}artefact/blog/view/index.php?id={$post->parent}" class="inner-link">
                        {$post->parenttitle}
                    </a>
                {else}
                    {$post->parenttitle}
                {/if}
            </span>
            <span class="metadata">
                {str tag='postedon' section='blocktype.blog/recentposts'}
                {$post->displaydate}
                <br>
                {if $post->updateddate}
                    {str tag='updatedon' section='blocktype.blog/recentposts'}
                    {$post->updateddate}
                {/if}
            </span>
        </div>
        <div  id="recent_post_{$post->id}" class="collapse content-text">
            <span>{$post->description|safe}</span>
            {if isset($post->attachments) && !$modal}
                {include file="artefact:blog:render/blogpost_renderattachments.tpl" attachments=$post->attachments postid=$post->id}
            {/if}
        </div>


    </div>
{/foreach}
</div>
