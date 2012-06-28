{if ($editing)}
    <div class="shortcut right nojs-hidden-block">
        <div{if (count($blogs) == 1)} class="hidden"{/if}>
            <span class="text">{str tag='shortcutaddpost' section='artefact.blog'}</span>
            <select id="blogselect_{$blockid}" class="select">
            {foreach from=$blogs item=blog}
                <option value="{$blog->id}"> {$blog->title} </option>
            {/foreach}
            </select>
            <a class="shortcut btn">{str tag='shortcutgo' section='artefact.blog'}</a>
        </div>
        <a class="shortcut btn{if (count($blogs) != 1)} hidden{/if}">{str tag='shortcutnewentry' section='artefact.blog'}</a>
    </div>
{/if}
<ul class="recentblogpost">
{foreach from=$mostrecent item=post}
    <li>
        <strong><a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&amp;view={$view}">{$post->title}</a></strong>
        {str tag='postedin' section='blocktype.blog/recentposts'} 
        <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&amp;view={$view}">{$post->parenttitle}</a>
        {str tag='postedon' section='blocktype.blog/recentposts'}
        <span class="description">{$post->displaydate}</span>
    </li>
{/foreach}
</ul>
