{if ($editing)}
<div class="shortcut nojs-hidden-block">
    <div{if (count($blogs) == 1)} class="hidden"{/if}>
        <label class="text">{str tag='shortcutaddpost' section='artefact.blog'}</label>
        <select id="blogselect_{$blockid}" class="select">{foreach from=$blogs item=blog}<option value="{$blog->id}"> {$blog->title} </option>{/foreach}</select>
        <input class="select" type="hidden" value="{$tagselect}">
        <a class="shortcut btn">{str tag='shortcutgo' section='artefact.blog'}</a>
    </div>
    <a class="shortcut btn{if (count($blogs) != 1)} hidden{/if}">{str tag='shortcutnewentry' section='artefact.blog'}</a>
</div>
{/if}

{str tag='blockheading' section='blocktype.blog/taggedposts'}
{if $viewowner}<strong>{$tag}</strong> by <strong><a href="{profile_url($viewowner)}">{$viewowner|display_name}</a></strong>
{else}<strong><a href="{$WWWROOT}tags.php?tag={$tag}&sort=name&type=text">{$tag}</a></strong>{/if}

{if $configerror}{str tag='configerror' section='blocktype.blog/taggedposts'}
{elseif $badtag}{str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}
{elseif $full}
<div id="blogdescription">
    <table id="postlist_{$blockid}" class="postlist"><tbody>
    {foreach from=$results item=post}
    <tr><td>
        <h3><a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a></h3>
        <div>{$post->description|clean_html|safe}
            <p class="tags s"><label>{str tag=tags}:</label> {list_tags owner=$post->owner tags=$post->taglist}</p>
        </div>
        <div class="postdetails">{$post->postedbyon}
        {if $post->allowcomments} | <a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&view={$view}">{str tag=Comments section=artefact.comment} ({$post->commentcount})</a>{/if}</div>
    </td></tr>
    {/foreach}
    </tbody></table>
</div>

{else}<ul class="taggedposts">{foreach from=$results item=post}
<li>
    <strong><a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a></strong>
    {str tag='postedin' section='blocktype.blog/taggedposts'}
    {if $viewowner}{$post->parenttitle}
    {else}<a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&view={$view}">{$post->parenttitle}</a>{/if}
    {str tag='postedon' section='blocktype.blog/taggedposts'} <span class="description">{$post->displaydate}</span>
</li>
{/foreach}</ul>
{/if}
