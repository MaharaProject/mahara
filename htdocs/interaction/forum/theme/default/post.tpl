{if $post->deleted}
<h4>{str tag="deletedpost" section="interaction.forum}</h4>
{else}
{if $post->parent}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins}
{else}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins nosubject=true}
{/if}
<div class="postbtns">
{if $moderator || !$closed}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}" id="btn-reply">{str tag="Reply" section=interaction.forum}</a>{/if}
{if ($moderator || !$closed) && $post->canedit} | {/if}
{if $post->canedit}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}" id="btn-edit"> {str tag="edit"}</a>{/if}
{if $moderator && $post->parent} | <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id|escape}" id="btn-delete"> {str tag="delete"}</a>{/if}
</div>
{if $post->edit}
<h5>{str tag="editstothispost" section="interaction.forum}</h5>
<ul>
    {foreach from=$post->edit item=edit}
    <li>
        <a href="{$WWWROOT}user/view.php?id={$edit.editor}"{if in_array($edit.editor, $groupadmins)} class="groupadmin"{elseif $edit.moderator} class="moderator"{/if}>
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
<ul class="postreply">
{foreach from=$children item=child}
    <li>
        {$child}
    </li>
{/foreach}
</ul>
{/if}
