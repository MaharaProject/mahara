{if $post->deleted}
<h4>{str tag="deletedpost" section="interaction.forum}</h4>
{else}
<h4>{$post->subject|escape}</h4>
<h5>{$post->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$post->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$post->poster}" alt=""></div>
<p>{$post->body}</p>
{if $moderator || !$closed}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}">{str tag="reply" section=interaction.forum}</a>{/if}
{if $moderator || $post->editor}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}"> {str tag="edit"}</a>{/if}
{if $moderator}<a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id|escape}"> {str tag="delete"}</a>{/if}
{if $post->edit}
<p>{str tag="edited" section="interaction.forum}</p>
<ul>
    {foreach from=$post->edit item=edit}
    <li>
        {$edit.editor|display_name|escape}
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
