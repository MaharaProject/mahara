{if ($editing)}
<div class="shortcut nojs-hidden-block mtl">
    <div class="panel-footer mtl {if (count($blogs) == 1)}hidden{/if}">
        <label class="text">{str tag='shortcutaddpost' section='artefact.blog'} </label>
        <div class="input-group">
            <span class="picker">
                <select id="blogselect_{$blockid}" class="select form-control">{foreach from=$blogs item=blog}<option value="{$blog->id}"> {$blog->title} </option>{/foreach}</select>
            </span>
            <input class="select" type="hidden" value="{$tagselect}">
            <span class="input-group-btn">
                <a class="btn btnshortcut btn-default">
                    <span class="icon icon-plus text-success prs"></span>
                    {str tag='shortcutgo' section='artefact.blog'}
                </a>
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
    <div id="postlist_{$blockid}" class="postlist list-group list-group-unbordered">
    {foreach from=$results item=post}
    <div class="post list-group-item">
        <h4 class="title">
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
            {$post->title}
            </a>
        </h4>
        <div class="postdetails metadata">
            <span class="icon icon-calendar mrs"></span>
            {$post->postedbyon}
        </div>
        <div class="tags metadata">
            <span class="icon icon-tags"></span>
            <strong>{str tag=tags}:</strong>
            {list_tags owner=$post->owner tags=$post->taglist}
        </div>

        <div class="detail mtl mbl">
            {$post->description|clean_html|safe}
        </div>

        {if !$editing}
            {if $post->commentcount != null}
            <div class="comments">
                {if $post->commentcount > 0}
                <a id="block_0{$post->id}{$blockid}" class="commentlink link-blocktype" data-toggle="modal-docked" data-target="#feedbacktable_0{$post->id}{$blockid}" href="#">
                    <span class="icon icon-comments"></span>
                    {str tag=Comments section=artefact.comment} ({$post->commentcount})
                </a>
                {/if}
                {if $post->allowcomments}
                <a class="addcomment link-blocktype" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
                    <span class="icon icon-arrow-circle-right"></span>
                    {str tag=addcomment section=artefact.comment}
                </a>
                {/if}
            </div>
            <div id="feedbacktable_0{$post->id}{$blockid}" class="feedbacktable modal modal-docked">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header clearfix">
                            <button class="close" data-dismiss="modal-docked">
                                <span class="times">&times;</span>
                                <span class="sr-only">{str tag=Close}</span>
                            </button>
                            <h4 class="modal-title pull-left">
                                <span class="icon icon-lg icon-comments prm"></span>
                                {str tag=Comments section=artefact.comment} |
                                {$post->title}
                            </h4>
                            {if $post->allowcomments}
                            <a class="addcomment pull-right" href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">
                                {str tag=addcomment section=artefact.comment}
                                <span class="icon icon-arrow-right pls"></span>
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
