{if $post->id}
<a name="post{$post->id}">
</a>
{/if}

    <div class="media forum-post">
        <div class="forumpostleft media-left metadata">
            <img src="{profile_icon_url user=$post->poster maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$post->poster|display_default_name}" class="media-object">
            
            <div class="poster mtm">
                <a href="{profile_url($post->poster)}"{if in_array($post->poster, $groupadmins)} class="groupadmin"{elseif $post->moderator} class="moderator"{/if}>{$post->poster|display_name}
                </a>
            </div>
            
            {if $post->postcount}
            <div class="postcount mtm">
                {$post->postcount}
            </div>
            {/if}
        </div>
        <div class="postedits media-body">
            {if $post->subject && !$nosubject}
            <div class="forumsubject media-heading">
                <h4 class="title">
                    {if $post->id}
                    <a href="#post{$post->id}">
                        {$post->subject}
                    </a>
                    {else}
                        {$post->subject}
                    {/if}
                </h4>
            </div>
            {/if}
            
            <p class="posttime detail">
                {$post->ctime}
            </p>
            
            {$post->body|clean_html|safe}
            
            {if $post->edit}
            <div class="editstopost">
                <h4 class="title">
                    {str tag="editstothispost" section="interaction.forum"}
                </h4>
                <ul>
                    {foreach from=$post->edit item=edit}
                    <li>
                        <a href="{profile_url($edit.editor)}"
                        {if $edit.editor == $groupowner} class="groupowner"
                        {elseif $edit.moderator} class="moderator"
                        {/if}
                        >
                        <img src="{profile_icon_url user=$edit.editor maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$edit.editor|display_default_name}">
                        {$edit.editor|display_name}
                    </a> - 
                    <span class="posttime">
                        {$edit.edittime}
                    </span>
                </li>
                {/foreach}
            </ul>
        </div>
        {/if}
    </div>
</div>
