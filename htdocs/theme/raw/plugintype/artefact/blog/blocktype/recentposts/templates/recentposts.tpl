{if ($editing)}
    {if (count($blogs) == 1)}
        <a class="panel-footer {if (count($blogs) != 1)} hidden{/if}">
            <span id="blog_{$blogs[0]->id}" class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            {str tag='shortcutnewentry' section='artefact.blog'}
        </a>
    {elseif (count($blogs) > 1)}
    <div class="panel-footer">
        <label class="text" for="blogselect_{$blockid}">{str tag='shortcutaddpost' section='artefact.blog'}</label>
        <div class="input-group">

            <select id="blogselect_{$blockid}" class="select form-control">
            {foreach from=$blogs item=blog}
                <option value="{$blog->id}"> {$blog->title} </option>
            {/foreach}
            </select>
            <span class="input-group-btn">
                <a class="btn btn-default btnshortcut">
                    <span class="icon icon-plus text-success left" role="presentation" aria-hidden="true"></span> {str tag='shortcutadd' section='artefact.blog'}
                </a>
            </span>
        </div>
    </div>
    {/if}
{/if}
<div class="recentblogpost list-group">
{foreach from=$mostrecent item=post}
    <div class="list-group-item">
        <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&amp;view={$view}" class="outer-link">
            <span class="sr-only">{$post->title}</span>
        </a>
        <h4 class="list-group-item-heading text-inline">
            {$post->title}
        </h4>
        <span class="text-small">
            {str tag='postedin' section='blocktype.blog/recentposts'}
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->parent}&amp;view={$view}" class="inner-link">
                {$post->parenttitle}
            </a>
        </span>
        <span class="metadata">
            {str tag='postedon' section='blocktype.blog/recentposts'}
            {$post->displaydate}
        </span>
    </div>
{/foreach}
</div>
