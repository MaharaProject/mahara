{if $post->deleted}
<h4>{str tag="deletedpost" section="interaction.forum}</h4>
{else}
{if $post->parent}
{include file="interaction:forum:simplepost.tpl" post=$post groupowner=$groupowner}
{else}
{include file="interaction:forum:simplepost.tpl" post=$post groupowner=$groupowner nosubject=true}
{/if}
<div>
{if $moderator || !$closed}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}">{str tag="reply" section=interaction.forum}</a>{/if}
{if ($moderator || !$closed) && $post->canedit} | {/if}
{if $post->canedit}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}"> {str tag="edit"}</a>{/if}
{if $moderator && $post->parent} | <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id|escape}"> {str tag="delete"}</a>{/if}
</div>
{if $post->edit}
<h5>{str tag="editstothispost" section="interaction.forum}</h5>
<ul>
    {foreach from=$post->edit item=edit}
    <li>
        <a href="{$WWWROOT}user/view.php?id={$edit.editor}"
        {if $edit.editor == $groupowner} class="groupowner"
        {elseif $edit.moderator} class="moderator"
        {/if}
        >
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$edit.editor}" alt="">
        {$edit.editor|display_name|escape}
        </a>
        {$edit.edittime|escape}
    </li>
    {/foreach}
</ul>
{/if}
{/if}
{if $children}
<ul>
{foreach from=$children item=child}
    <li>
        {$child}
    </li>
{/foreach}
</ul>
{/if}
