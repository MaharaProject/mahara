{if $post->deleted}
<h5 class="deletedpost">
{if $post->deletedcount > 1}
{str tag="postsbyuserweredeleted" section="interaction.forum" args=array($post->deletedcount,display_name($post->poster))}
{else}
{str tag="postbyuserwasdeleted" section="interaction.forum" args=display_name($post->poster)}
{/if}
</h5>
{else}
<div style="margin-left:auto; margin-right:0px; width:{$width}%">
{if $post->parent}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins highlightreported=$highlightreported}
{else}
{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins highlightreported=$highlightreported nosubject=true}
{/if}
{if $reportedaction}
<div class="reportedaction">{$post->postnotobjectionableform|safe}</div>
{elseif $highlightreported}
<div class="reportedaction">{str tag=postobjectionable section=interaction.forum}</div>
{/if}
<div class="postbtns">
{if !$chronological}
    {if ($moderator || ($membership && !$closed)) && $ineditwindow}<a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id}" class="btn"><span class="btn-reply">{str tag="Reply" section=interaction.forum}</span></a>{/if}
{/if}
{if $post->canedit}<a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id}" class="btn"><span class="btn-edit">{str tag="edit"}</span></a>{/if}
{if $moderator && $post->parent} <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id}" class="btn"><span class="btn-del">{str tag="delete"}</span></a>{/if}
{if $LOGGEDIN && !$post->ownpost && !$highlightreported}<a href="{$WWWROOT}interaction/forum/reportpost.php?id={$post->id}" class="btn"><span class="btn-objection">{str tag=reportobjectionablematerial section=interaction.forum}</span></a>{/if}
</div>
</div>
{/if}
