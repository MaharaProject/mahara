<h4>{$post->subject|escape}</h4>
<h5>{$post->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$post->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&maxsize=100&id={$post->poster}" alt=""></div>
<p>{$post->body}</p>
{if $moderator || !$closed}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}">{str tag="reply" section=interaction.forum}</a>{/if}
{if $moderator || $post->editor}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}"> {str tag="edit" section=interaction.forum}</a>{/if}
{if $moderator && $post->parent}<a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id|escape}"> {str tag="delete" section=interaction.forum}</a>{/if}
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
{if $children}
<ul>
{foreach from=$children item=child}
    <li>
        {$child}
    </li>
{/foreach}
</ul>
{/if}
