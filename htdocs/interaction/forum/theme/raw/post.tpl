{if $post->deleted}
<h5 class="deletedpost">{str tag="postbyuserwasdeleted" section="interaction.forum" args=display_name($post->poster)}</h5>
{else}
<div style="margin-left:auto; margin-right:0px; width:{$width}%">
{if $post->parent}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins}
{else}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins nosubject=true}
{/if}
<div class="postbtns">
{if ($moderator || ($membership && !$closed)) && $ineditwindow}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id}" class="btn"><span class="btn-reply">{str tag="Reply" section=interaction.forum}</span></a>{/if}
{if $post->canedit}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id}" class="btn"><span class="btn-edit">{str tag="edit"}</span></a>{/if}
{if $moderator && $post->parent} <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id}" class="btn"><span class="btn-del">{str tag="delete"}</span></a>{/if}
</div>
</div>
{/if}
