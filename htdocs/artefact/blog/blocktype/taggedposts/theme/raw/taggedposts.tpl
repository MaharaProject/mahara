{if ($editing)}
    <div class="shortcut right nojs-hidden-block">
        <div{if (count($blogs) == 1)} class="hidden"{/if}>
            <span class="text">{str tag='shortcutaddpost' section='artefact.blog'}</span>
            <select id="blogselect_{$blockid}" class="select">
            {foreach from=$blogs item=blog}
                <option value="{$blog->id}"> {$blog->title} </option>
            {/foreach}
            </select>
            <input class="select" type="hidden" value="{$tagselect}">
            <a class="shortcut btn">{str tag='shortcutgo' section='artefact.blog'}</a>
        </div>
        <a class="shortcut btn{if (count($blogs) != 1)} hidden{/if}">{str tag='shortcutnewentry' section='artefact.blog'}</a>
    </div>
{/if}
{if $configerror}
    {str tag='configerror' section='blocktype.blog/taggedposts'}
{elseif $badtag}
    {str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}
{else}
    {str tag='blockheading' section='blocktype.blog/taggedposts'}
    {if $viewowner}
        <strong>{$tag}</strong> by <strong><a href="{profile_url($viewowner)}">{$viewowner|display_default_name}</a></strong>
    {else}
        <strong><a href="{$WWWROOT}tags.php?tag={$tag}&sort=name&type=text">{$tag}</a></strong>
    {/if}
    <ul class="taggedposts">
    {foreach from=$results item=post}
        <li>
            <strong><a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a></strong>
            {str tag='postedin' section='blocktype.blog/taggedposts'}
            {if $viewowner}
                {$post->parenttitle}
            {else}
                <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&view={$view}">{$post->parenttitle}</a>
            {/if}
        </li>
    {/foreach}
    </ul>
{/if}
