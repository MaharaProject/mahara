{if $post->deleted}
<h4 class="deletedpost">{str tag="postbyuserwasdeleted" section="interaction.forum" args=display_name($post->poster)}</h4>
{else}
{if $post->parent}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins}
{else}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins nosubject=true}
{/if}
<div class="postbtns">
{if $moderator || ($membership && !$closed)}<span class="btn"><a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id}" class="icon btn-reply">{str tag="Reply" section=interaction.forum}</a></span>{/if}
{if $post->canedit}<span class="btn"><a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id}" class="icon btn-edit"> {str tag="edit"}</a></span>{/if}
{if $moderator && $post->parent} <span class="btn"><a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id}" class="icon btn-del"> {str tag="delete"}</a></span>{/if}
</div>
{/if}
{if $children}
<div class="postreply">
{foreach from=$children item=child}
        {$child|safe}
{/foreach}
</div>
{/if}
