{if $post->deleted}
<h4>{str tag="deletedpost" section="interaction.forum}</h4>
{else}
{include file="interaction:forum:simplepost.tpl" post=$post}
<div>
{if $moderator || !$closed}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}">{str tag="reply" section=interaction.forum}</a>{/if}
{if $moderator || (!$closed && $post->canedit)} | {/if}
{if $moderator || $post->canedit}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}"> {str tag="edit"}</a>{/if}
{if $moderator} | <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id|escape}"> {str tag="delete"}</a>{/if}
</div>
{if $post->edit}
{str tag="editstothispost" section="interaction.forum}
<ul>
    {foreach from=$post->edit item=edit}
    <li>
        {$edit}
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
