{if ($editing)}
<div class="shortcut nojs-hidden-block">
    <div{if (count($blogs) == 1)} class="hidden"{/if}>
        <label class="text">{str tag='shortcutaddpost' section='artefact.blog'}</label>
        <select id="blogselect_{$blockid}" class="select">{foreach from=$blogs item=blog}<option value="{$blog->id}"> {$blog->title} </option>{/foreach}</select>
        <input class="select" type="hidden" value="{$tagselect}">
        <a class="btn btnshortcut">{str tag='shortcutgo' section='artefact.blog'}</a>
    </div>
    <a class="btn btnshortcut {if (count($blogs) != 1)} hidden{/if}">{str tag='shortcutnewentry' section='artefact.blog'}</a>
</div>
{/if}

<p>{$blockheading|safe}</p>
{if $configerror}{$configerror}
{elseif $badtag && $badnotag}{str tag='notagsboth' section='blocktype.blog/taggedposts' arg1=$badtag arg2=$badnotag}
{elseif $badtag}{str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}
{elseif $full}
<div id="blogdescription">
    <div id="postlist_{$blockid}" class="postlist fullwidth">
    {foreach from=$results item=post}
    <div class="post">
    {$post->html|safe}
        {if $post->commentcount != null}
        <div class="comments">
            {if $post->commentcount > 0}
                {if !$editing}<a id="block_0{$post->id}{$blockid}" class="commentlink" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">{/if}
                {str tag=Comments section=artefact.comment} ({$post->commentcount})
                {if !$editing}</a>{/if}
            {else}
                {if $post->allowcomments}
                    <span class="nocomments">{str tag=Comments section=artefact.comment} ({$post->commentcount})</span>
                {/if}
            {/if}
            {if $post->allowcomments && !$editing}
                <a class="addcomment bar-before" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">{str tag=addcomment section=artefact.comment}</a>
            {/if}
        </div>
        <div class="feedbacktablewrapper">
            <div id="feedbacktable_0{$post->id}{$blockid}" class="feedbacktable">
                {$post->comments->tablerows|safe}
            </div>
        </div>
        {/if}
    </div>
    {/foreach}
    </div>
</div>

{else}<ul class="taggedposts">{foreach from=$results item=post}
<li>
    <strong><a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a></strong>
    {str tag='postedin' section='blocktype.blog/taggedposts'}
    {if $viewowner}{$post->parenttitle}
    {else}<a href="{$WWWROOT}artefact/artefact.php?artefact={$post->parent}&view={$view}">{$post->parenttitle}</a>{/if}
    <span class="postdetails">{str tag='postedon' section='blocktype.blog/taggedposts'} {$post->displaydate}</span>
</li>
{/foreach}</ul>
{/if}
