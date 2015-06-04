{if $post->deleted}
<h5 class="deletedpost">
    {if $post->deletedcount > 1}
    
    {str tag="postsbyuserweredeleted" section="interaction.forum" args=array($post->deletedcount,display_name($post->poster))}
    
    {else}
    
    {str tag="postbyuserwasdeleted" section="interaction.forum" args=display_name($post->poster)}
    
    {/if}
</h5>

{else}
<div class="pbm ptm" style="margin-left:auto; margin-right:0px; width:{$width}%">
    {if $post->parent}
    
    {include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins highlightreported=$highlightreported}
    
    {else}
    
    {include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins highlightreported=$highlightreported nosubject=true}
    
    {/if}
    
    {if $reportedaction}
    <div class="reportedaction alert alert-danger mtl">
        {$post->postnotobjectionableform|safe}
    </div>
    
    {elseif $highlightreported}
    <div class="reportedaction text-danger ptm">
        {str tag=postobjectionable section=interaction.forum}
    </div>
    {/if}

    <div class="forum-post-btns text-right">
        {if !$chronological}
            {if ($moderator || ($membership && !$closed)) && $ineditwindow}
            <a href="{$WWWROOT}interaction/forum/editpost.php?parent={$post->id}" class="mll">
                <span class="fa fa-reply"></span>
                {str tag="Reply" section=interaction.forum}
            </a>
            {/if}
        
        {/if}
        
        {if $post->canedit}
        <a href="{$WWWROOT}interaction/forum/editpost.php?id={$post->id}" class="mll">
            <span class="fa fa-pencil"></span>
            {str tag="edit"}
        </a>
        {/if}
        
        {if $moderator && $post->parent} 
        <a href="{$WWWROOT}interaction/forum/deletepost.php?id={$post->id}" class="mll">
            <span class="fa fa-trash"></span>
            {str tag="delete"}
        </a>
        {/if}
        
        {if $LOGGEDIN && !$post->ownpost && !$highlightreported}
        <a href="{$WWWROOT}interaction/forum/reportpost.php?id={$post->id}" class="mll">
            <span class="fa fa-exclamation-triangle"></span>
            {str tag=reportobjectionablematerial section=interaction.forum}
        </a>
        {/if}
    </div>
</div>
{/if}
