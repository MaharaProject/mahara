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

<p>{str tag='blockheading' section='blocktype.blog/taggedposts'}
{if $viewowner}{$tag} {str tag='by' section='artefact.blog'} <a href="{profile_url($viewowner)}">{$viewowner|display_name}</a>
{else}<a href="{$WWWROOT}tags.php?tag={$tag}&sort=name&type=text">{$tag}</a>{/if}</p>

{if $configerror}{str tag='configerror' section='blocktype.blog/taggedposts'}
{elseif $badtag}{str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}
{elseif $full}
<div id="blogdescription">
    <div id="postlist_{$blockid}" class="postlist fullwidth">
    {foreach from=$results item=post}
    <div class="post">
        <h3 class="title"><a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a></h3>
        <div class="postdetails">{$post->postedbyon}</div>
        <div class="detail">{$post->description|clean_html|safe}</div>
        <div class="tags">{str tag=tags}: {list_tags owner=$post->owner tags=$post->taglist}</div>
        {if $post->allowcomments}<div class="postdetails"><a href="{$WWWROOT}artefact/artefact.php?artefact={$post->id}&view={$view}">{str tag=Comments section=artefact.comment} ({$post->commentcount})</a></div>{/if}
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
