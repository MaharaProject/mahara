<ul class="recentblogpost list-group list-group-link">
{foreach from=$mostrecent item=post}
    <li class="list-group-item list-group-item-link">
        <h4 class="list-group-item-heading mb0">
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&amp;view={$view}" class="">
                {$post->title}
                <span class="metadata">
                    {str tag='postedin' section='blocktype.blog/recentposts'}
                    {$post->parenttitle}
                    {str tag='postedon' section='blocktype.blog/recentposts'}
                    {$post->displaydate}
                </span>
            </a>
        </h4>
    </li>
{/foreach}
</ul>
{if ($editing)}
    {if (count($blogs) == 1)}
        <a class="panel-footer {if (count($blogs) != 1)} hidden{/if}">
            <span class="icon icon-plus prs"></span>
            {str tag='shortcutnewentry' section='artefact.blog'}
        </a>
    {else}
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
                    <span class="icon icon-plus prs"></span> {str tag='shortcutgo' section='artefact.blog'}
                </a>
            </span>
        </div>
    </div>
    {/if}
{/if}
