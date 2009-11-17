{if $post->deleted}
{assign var=poster value=$post->poster|display_name|escape}
<h4 class="deletedpost">{str tag="postbyuserwasdeleted" section="interaction.forum" args=$poster}</h4>
{else}
{if $post->parent}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins}
{else}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins nosubject=true}
{/if}
<div class="postbtns">
{if $moderator || ($membership && !$closed)}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id|escape}" class="btn-reply">{str tag="Reply" section=interaction.forum}</a>{/if}
{if ($moderator || !$closed) && $post->canedit} | {/if}
{if $post->canedit}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id|escape}" class="btn-edit"> {str tag="edit"}</a>{/if}
{if $moderator && $post->parent} | <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id|escape}" class="btn-del"> {str tag="delete"}</a>{/if}
</div>
{/if}
{if $children}
<div class="postreply">
{foreach from=$children item=child}
        {$child}
{/foreach}
</div>
{/if}
