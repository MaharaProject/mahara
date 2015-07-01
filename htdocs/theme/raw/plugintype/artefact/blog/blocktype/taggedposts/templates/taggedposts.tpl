{if ($editing)}
<div class="shortcut nojs-hidden-block mtl">
    <div class="alert alert-default {if (count($blogs) == 1)}hidden{/if}">
        <label class="text">{str tag='shortcutaddpost' section='artefact.blog'}: </label>
        <div class="input-group">
            <span class="picker">
                <select id="blogselect_{$blockid}" class="select form-control">{foreach from=$blogs item=blog}<option value="{$blog->id}"> {$blog->title} </option>{/foreach}</select>
            </span>
            <input class="select" type="hidden" value="{$tagselect}">
            <span class="input-group-btn">
                <a class="btn btnshortcut btn-success">{str tag='shortcutgo' section='artefact.blog'}</a>
            </span>
        </div>
    </div>
    <a class="btn btnshortcut feedback panel-footer mtl {if (count($blogs) != 1)} hidden{/if}">
        <span class="icon icon-plus prs"></span>
        {str tag='shortcutnewentry' section='artefact.blog'}
    </a>
</div>
{/if}

<h4>
    {$blockheading|clean_html|safe}
{if $viewowner}
    {$tag} {str tag='by' section='artefact.blog'}
    <a href="{profile_url($viewowner)}">{$viewowner|display_name}</a>
{else}
    <a href="{$WWWROOT}tags.php?tag={$tag}&sort=name&type=text">{$tag}</a>
{/if}
</h4>

{if $configerror}
    <span class="metadata">{str tag='configerror' section='blocktype.blog/taggedposts'}</span>
{elseif $badtag}
    <span class="metadata">{str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}</span>
{elseif $full}
<div id="blogdescription">
    <div id="postlist_{$blockid}" class="postlist">
    {foreach from=$results item=post}
    <div class="post">
        <h4 class="title">
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
            {$post->title}
            </a>
        </h4>
        <div class="postdetails metadata">
            <span class="icon icon-calendar mrs"></span>
            {$post->postedbyon}
        </div>
        <div class="tag tags metadata">
            <strong><span class="icon icon-tags"></span>{str tag=tags}:</strong>
            {list_tags owner=$post->owner tags=$post->taglist}
        </div>
        <div class="detail mtl mbl">{$post->description|clean_html|safe}</div>
        {if $post->commentcount != null}
        <div class="comments clearfix">
            {if $post->commentcount > 0}
                {if $post->allowcomments}
                <a class="addcomment bar-before btn-sm btn btn-default pull-right" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
                    <span class="icon icon-plus prs"></span>
                    {str tag=addcomment section=artefact.comment}
                </a>
                {/if}
                <a id="block_0{$post->id}{$blockid}" class="lead text-small commentlink as-link link-expand-right collapsed" data-toggle="collapse" href="#feedbacktable_0{$post->id}{$blockid}" aria-expanded="false">
                    {str tag=Comments section=artefact.comment} ({$post->commentcount})
                    <span class="icon icon-chevron-down pls"> </span>
                </a>
                <div id="feedbacktable_0{$post->id}{$blockid}" class="feedbacktable collapse mtl">
                    {$post->comments->tablerows|safe}
                </div>
            {else}
                {if $post->allowcomments}
                    <span class="nocomments lead text-small text-medium prm">
                        {str tag=Comments section=artefact.comment} ({$post->commentcount})
                    </span>
                    <a class="addcomment bar-before btn btn-default btn-sm pull-right" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
                        <span class="icon icon-plus prs"></span>
                        {str tag=addcomment section=artefact.comment}
                    </a>
                {/if}
            {/if}
        </div>
        {/if}
    </div>
    {/foreach}
    </div>
</div>
{else}
<ul class="taggedposts">
    {foreach from=$results item=post}
    <li>
        <strong>
        <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a>
        </strong>
        {str tag='postedin' section='blocktype.blog/taggedposts'}

        {if $viewowner}
        {$post->parenttitle}
        {else}
        <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->parent}&view={$view}">{$post->parenttitle}</a>
        {/if}
        <span class="postdetails">
            {str tag='postedon' section='blocktype.blog/taggedposts'} {$post->displaydate}
        </span>
    </li>
    {/foreach}
</ul>
{/if}
