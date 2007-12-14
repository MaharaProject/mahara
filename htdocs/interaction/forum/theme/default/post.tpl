{if $post->deleted}
<h4>{str tag="deletedpost" section="interaction.forum}</h4>
{else}
<h4>{$post->subject|escape}</h4>
<h5>{$post->poster|display_name|escape}</h5>
<h5>{str tag="postsvariable" section=interaction.forum args=$post->count}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$post->poster}" alt=""></div>
{$post->body}
<div>
{if $moderator || !$closed}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}">{str tag="reply" section=interaction.forum}</a>{/if}
{if $moderator || (!$closed && $post->editor)} | {/if}
{if $moderator || $post->editor}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}"> {str tag="edit"}</a>{/if}
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
