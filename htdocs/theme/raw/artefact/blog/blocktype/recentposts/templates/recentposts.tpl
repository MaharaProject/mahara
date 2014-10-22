{if ($editing)}
    <div class="shortcut nojs-hidden-block">
        <div{if (count($blogs) == 1)} class="hidden"{/if}>
            <label class="text" for="blogselect_{$blockid}">{str tag='shortcutaddpost' section='artefact.blog'}</label>
            <select id="blogselect_{$blockid}" class="select">
            {foreach from=$blogs item=blog}
                <option value="{$blog->id}"> {$blog->title} </option>
            {/foreach}
            </select>
            <a class="btn btnshortcut">{str tag='shortcutgo' section='artefact.blog'}</a>
        </div>
        <a class="btn btnshortcut{if (count($blogs) != 1)} hidden{/if}">{str tag='shortcutnewentry' section='artefact.blog'}</a>
    </div>
{/if}
<ul class="recentblogpost">
{foreach from=$mostrecent item=post}
    <li>
        <strong><a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&amp;view={$view}">{$post->title}</a></strong>
        {str tag='postedin' section='blocktype.blog/recentposts'} 
        <a href="{$WWWROOT}artefact/artefact.php?artefact={$post->parent}&amp;view={$view}">{$post->parenttitle}</a>
        {str tag='postedon' section='blocktype.blog/recentposts'}
        <span>{$post->displaydate}</span>
    </li>
{/foreach}
</ul>
